<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = ['cart_id', 'product_id', 'product_variant_id', 'quantity', 'engraving'];

    protected $casts = ['engraving' => 'array'];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function unitPrice(): float
    {
        return $this->variant ? $this->variant->currentPrice() : $this->product->currentPrice();
    }

    public function engravingFee(): float
    {
        return $this->engraving ? (float) $this->product->engraving_price * $this->quantity : 0.0;
    }

    public function lineTotal(): float
    {
        return $this->unitPrice() * $this->quantity + $this->engravingFee();
    }

    public function availableStock(): int
    {
        return $this->variant ? (int) $this->variant->stock_quantity : (int) $this->product->stock_quantity;
    }
}
