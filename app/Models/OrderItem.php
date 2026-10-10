<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'product_variant_id', 'product_name', 'variant_name', 'sku',
        'unit_price', 'quantity', 'engraving_fee', 'line_total', 'warranty_months',
    ];

    protected $casts = ['unit_price' => 'decimal:2', 'engraving_fee' => 'decimal:2', 'line_total' => 'decimal:2'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function engravingRequest(): HasOne
    {
        return $this->hasOne(EngravingRequest::class);
    }
}
