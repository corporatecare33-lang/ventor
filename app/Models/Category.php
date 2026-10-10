<?php

namespace App\Models;

use App\Enums\ProductStatus;
use App\Models\Concerns\FlushesStorefrontCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Category extends Model implements HasMedia
{
    use FlushesStorefrontCache, HasFactory, InteractsWithMedia;

    protected $fillable = [
        'parent_id', 'name', 'slug', 'tagline', 'description', 'sort_order',
        'is_active', 'is_featured', 'seo_title', 'seo_description',
    ];

    protected $casts = ['is_active' => 'boolean', 'is_featured' => 'boolean'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(160)->height(160)->format('webp')->quality(78)->nonQueued();
        $this->addMediaConversion('card')->width(640)->height(800)->format('webp')->quality(78)->nonQueued();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function activeProducts(): HasMany
    {
        return $this->products()->whereIn('status', [ProductStatus::Active, ProductStatus::OutOfStock]);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
