<?php

namespace App\Models;

use App\Enums\CouponType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'description', 'type', 'value', 'min_order_amount', 'max_discount',
        'usage_limit', 'used_count', 'free_shipping', 'starts_at', 'expires_at', 'is_active',
    ];

    protected $casts = [
        'type' => CouponType::class,
        'value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'free_shipping' => 'boolean',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(fn (Coupon $coupon) => $coupon->code = strtoupper(trim($coupon->code)));
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /** Returns null when usable, otherwise a customer-facing reason. */
    public function validationError(float $subtotal): ?string
    {
        return match (true) {
            ! $this->is_active => 'This coupon is not active.',
            $this->starts_at && $this->starts_at->isFuture() => 'This coupon is not active yet.',
            $this->expires_at && $this->expires_at->isPast() => 'This coupon has expired.',
            $this->usage_limit !== null && $this->used_count >= $this->usage_limit => 'This coupon has reached its usage limit.',
            $this->min_order_amount !== null && $subtotal < (float) $this->min_order_amount => 'Minimum order for this coupon is ৳'.number_format((float) $this->min_order_amount).'.',
            default => null,
        };
    }

    public function discountFor(float $subtotal): float
    {
        $discount = $this->type === CouponType::Percent
            ? $subtotal * ((float) $this->value / 100)
            : (float) $this->value;

        if ($this->max_discount !== null) {
            $discount = min($discount, (float) $this->max_discount);
        }

        return round(min($discount, $subtotal), 2);
    }
}
