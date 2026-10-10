<?php

namespace App\Http\Controllers;

use App\Enums\BannerPosition;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Faq;
use App\Models\HomeSection;
use App\Models\Product;
use App\Models\Review;
use App\Support\Seo;
use App\Support\StorefrontCache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $data = StorefrontCache::remember('home', function () {
            $sections = HomeSection::where('is_active', true)->orderBy('sort_order')->get()->keyBy('key');

            $series = Category::active()->where('is_featured', true)->orderBy('sort_order')->get(['id', 'name', 'slug', 'tagline'])
                ->map(function (Category $category) {
                    $category->setRelation('featuredProducts', Product::visible()->forCard()
                        ->where('category_id', $category->id)
                        ->orderByDesc('is_featured')->orderBy('sort_order')->limit(4)->get());

                    return $category;
                })
                ->filter(fn (Category $c) => $c->featuredProducts->isNotEmpty())
                ->values();

            return [
                'hero' => Banner::live(BannerPosition::Hero)->with('media')->get(),
                'sections' => $sections,
                'series' => $series,
                'compare' => Product::visible()->whereHas('category', fn ($q) => $q->whereIn('slug', ['smart-wallets', 'classic-wallets', 'card-holders']))
                    ->orderByDesc('is_featured')->orderBy('sort_order')->limit(3)
                    ->get(['id', 'name', 'slug', 'regular_price', 'sale_price', 'leather_type', 'rfid_protection', 'nfc_enabled', 'warranty_months', 'card_capacity', 'cash_capacity', 'engraving_available']),
                'reviews' => Review::approved()->where('rating', '>=', 4)->with('product:id,name,slug')->latest()->limit(6)->get(),
                'faqs' => Faq::active()->where('show_on_home', true)->limit(6)->get(),
            ];
        }, 900);

        $seo = Seo::make(null, setting('seo_default_description'))
            ->canonical(route('home'))
            ->schema([
                '@type' => 'Organization',
                'name' => setting('site_name', 'LeatherX'),
                'url' => url('/'),
                'contactPoint' => ['@type' => 'ContactPoint', 'telephone' => setting('contact_phone'), 'contactType' => 'customer service', 'areaServed' => 'BD'],
                'sameAs' => array_values(array_filter([setting('social_facebook'), setting('social_instagram'), setting('social_youtube')])),
            ])
            ->schema([
                '@type' => 'WebSite',
                'name' => setting('site_name', 'LeatherX'),
                'url' => url('/'),
                'potentialAction' => ['@type' => 'SearchAction', 'target' => route('shop').'?q={search_term_string}', 'query-input' => 'required name=search_term_string'],
            ]);

        return view('home.index', [...$data, 'seo' => $seo]);
    }
}
