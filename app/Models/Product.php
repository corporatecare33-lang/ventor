<?php

namespace App\Models;

use App\Enums\ProductStatus;
use App\Models\Concerns\FlushesStorefrontCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use FlushesStorefrontCache, HasFactory, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'category_id', 'name', 'slug', 'sku', 'short_description', 'description',
        'regular_price', 'sale_price', 'stock_quantity', 'low_stock_threshold',
        'leather_type', 'material', 'rfid_protection', 'nfc_enabled', 'warranty_months',
        'card_capacity', 'cash_capacity', 'engraving_available', 'engraving_price',
        'specifications', 'status', 'is_featured', 'sort_order',
        'seo_title', 'seo_description', 'og_title', 'og_description',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProductStatus::class,
            'regular_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'engraving_price' => 'decimal:2',
            'rating_avg' => 'decimal:2',
            'rfid_protection' => 'boolean',
            'nfc_enabled' => 'boolean',
            'engraving_available' => 'boolean',
            'is_featured' => 'boolean',
            'specifications' => 'array',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/avif']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(480)->height(600)->format('webp')->quality(78)->nonQueued();
        $this->addMediaConversion('medium')->width(800)->height(1000)->format('webp')->quality(78)->nonQueued();
        $this->addMediaConversion('large')->width(1200)->height(1500)->format('webp')->quality(78)->nonQueued();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    public function activeVariants(): HasMany
    {
        return $this->variants()->where('is_active', true);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->reviews()->where('is_approved', true)->latest();
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class)->latest();
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** Products visible in the storefront (out-of-stock items stay visible but can't be bought). */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->whereIn('status', [ProductStatus::Active, ProductStatus::OutOfStock]);
    }

    /** Lightweight eager-loads used by every product card. */
    public function scopeForCard(Builder $query): Builder
    {
        return $query->select([
            'id', 'category_id', 'name', 'slug', 'sku', 'short_description', 'regular_price', 'sale_price',
            'stock_quantity', 'status', 'rating_avg', 'reviews_count', 'rfid_protection', 'nfc_enabled',
            'warranty_months', 'card_capacity', 'engraving_available', 'leather_type', 'is_featured', 'sold_count',
        ])->with([
            'category:id,name,slug',
            'activeVariants:id,product_id,color_id,stock_quantity',
            'activeVariants.color:id,name,value',
            'media',
        ]);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function currentPrice(): float
    {
        return (float) ($this->hasDiscount() ? $this->sale_price : $this->regular_price);
    }

    public function hasDiscount(): bool
    {
        return $this->sale_price !== null && (float) $this->sale_price > 0 && (float) $this->sale_price < (float) $this->regular_price;
    }

    public function discountPercent(): int
    {
        return $this->hasDiscount()
            ? (int) round((1 - (float) $this->sale_price / (float) $this->regular_price) * 100)
            : 0;
    }

    /** Total sellable stock: variants when present, otherwise the product itself. */
    public function availableStock(): int
    {
        if ($this->relationLoaded('activeVariants') ? $this->activeVariants->isNotEmpty() : $this->activeVariants()->exists()) {
            return (int) $this->activeVariants->sum('stock_quantity');
        }

        return (int) $this->stock_quantity;
    }

    public function isPurchasable(): bool
    {
        return $this->status === ProductStatus::Active && $this->availableStock() > 0;
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->low_stock_threshold;
    }

    /** @return array<int, array{name: string, hex: ?string}> */
    public function colorSwatches(): array
    {
        return $this->activeVariants
            ->pluck('color')
            ->filter()
            ->unique('id')
            ->map(fn (ProductOption $color) => ['name' => $color->name, 'hex' => $color->value])
            ->values()
            ->all();
    }

    /**
     * Colour shown by the material tile when there is no photo: the lightest real
     * colourway, because it shows the leather best (black reads as an empty slab).
     */
    public function primaryColor(): ?ProductOption
    {
        return $this->activeVariants->pluck('color')->filter()
            ->sortByDesc(fn (ProductOption $color) => self::luminance((string) $color->value))
            ->first();
    }

    private static function luminance(string $hex): float
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) !== 6) {
            return 0.0;
        }
        [$r, $g, $b] = array_map(fn ($c) => hexdec($c) / 255, str_split($hex, 2));

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    /** Short feature line for cards, built only from real attributes. */
    public function featureLine(): string
    {
        return collect([
            $this->nfc_enabled ? 'NFC' : null,
            $this->rfid_protection ? 'RFID-blocking' : null,
            $this->card_capacity ? $this->card_capacity.' cards' : null,
            $this->engraving_available ? 'Engravable' : null,
        ])->filter()->take(3)->implode(' · ');
    }

    public function thumbUrl(): ?string
    {
        $media = $this->getFirstMedia('images');

        return $media?->getAvailableUrl(['thumb']);
    }

    public function metaTitle(): string
    {
        return $this->seo_title ?: $this->name;
    }

    public function metaDescription(): string
    {
        return $this->seo_description ?: str($this->short_description ?: strip_tags((string) $this->description))->limit(155)->toString();
    }

    public function recalculateRating(): void
    {
        $stats = $this->reviews()->where('is_approved', true)
            ->selectRaw('COUNT(*) as total, COALESCE(AVG(rating), 0) as average')
            ->first();

        $this->forceFill([
            'reviews_count' => (int) $stats->total,
            'rating_avg' => round((float) $stats->average, 2),
        ])->saveQuietly();
    }
}
