<?php

namespace App\Models;

use App\Models\Concerns\FlushesStorefrontCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Manual payment methods controlled from the admin. No live gateway is connected yet:
 * bKash/Nagad payments are verified by an admin against the submitted transaction ID.
 */
class PaymentMethod extends Model
{
    use FlushesStorefrontCache;

    protected $fillable = ['code', 'name', 'instructions', 'account_number', 'requires_transaction_id', 'is_active', 'sort_order'];

    protected $casts = ['requires_transaction_id' => 'boolean', 'is_active' => 'boolean'];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
