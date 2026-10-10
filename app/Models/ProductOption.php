<?php

namespace App\Models;

use App\Enums\OptionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProductOption extends Model
{
    protected $fillable = ['type', 'name', 'value', 'sort_order', 'is_active'];

    protected $casts = ['type' => OptionType::class, 'is_active' => 'boolean'];

    public function scopeOfType(Builder $query, OptionType $type): Builder
    {
        return $query->where('type', $type)->where('is_active', true)->orderBy('sort_order');
    }
}
