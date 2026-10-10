<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = ['order_id', 'method', 'amount', 'transaction_id', 'sender_number', 'status', 'verified_by', 'verified_at', 'note'];

    protected $casts = ['status' => TransactionStatus::class, 'amount' => 'decimal:2', 'verified_at' => 'datetime'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
