<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingZone;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Database-backed cart. Guests are identified by a signed cookie token; on login the
 * guest cart is merged into the customer's cart. Prices are always read live from the
 * catalogue so a stale cart can never check out at an old price.
 */
class CartService
{
    public const COOKIE = 'lx_cart';

    private ?Cart $cart = null;

    public function current(bool $create = false): ?Cart
    {
        if ($this->cart) {
            return $this->cart;
        }

        $userId = auth()->id();
        $token = request()->cookie(self::COOKIE);

        $cart = null;
        if ($userId) {
            $cart = Cart::where('user_id', $userId)->whereNull('converted_at')->latest('id')->first();
        }
        if (! $cart && $token && Str::isUuid($token)) {
            $cart = Cart::where('token', $token)->whereNull('converted_at')->first();
            if ($cart && $userId && ! $cart->user_id) {
                $cart->update(['user_id' => $userId]);
            }
        }

        if (! $cart && $create) {
            $cart = Cart::create(['token' => (string) Str::uuid(), 'user_id' => $userId]);
        }

        if ($cart && $cart->token !== $token) {
            Cookie::queue(self::COOKIE, $cart->token, 60 * 24 * 30);
        }

        return $this->cart = $cart;
    }

    /** @param array<string, mixed>|null $engraving */
    public function add(Product $product, ?ProductVariant $variant, int $quantity, ?array $engraving = null): CartItem
    {
        $quantity = max(1, $quantity);

        if (! $product->isPurchasable()) {
            throw ValidationException::withMessages(['cart' => 'This product is currently unavailable.']);
        }

        if ($product->activeVariants()->exists() && ! $variant) {
            throw ValidationException::withMessages(['variant' => 'Please choose a variant.']);
        }

        if ($variant && ($variant->product_id !== $product->id || ! $variant->is_active)) {
            throw ValidationException::withMessages(['variant' => 'Selected variant is not available.']);
        }

        if ($engraving && ! $product->engraving_available) {
            $engraving = null;
        }

        $cart = $this->current(create: true);

        // Engraved items are always separate lines because each one carries its own text.
        $item = $engraving ? null : $cart->items()
            ->where('product_id', $product->id)
            ->where('product_variant_id', $variant?->id)
            ->whereNull('engraving')
            ->first();

        $newQuantity = ($item?->quantity ?? 0) + $quantity;
        $stock = $variant ? $variant->stock_quantity : $product->stock_quantity;

        if ($newQuantity > $stock) {
            throw ValidationException::withMessages(['quantity' => "Only {$stock} left in stock."]);
        }

        if ($item) {
            $item->update(['quantity' => $newQuantity]);

            return $item;
        }

        return $cart->items()->create([
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'quantity' => $quantity,
            'engraving' => $engraving,
        ]);
    }

    public function updateQuantity(int $itemId, int $quantity): void
    {
        $item = $this->findItem($itemId);

        if ($quantity < 1) {
            $item->delete();

            return;
        }

        if ($quantity > $item->availableStock()) {
            throw ValidationException::withMessages(['quantity' => "Only {$item->availableStock()} left in stock."]);
        }

        $item->update(['quantity' => $quantity]);
    }

    public function remove(int $itemId): void
    {
        $this->findItem($itemId)->delete();
    }

    public function applyCoupon(string $code): Coupon
    {
        $coupon = Coupon::where('code', strtoupper(trim($code)))->first();

        if (! $coupon) {
            throw ValidationException::withMessages(['coupon' => 'Invalid coupon code.']);
        }

        if ($error = $coupon->validationError($this->subtotal())) {
            throw ValidationException::withMessages(['coupon' => $error]);
        }

        $this->current(create: true)->update(['coupon_code' => $coupon->code]);

        return $coupon;
    }

    public function removeCoupon(): void
    {
        $this->current()?->update(['coupon_code' => null]);
    }

    /** @return Collection<int, CartItem> */
    public function items()
    {
        $cart = $this->current();

        if (! $cart) {
            return collect();
        }

        return $cart->items()
            ->with(['product' => fn ($q) => $q->withTrashed()->with('media'), 'variant.color', 'variant.material', 'variant.size'])
            ->get()
            ->each(fn (CartItem $item) => $item->variant?->setRelation('product', $item->product));
    }

    public function count(): int
    {
        $cart = $this->current();

        return $cart ? (int) $cart->items()->sum('quantity') : 0;
    }

    public function subtotal(): float
    {
        return round($this->items()->sum(fn (CartItem $item) => $item->unitPrice() * $item->quantity), 2);
    }

    /**
     * Full price breakdown for the cart and checkout pages.
     *
     * @return array{items: Collection, subtotal: float, engraving: float, discount: float, shipping: float, total: float, coupon: ?Coupon, coupon_error: ?string}
     */
    public function totals(?ShippingZone $zone = null): array
    {
        $items = $this->items();
        $subtotal = round($items->sum(fn (CartItem $i) => $i->unitPrice() * $i->quantity), 2);
        $engraving = round($items->sum(fn (CartItem $i) => $i->engravingFee()), 2);

        $coupon = null;
        $couponError = null;
        $discount = 0.0;
        if ($code = $this->current()?->coupon_code) {
            $coupon = Coupon::where('code', $code)->first();
            $couponError = $coupon ? $coupon->validationError($subtotal) : 'Coupon no longer exists.';
            if ($couponError) {
                $coupon = null;
            } else {
                $discount = $coupon->discountFor($subtotal);
            }
        }

        $shipping = 0.0;
        if ($zone && $items->isNotEmpty()) {
            $shipping = $coupon?->free_shipping ? 0.0 : $zone->chargeFor($subtotal - $discount);
        }

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'engraving' => $engraving,
            'discount' => $discount,
            'shipping' => $shipping,
            'total' => round(max(0, $subtotal + $engraving - $discount + $shipping), 2),
            'coupon' => $coupon,
            'coupon_error' => $couponError,
        ];
    }

    /** Merge a guest cart into the user's cart after login. */
    public function mergeGuestCart(?string $guestToken, int $userId): void
    {
        if (! $guestToken || ! Str::isUuid($guestToken)) {
            return;
        }

        $guest = Cart::where('token', $guestToken)->whereNull('user_id')->whereNull('converted_at')->first();
        if (! $guest) {
            return;
        }

        $userCart = Cart::where('user_id', $userId)->whereNull('converted_at')->where('id', '!=', $guest->id)->latest('id')->first();

        if (! $userCart) {
            $guest->update(['user_id' => $userId]);

            return;
        }

        foreach ($guest->items as $item) {
            $existing = $item->engraving ? null : $userCart->items()
                ->where('product_id', $item->product_id)
                ->where('product_variant_id', $item->product_variant_id)
                ->whereNull('engraving')
                ->first();

            $existing
                ? $existing->increment('quantity', $item->quantity)
                : $item->update(['cart_id' => $userCart->id]);
        }

        $guest->delete();
        $this->cart = null;
    }

    public function forget(): void
    {
        $this->cart = null;
    }

    private function findItem(int $itemId): CartItem
    {
        $item = $this->current()?->items()->with(['product', 'variant'])->find($itemId);

        if (! $item) {
            throw ValidationException::withMessages(['cart' => 'Cart item not found.']);
        }

        return $item;
    }
}
