<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\ShippingZone;
use App\Support\Seo;
use App\Support\StorefrontCache;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function show(Product $product): View
    {
        abort_if($product->status === ProductStatus::Draft && ! auth()->user()?->isStaff(), 404);

        $product->load([
            'category:id,name,slug',
            'media',
            'activeVariants' => fn ($q) => $q->with(['color', 'material', 'size']),
        ]);

        $reviews = $product->approvedReviews()->limit(10)->get();
        $ratingBreakdown = $product->reviews()->where('is_approved', true)
            ->selectRaw('rating, COUNT(*) as total')->groupBy('rating')->pluck('total', 'rating');

        $related = StorefrontCache::remember("related:{$product->id}", fn () => Product::visible()->forCard()
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->orderByDesc('sold_count')->limit(4)->get(), 1800);

        $zones = StorefrontCache::remember('shipping_zones', fn () => ShippingZone::active()->get(), 3600);

        $images = $product->getMedia('images');
        $url = route('products.show', $product);

        $seo = Seo::make($product->metaTitle(), $product->metaDescription())
            ->type('product')
            ->canonical($url)
            ->image($images->first()?->getUrl())
            ->breadcrumbs(array_values(array_filter([
                ['Home', route('home')],
                ['Shop', route('shop')],
                $product->category ? [$product->category->name, route('categories.show', $product->category)] : null,
                [$product->name, $url],
            ])))
            ->schema(array_filter([
                '@type' => 'Product',
                'name' => $product->name,
                'sku' => $product->sku,
                'description' => $product->metaDescription(),
                'image' => $images->map->getUrl()->all() ?: null,
                'brand' => ['@type' => 'Brand', 'name' => setting('site_name', 'LeatherX')],
                'category' => $product->category?->name,
                'material' => $product->leather_type,
                'offers' => [
                    '@type' => 'Offer',
                    'url' => $url,
                    'priceCurrency' => 'BDT',
                    'price' => number_format($product->currentPrice(), 2, '.', ''),
                    'availability' => $product->isPurchasable() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                    'itemCondition' => 'https://schema.org/NewCondition',
                ],
                'aggregateRating' => $product->reviews_count > 0 ? [
                    '@type' => 'AggregateRating',
                    'ratingValue' => (float) $product->rating_avg,
                    'reviewCount' => $product->reviews_count,
                ] : null,
            ]));

        return view('products.show', compact('product', 'reviews', 'ratingBreakdown', 'related', 'zones', 'seo'));
    }
}
