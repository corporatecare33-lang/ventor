<?php

namespace App\Models;

use App\Enums\BannerPosition;
use App\Models\Concerns\FlushesStorefrontCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Banner extends Model implements HasMedia
{
    use FlushesStorefrontCache, InteractsWithMedia;

    protected $fillable = [
        'position', 'eyebrow', 'title', 'subtitle', 'button_text', 'button_link',
        'secondary_button_text', 'secondary_button_link', 'sort_order', 'is_active', 'starts_at', 'ends_at',
    ];

    protected $casts = [
        'position' => BannerPosition::class,
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile();
        $this->addMediaCollection('mobile_image')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('mobile')->width(800)->format('webp')->quality(78)->nonQueued();
        $this->addMediaConversion('tablet')->width(1280)->format('webp')->quality(78)->nonQueued();
        $this->addMediaConversion('desktop')->width(1920)->format('webp')->quality(78)->nonQueued();
    }

    public function scopeLive(Builder $query, BannerPosition $position): Builder
    {
        return $query->where('position', $position)
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->orderBy('sort_order');
    }
}
