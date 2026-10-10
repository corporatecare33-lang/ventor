<?php

namespace App\Models;

use App\Models\Concerns\FlushesStorefrontCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ShippingZone extends Model
{
    use FlushesStorefrontCache;

    protected $fillable = ['name', 'description', 'charge', 'free_shipping_threshold', 'min_days', 'max_days', 'is_active', 'sort_order'];

    protected $casts = ['charge' => 'decimal:2', 'free_shipping_threshold' => 'decimal:2', 'is_active' => 'boolean'];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function chargeFor(float $subtotal): float
    {
        if ($this->free_shipping_threshold !== null && $subtotal >= (float) $this->free_shipping_threshold) {
            return 0.0;
        }

        return (float) $this->charge;
    }

    public function estimate(): string
    {
        return $this->min_days === $this->max_days
            ? "{$this->min_days} day".($this->min_days > 1 ? 's' : '')
            : "{$this->min_days}-{$this->max_days} days";
    }
}
