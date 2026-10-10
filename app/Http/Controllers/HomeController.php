<?php

namespace App\Http\Controllers;

use App\Enums\BannerPosition;
use App\Models\Banner;
use App\Models\Category;
use App\Models\EngravingRequest;
use App\Models\Faq;
use App\Models\HomeSection;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Review;
use App\Models\ShippingZone;
use App\Support\CollectionSummary;
use App\Support\Seo;
use App\Support\StorefrontCache;
use Illuminate\View\View;

class HomeController extends Controller
{
    /** Categories shown together in the "Smart additions" carousel instead of their own collection grid. */
    private const ADDITIONS = ['smart-accessories', 'key-holders', 'accessories'];

    /** The two collections compared side by side after the first collection. */
    private const COMPARE = ['smart-wallets', 'classic-wallets'];

    public function __invoke(): View
    {
        $data = StorefrontCache::remember('home', function () {
            // Each featured category is a homepage collection: title, feature line and its products.
            $collections = Category::active()->whereNull('parent_id')->where('is_featured', true)
                ->whereNotIn('slug', self::ADDITIONS)->withCount('activeProducts')->orderBy('sort_order')
                ->get(['id', 'name', 'slug', 'tagline'])
                ->map(function (Category $category) {
                    $all = Product::visible()->forCard()->where('category_id', $category->id)
                        ->orderByDesc('is_featured')->orderBy('sort_order')->get();

                    return [
                        'category' => $category,
                        'products' => $all->take(8)->values(),
                        'features' => CollectionSummary::features($all),
                        'all' => $all,
                    ];
                })
                ->filter(fn (array $c) => $c['products']->isNotEmpty())->values();

            $pair = $collections->filter(fn (array $c) => in_array($c['category']->slug, self::COMPARE, true))
                ->sortBy(fn (array $c) => array_search($c['category']->slug, self::COMPARE, true))->values();
            $comparison = $pair->count() === 2 ? [
                'collections' => [$pair[0]['category'], $pair[1]['category']],
                'rows' => CollectionSummary::compare($pair[0]['category'], $pair[0]['all'], $pair[1]['category'], $pair[1]['all']),
            ] : null;

            $collections = $collections->map(fn (array $c) => collect($c)->except('all')->all());

            $zones = ShippingZone::active()->get();
            $visible = Product::visible();

            return [
                'heroes' => Banner::live(BannerPosition::Hero)->with('media')->get(),
                // Every fact in the hero is computed from live store data.
                'facts' => array_filter([
                    'delivery' => $zones->isNotEmpty() ? [
                        'label' => 'Delivery',
                        'value' => 'From '.money($zones->min('charge')),
                        'note' => $zones->sortBy('min_days')->first()->name.', '.$zones->sortBy('min_days')->first()->estimate(),
                        'icon' => 'truck',
                    ] : null,
                    'cod' => PaymentMethod::active()->where('code', 'cod')->exists() ? [
                        'label' => 'Payment',
                        'value' => 'Cash on delivery',
                        'note' => PaymentMethod::active()->where('code', '!=', 'cod')->pluck('name')->map(fn ($n) => str($n)->before(' ('))->implode(', ') ?: 'Pay when it arrives',
                        'icon' => 'cash',
                    ] : null,
                    'warranty' => ($months = (clone $visible)->max('warranty_months')) ? [
                        'label' => 'Warranty',
                        'value' => 'Up to '.($months % 12 === 0 ? ($months / 12).'-year' : $months.'-month').' warranty',
                        'note' => 'Stitching, edges and hardware',
                        'icon' => 'shield',
                    ] : null,
                    'nfc' => (clone $visible)->where('nfc_enabled', true)->exists() ? [
                        'label' => 'LeatherX Tap',
                        'value' => 'Built-in NFC',
                        'note' => 'Share your contact card with one tap',
                        'icon' => 'signal',
                    ] : null,
                    'engraving' => ($price = (clone $visible)->where('engraving_available', true)->min('engraving_price')) !== null ? [
                        'label' => 'Engraving',
                        'value' => 'Engraving from '.money($price),
                        'note' => 'Names, initials or logos',
                        'icon' => 'pen',
                    ] : null,
                ]),
                'sections' => HomeSection::where('is_active', true)->with('media')->orderBy('sort_order')->get()->keyBy('key'),
                // Brand promises for the scrolling strip — each shown only when the catalogue backs it up.
                'promises' => array_values(array_filter([
                    (clone $visible)->where('leather_type', 'like', '%full-grain%')->exists() ? 'Full-grain leather' : null,
                    (clone $visible)->where('rfid_protection', true)->exists() ? 'RFID-blocking linings' : null,
                    (clone $visible)->where('nfc_enabled', true)->exists() ? 'LeatherX Tap™ NFC' : null,
                    (clone $visible)->where('engraving_available', true)->exists() ? 'Laser engraving' : null,
                    PaymentMethod::active()->where('code', 'cod')->exists() ? 'Cash on delivery' : null,
                    $zones->isNotEmpty() ? 'Delivery across Bangladesh' : null,
                ])),
                'collections' => $collections,
                'comparison' => $comparison,
                'additions' => Product::visible()->forCard()
                    ->whereHas('category', fn ($query) => $query->whereIn('slug', self::ADDITIONS))
                    ->orderByDesc('is_featured')->orderBy('sort_order')->limit(12)->get(),
                'engraving' => [
                    'from' => ($price = Product::visible()->where('engraving_available', true)->min('engraving_price')) !== null ? money($price) : null,
                    'pieces' => Product::visible()->where('engraving_available', true)->count(),
                    'styles' => count(EngravingRequest::FONTS),
                ],
                'corporateProduct' => Product::visible()->forCard()->whereHas('category', fn ($q) => $q->where('slug', 'corporate-gifts'))->orderBy('sort_order')->first(),
                'reviews' => Review::approved()->where('rating', '>=', 4)->whereHas('product', fn ($query) => $query->visible()->withoutTrashed())
                    ->with('product:id,name,slug')->latest()->limit(8)->get(),
                'reviewStats' => ['average' => round((float) Review::approved()->avg('rating'), 1), 'count' => Review::approved()->count()],
                'faqs' => Faq::active()->where('show_on_home', true)->limit(6)->get(),
                'fonts' => EngravingRequest::FONTS,
            ];
        }, 900);

        $hero = $data['heroes']->first();

        $seo = Seo::make(null, setting('seo_default_description'))
            ->home()
            ->canonical(route('home'))
            ->image($hero?->getFirstMediaUrl('image', 'desktop') ?: null);

        return view('home.index', [...$data, 'hero' => $hero, 'seo' => $seo]);
    }
}
