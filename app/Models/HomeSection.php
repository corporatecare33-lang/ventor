<?php

namespace App\Models;

use App\Models\Concerns\FlushesStorefrontCache;
use Illuminate\Database\Eloquent\Model;

/**
 * Editable homepage blocks. Each `key` maps to a Blade partial in resources/views/home/sections.
 */
class HomeSection extends Model
{
    use FlushesStorefrontCache;

    protected $fillable = ['key', 'title', 'subtitle', 'content', 'is_active', 'sort_order'];

    protected $casts = ['content' => 'array', 'is_active' => 'boolean'];

    public const KEYS = [
        'categories' => 'Shop by category',
        'featured' => 'Featured products',
        'tap_intro' => 'LeatherX Tap explainer',
        'comparison' => 'Wallet comparison table',
        'engraving' => 'Laser engraving',
        'corporate' => 'Corporate gifting',
        'reviews' => 'Customer reviews (shown only when approved reviews exist)',
        'faq' => 'FAQ',
    ];
}
