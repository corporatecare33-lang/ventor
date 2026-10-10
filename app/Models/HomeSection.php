<?php

namespace App\Models;

use App\Models\Concerns\FlushesStorefrontCache;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Editable homepage blocks. Each `key` maps to a Blade partial in resources/views/home/sections.
 */
class HomeSection extends Model implements HasMedia
{
    use FlushesStorefrontCache, InteractsWithMedia;

    protected $fillable = ['key', 'title', 'subtitle', 'content', 'is_active', 'sort_order'];

    protected $casts = ['content' => 'array', 'is_active' => 'boolean'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('mobile')->width(800)->format('webp')->quality(78)->nonQueued();
        $this->addMediaConversion('desktop')->width(1400)->format('webp')->quality(78)->nonQueued();
    }

    public const KEYS = [
        'tap_intro' => 'LeatherX Tap explainer',
        'collections' => 'Product collections (one per featured category); title/subtitle head the accessories carousel',
        'comparison' => 'Collection comparison table (shown after the first collection)',
        'engraving' => 'Laser engraving',
        'corporate' => 'Corporate gifting',
        'reviews' => 'Customer reviews (shown only when approved reviews exist)',
        'faq' => 'FAQ',
    ];
}
