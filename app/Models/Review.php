<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = ['product_id', 'user_id', 'order_id', 'name', 'rating', 'title', 'body', 'is_approved', 'is_verified_purchase', 'admin_reply'];

    protected $casts = ['is_approved' => 'boolean', 'is_verified_purchase' => 'boolean'];

    protected static function booted(): void
    {
        $sync = fn (Review $review) => $review->product?->recalculateRating();

        static::saved($sync);
        static::deleted($sync);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('is_approved', true);
    }
}
