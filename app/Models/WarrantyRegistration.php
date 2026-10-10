<?php

namespace App\Models;

use App\Enums\WarrantyStatus;
use App\Enums\WarrantyType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarrantyRegistration extends Model
{
    protected $fillable = [
        'reference', 'type', 'user_id', 'order_id', 'product_id', 'customer_name', 'phone', 'email',
        'order_number', 'product_name', 'purchase_date', 'expires_at', 'issue_details', 'status', 'admin_note',
    ];

    protected $casts = [
        'type' => WarrantyType::class,
        'status' => WarrantyStatus::class,
        'purchase_date' => 'date',
        'expires_at' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (WarrantyRegistration $warranty) {
            $prefix = $warranty->type === WarrantyType::Claim ? 'WC' : 'WR';
            $warranty->reference ??= $prefix.now()->format('ymd').strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function isActive(): bool
    {
        return $this->expires_at !== null && $this->expires_at->endOfDay()->isFuture();
    }
}
