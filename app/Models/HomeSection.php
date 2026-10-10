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
        'trust_badges' => 'Trust Badges',
        'tap_intro' => 'What is LeatherX Tap?',
        'series' => 'Featured Product Series',
        'comparison' => 'Product Comparison Table',
        'engraving' => 'Laser Engraving Workflow',
        'corporate' => 'Corporate Gifting',
        'reviews' => 'Customer Reviews',
        'delivery_payment' => 'Delivery & Payment Trust',
        'faq' => 'FAQ',
    ];
}
