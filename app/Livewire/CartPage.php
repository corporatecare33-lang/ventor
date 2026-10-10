<?php

namespace App\Livewire;

use App\Models\ShippingZone;
use App\Services\CartService;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class CartPage extends Component
{
    public string $couponCode = '';

    public function updateQuantity(CartService $cart, int $itemId, int $quantity): void
    {
        $this->wrap(fn () => $cart->updateQuantity($itemId, $quantity));
    }

    public function remove(CartService $cart, int $itemId): void
    {
        $cart->remove($itemId);
        $this->dispatch('cart-updated');
    }

    public function applyCoupon(CartService $cart): void
    {
        $this->validate(['couponCode' => ['required', 'string', 'max:40']]);

        $this->wrap(function () use ($cart) {
            $coupon = $cart->applyCoupon($this->couponCode);
            $this->couponCode = '';
            $this->dispatch('toast', message: "Coupon {$coupon->code} applied");
        }, 'couponCode');
    }

    public function removeCoupon(CartService $cart): void
    {
        $cart->removeCoupon();
    }

    private function wrap(callable $callback, string $errorKey = 'cart'): void
    {
        try {
            $callback();
            $this->dispatch('cart-updated');
        } catch (ValidationException $e) {
            $this->addError($errorKey, collect($e->errors())->flatten()->first());
        }
    }

    public function render(CartService $cart)
    {
        $cart->forget();
        $zone = ShippingZone::active()->first();

        return view('livewire.cart-page', [
            'totals' => $cart->totals(),
            'cheapestZone' => $zone,
        ]);
    }
}
