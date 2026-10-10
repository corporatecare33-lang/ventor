<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number', 'user_id', 'customer_name', 'phone', 'email', 'address', 'area', 'city',
        'district', 'postal_code', 'shipping_zone_id', 'shipping_zone_name', 'subtotal',
        'engraving_total', 'discount', 'shipping_charge', 'total', 'coupon_id', 'coupon_code',
        'payment_method', 'payment_status', 'status', 'courier_name', 'tracking_number',
        'customer_note', 'admin_note', 'ip_address', 'confirmed_at', 'shipped_at', 'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'subtotal' => 'decimal:2',
            'engraving_total' => 'decimal:2',
            'discount' => 'decimal:2',
            'shipping_charge' => 'decimal:2',
            'total' => 'decimal:2',
            'confirmed_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->latest();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest();
    }

    public function engravingRequests(): HasMany
    {
        return $this->hasMany(EngravingRequest::class);
    }

    public function shippingZone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function paymentMethodModel(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method', 'code');
    }

    public function getRouteKeyName(): string
    {
        return 'order_number';
    }

    public function fullAddress(): string
    {
        return collect([$this->address, $this->area, $this->city, $this->district, $this->postal_code])->filter()->implode(', ');
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, [OrderStatus::Pending, OrderStatus::Confirmed], true);
    }

    public static function generateNumber(): string
    {
        do {
            $number = 'LX'.now()->format('ymd').strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
        } while (static::where('order_number', $number)->exists());

        return $number;
    }
}
