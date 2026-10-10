<?php

namespace App\Models;

use App\Enums\EngravingStatus;
use App\Enums\EngravingType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EngravingRequest extends Model
{
    protected $fillable = [
        'user_id', 'order_id', 'order_item_id', 'product_id', 'customer_name', 'phone', 'email',
        'type', 'text', 'font', 'position', 'logo_instruction', 'quantity', 'fee', 'status', 'admin_note',
    ];

    protected $casts = ['type' => EngravingType::class, 'status' => EngravingStatus::class, 'fee' => 'decimal:2'];

    public const FONTS = ['Classic Serif', 'Modern Sans', 'Script', 'Block Capitals'];

    public const POSITIONS = ['Front bottom corner', 'Front center', 'Inside panel', 'Back center'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }
}
