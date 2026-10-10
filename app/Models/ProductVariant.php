<?php

namespace App\Models;

use App\Services\InventoryService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id', 'color_id', 'material_id', 'size_id', 'sku',
        'price', 'sale_price', 'stock_quantity', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        $sync = function (ProductVariant $variant) {
            $service = app(InventoryService::class);
            $service->syncProductStockFromVariants($variant->product);
            $service->syncStatus($variant->product->fresh());
        };

        static::saved($sync);
        static::deleted($sync);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(ProductOption::class, 'color_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(ProductOption::class, 'material_id');
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(ProductOption::class, 'size_id');
    }

    public function name(): string
    {
        return collect([$this->color?->name, $this->material?->name, $this->size?->name])->filter()->implode(' / ') ?: $this->sku;
    }

    /** Variant price falls back to the parent product's price. */
    public function currentPrice(): float
    {
        if ($this->price === null) {
            return $this->product->currentPrice();
        }

        $sale = $this->sale_price;

        return (float) ($sale !== null && (float) $sale > 0 && (float) $sale < (float) $this->price ? $sale : $this->price);
    }

    public function regularPrice(): float
    {
        return (float) ($this->price ?? $this->product->regular_price);
    }
}
