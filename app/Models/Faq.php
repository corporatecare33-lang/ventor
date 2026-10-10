<?php

namespace App\Models;

use App\Models\Concerns\FlushesStorefrontCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use FlushesStorefrontCache;

    protected $fillable = ['category', 'question', 'answer', 'show_on_home', 'is_active', 'sort_order'];

    protected $casts = ['show_on_home' => 'boolean', 'is_active' => 'boolean'];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
