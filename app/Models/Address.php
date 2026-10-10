<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    protected $fillable = ['user_id', 'label', 'name', 'phone', 'address', 'area', 'city', 'district', 'postal_code', 'is_default'];

    protected $casts = ['is_default' => 'boolean'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFullAddressAttribute(): string
    {
        return collect([$this->address, $this->area, $this->city, $this->district, $this->postal_code])->filter()->implode(', ');
    }
}
