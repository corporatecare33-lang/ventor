<?php

namespace App\Livewire;

use App\Enums\OptionType;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductVariant;
use App\Support\StorefrontCache;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ProductListing extends Component
{
    use WithPagination;

    #[Locked]
    public ?int $categoryId = null;

    #[Url(except: '')]
    public string $q = '';

    #[Url(except: 'featured')]
    public string $sort = 'featured';

    #[Url(as: 'cat', except: [])]
    public array $categories = [];

    #[Url(except: [])]
    public array $colors = [];

    #[Url(except: [])]
    public array $features = [];

    #[Url(as: 'min', except: null)]
    public ?int $minPrice = null;

    #[Url(as: 'max', except: null)]
    public ?int $maxPrice = null;

    #[Url(as: 'stock', except: false)]
    public bool $inStock = false;

    public const SORTS = [
        'featured' => 'Featured',
        'newest' => 'Newest',
        'popular' => 'Best selling',
        'rating' => 'Top rated',
        'price_asc' => 'Price: low to high',
        'price_desc' => 'Price: high to low',
    ];

    public const FEATURES = [
        'nfc' => 'NFC enabled',
        'rfid' => 'RFID protection',
        'engraving' => 'Engraving available',
        'sale' => 'On sale',
    ];

    public function mount(?Category $category = null): void
    {
        $this->categoryId = $category?->id;
        $this->q = trim(request()->string('q')->toString());
    }

    public function updating(string $property): void
    {
        if ($property !== 'paginators') {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset('q', 'categories', 'colors', 'features', 'minPrice', 'maxPrice', 'inStock');
        $this->sort = 'featured';
        $this->resetPage();
    }

    protected function query(): Builder
    {
        $price = 'CASE WHEN sale_price IS NOT NULL AND sale_price > 0 AND sale_price < regular_price THEN sale_price ELSE regular_price END';

        $query = Product::visible()->forCard()
            ->when($this->categoryId, fn ($q) => $q->where(fn ($q) => $q
                ->where('category_id', $this->categoryId)
                ->orWhereIn('category_id', Category::where('parent_id', $this->categoryId)->select('id'))))
            ->when(! $this->categoryId && $this->categories, fn ($q) => $q->whereIn('category_id', Category::whereIn('slug', $this->categories)->select('id')))
            ->when($this->q !== '', function ($q) {
                $term = '%'.str_replace(['%', '_'], ['\%', '\_'], mb_substr($this->q, 0, 80)).'%';
                $q->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('sku', 'like', $term)->orWhere('short_description', 'like', $term));
            })
            ->when($this->colors, fn ($q) => $q->whereHas('activeVariants', fn ($v) => $v->whereIn('color_id', array_map('intval', $this->colors))))
            ->when(in_array('nfc', $this->features, true), fn ($q) => $q->where('nfc_enabled', true))
            ->when(in_array('rfid', $this->features, true), fn ($q) => $q->where('rfid_protection', true))
            ->when(in_array('engraving', $this->features, true), fn ($q) => $q->where('engraving_available', true))
            ->when(in_array('sale', $this->features, true), fn ($q) => $q->whereNotNull('sale_price')->whereColumn('sale_price', '<', 'regular_price'))
            ->when($this->minPrice, fn ($q) => $q->whereRaw("{$price} >= ?", [$this->minPrice]))
            ->when($this->maxPrice, fn ($q) => $q->whereRaw("{$price} <= ?", [$this->maxPrice]))
            ->when($this->inStock, fn ($q) => $q->where('stock_quantity', '>', 0)->where('status', 'active'));

        return match ($this->sort) {
            'newest' => $query->latest('id'),
            'popular' => $query->orderByDesc('sold_count'),
            'rating' => $query->orderByDesc('rating_avg')->orderByDesc('reviews_count'),
            'price_asc' => $query->orderByRaw("{$price} asc"),
            'price_desc' => $query->orderByRaw("{$price} desc"),
            default => $query->orderByDesc('is_featured')->orderBy('sort_order')->latest('id'),
        };
    }

    public function render()
    {
        $filters = StorefrontCache::remember('shop-filters', fn () => [
            'categories' => Category::active()->whereNull('parent_id')->withCount(['activeProducts'])->orderBy('sort_order')->get(['id', 'name', 'slug']),
            'colors' => ProductOption::ofType(OptionType::Color)->whereIn('id', ProductVariant::where('is_active', true)->select('color_id'))->get(['id', 'name', 'value']),
        ], 1800);

        return view('livewire.product-listing', [
            'products' => $this->query()->paginate(12),
            'filterCategories' => $filters['categories'],
            'filterColors' => $filters['colors'],
            'activeFilterCount' => count($this->categories) + count($this->colors) + count($this->features) + ($this->minPrice ? 1 : 0) + ($this->maxPrice ? 1 : 0) + ($this->inStock ? 1 : 0),
        ]);
    }
}
