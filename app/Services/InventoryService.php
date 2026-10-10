<?php

namespace App\Services;

use App\Enums\ProductStatus;
use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InventoryService
{
    /**
     * Change stock for a product or one of its variants and record the movement.
     * Positive $quantity adds stock, negative removes it.
     */
    public function adjust(
        Product $product,
        ?ProductVariant $variant,
        int $quantity,
        StockMovementType $type,
        ?string $note = null,
        ?int $orderId = null,
    ): StockMovement {
        return DB::transaction(function () use ($product, $variant, $quantity, $type, $note, $orderId) {
            $target = $variant
                ? ProductVariant::whereKey($variant->id)->lockForUpdate()->firstOrFail()
                : Product::whereKey($product->id)->lockForUpdate()->firstOrFail();

            $balance = (int) $target->stock_quantity + $quantity;

            if ($balance < 0) {
                throw new InvalidArgumentException("Not enough stock for {$product->name}.");
            }

            $target->forceFill(['stock_quantity' => $balance])->save();

            if ($variant) {
                $this->syncProductStockFromVariants($product);
            }

            $this->syncStatus($product->fresh());

            return StockMovement::create([
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'user_id' => auth()->id(),
                'order_id' => $orderId,
                'type' => $type,
                'quantity' => $quantity,
                'balance_after' => $balance,
                'note' => $note,
            ]);
        });
    }

    /** Products with variants keep their stock_quantity as the variants' total. */
    public function syncProductStockFromVariants(Product $product): void
    {
        $total = (int) $product->variants()->where('is_active', true)->sum('stock_quantity');

        Product::whereKey($product->id)->update(['stock_quantity' => $total]);
    }

    /** Flip active <-> out_of_stock automatically; drafts are never touched. */
    public function syncStatus(Product $product): void
    {
        if ($product->status === ProductStatus::Draft) {
            return;
        }

        $status = $product->stock_quantity > 0 ? ProductStatus::Active : ProductStatus::OutOfStock;

        if ($product->status !== $status) {
            $product->forceFill(['status' => $status])->save();
        }
    }
}
