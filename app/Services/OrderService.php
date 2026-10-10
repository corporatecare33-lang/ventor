<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\StockMovementType;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(private InventoryService $inventory) {}

    /** Move an order to a new status, stamping dates and restocking on cancel/return. */
    public function changeStatus(Order $order, OrderStatus $status, ?string $note = null): Order
    {
        if ($order->status === $status) {
            return $order;
        }

        return DB::transaction(function () use ($order, $status, $note) {
            $restock = in_array($status, [OrderStatus::Cancelled, OrderStatus::Returned], true)
                && ! in_array($order->status, [OrderStatus::Cancelled, OrderStatus::Returned], true);

            if ($restock) {
                $order->loadMissing('items.product', 'items.variant');
                foreach ($order->items as $item) {
                    if ($item->product) {
                        $type = $status === OrderStatus::Returned ? StockMovementType::Return : StockMovementType::Cancel;
                        $this->inventory->adjust($item->product, $item->variant, $item->quantity, $type, "Order {$order->order_number} {$status->value}", $order->id);
                        $item->product->newQuery()->whereKey($item->product_id)->where('sold_count', '>=', $item->quantity)->decrement('sold_count', $item->quantity);
                    }
                }
                if ($order->coupon_id) {
                    $order->coupon()->where('used_count', '>', 0)->decrement('used_count');
                }
            }

            $stamps = match ($status) {
                OrderStatus::Confirmed => ['confirmed_at' => now()],
                OrderStatus::Shipped => ['shipped_at' => now()],
                OrderStatus::Delivered => ['delivered_at' => now()],
                default => [],
            };

            $order->update(['status' => $status, ...$stamps]);
            $order->statusHistories()->create(['status' => $status, 'note' => $note, 'user_id' => auth()->id()]);

            return $order;
        });
    }
}
