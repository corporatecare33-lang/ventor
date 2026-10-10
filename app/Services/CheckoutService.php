<?php

namespace App\Services;

use App\Enums\EngravingStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\StockMovementType;
use App\Enums\TransactionStatus;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingZone;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        private CartService $cart,
        private InventoryService $inventory,
    ) {}

    /**
     * Turn the current cart into an order. Everything runs in one transaction with
     * row locks, so stock and coupon usage can't be oversold by parallel checkouts.
     *
     * @param array{customer_name: string, phone: string, email?: ?string, address: string, area?: ?string, city: string, district?: ?string, postal_code?: ?string, shipping_zone_id: int, payment_method: string, transaction_id?: ?string, sender_number?: ?string, customer_note?: ?string} $data
     */
    public function placeOrder(array $data): Order
    {
        $cart = $this->cart->current();
        $zone = ShippingZone::active()->findOrFail($data['shipping_zone_id']);
        $method = PaymentMethod::active()->where('code', $data['payment_method'])->firstOrFail();

        if (! $cart || $cart->items()->doesntExist()) {
            throw ValidationException::withMessages(['cart' => 'Your cart is empty.']);
        }

        if ($method->requires_transaction_id && empty($data['transaction_id'])) {
            throw ValidationException::withMessages(['transaction_id' => "Please enter the {$method->name} transaction ID."]);
        }

        $order = DB::transaction(function () use ($cart, $zone, $method, $data) {
            $totals = $this->cart->totals($zone);

            /** @var \Illuminate\Support\Collection<int, CartItem> $items */
            $items = $totals['items'];

            foreach ($items as $item) {
                $this->assertAvailable($item);
            }

            $coupon = null;
            if ($totals['coupon']) {
                $coupon = Coupon::whereKey($totals['coupon']->id)->lockForUpdate()->first();
                if (! $coupon || $coupon->validationError($totals['subtotal'])) {
                    throw ValidationException::withMessages(['coupon' => 'Your coupon is no longer valid. Please remove it and try again.']);
                }
                $coupon->increment('used_count');
            }

            $order = Order::create([
                'order_number' => Order::generateNumber(),
                'user_id' => auth()->id(),
                'customer_name' => $data['customer_name'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,
                'address' => $data['address'],
                'area' => $data['area'] ?? null,
                'city' => $data['city'],
                'district' => $data['district'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'shipping_zone_id' => $zone->id,
                'shipping_zone_name' => $zone->name,
                'subtotal' => $totals['subtotal'],
                'engraving_total' => $totals['engraving'],
                'discount' => $totals['discount'],
                'shipping_charge' => $totals['shipping'],
                'total' => $totals['total'],
                'coupon_id' => $coupon?->id,
                'coupon_code' => $coupon?->code,
                'payment_method' => $method->code,
                'payment_status' => $method->requires_transaction_id ? PaymentStatus::PendingVerification : PaymentStatus::Unpaid,
                'status' => OrderStatus::Pending,
                'customer_note' => $data['customer_note'] ?? null,
                'ip_address' => request()->ip(),
            ]);

            foreach ($items as $item) {
                $orderItem = $order->items()->create([
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'product_name' => $item->product->name,
                    'variant_name' => $item->variant?->name(),
                    'sku' => $item->variant?->sku ?? $item->product->sku,
                    'unit_price' => $item->unitPrice(),
                    'quantity' => $item->quantity,
                    'engraving_fee' => $item->engravingFee(),
                    'line_total' => $item->lineTotal(),
                    'warranty_months' => $item->product->warranty_months,
                ]);

                $this->inventory->adjust($item->product, $item->variant, -$item->quantity, StockMovementType::Sale, "Order {$order->order_number}", $order->id);
                Product::whereKey($item->product_id)->increment('sold_count', $item->quantity);

                if ($item->engraving) {
                    $order->engravingRequests()->create([
                        'user_id' => auth()->id(),
                        'order_item_id' => $orderItem->id,
                        'product_id' => $item->product_id,
                        'customer_name' => $data['customer_name'],
                        'phone' => $data['phone'],
                        'email' => $data['email'] ?? null,
                        'type' => $item->engraving['type'],
                        'text' => $item->engraving['text'] ?? null,
                        'font' => $item->engraving['font'] ?? null,
                        'position' => $item->engraving['position'] ?? null,
                        'logo_instruction' => $item->engraving['logo_instruction'] ?? null,
                        'quantity' => $item->quantity,
                        'fee' => $item->engravingFee(),
                        'status' => EngravingStatus::Pending,
                    ]);
                }
            }

            if ($method->requires_transaction_id) {
                $order->payments()->create([
                    'method' => $method->code,
                    'amount' => $order->total,
                    'transaction_id' => $data['transaction_id'],
                    'sender_number' => $data['sender_number'] ?? null,
                    'status' => TransactionStatus::Pending,
                ]);
            }

            $order->statusHistories()->create(['status' => OrderStatus::Pending, 'note' => 'Order placed by customer.']);

            $cart->update(['converted_at' => now()]);

            return $order;
        });

        $this->cart->forget();

        \App\Jobs\SendOrderPlacedNotification::dispatch($order->id)->afterCommit();

        return $order;
    }

    private function assertAvailable(CartItem $item): void
    {
        $product = $item->product;

        if (! $product || $product->trashed() || ! $product->isPurchasable()) {
            throw ValidationException::withMessages(['cart' => ($product->name ?? 'A product').' is no longer available. Please remove it from your cart.']);
        }

        $stock = $item->variant
            ? (int) ProductVariant::whereKey($item->variant->id)->lockForUpdate()->value('stock_quantity')
            : (int) Product::whereKey($product->id)->lockForUpdate()->value('stock_quantity');

        if ($item->quantity > $stock) {
            throw ValidationException::withMessages(['cart' => "Only {$stock} of {$product->name} left in stock. Please update your cart."]);
        }
    }
}
