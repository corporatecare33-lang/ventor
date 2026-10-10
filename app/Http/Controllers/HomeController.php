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
use App\Support\Seo;
use App\Support\StorefrontCache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $data = StorefrontCache::remember('home', function () {
            $featured = Product::visible()->forCard()->where('is_featured', true)
                ->orderBy('sort_order')->limit(8)->get();

            $zones = ShippingZone::active()->get();
            $visible = Product::visible();

            return [
                'hero' => Banner::live(BannerPosition::Hero)->with('media')->first(),
                // Every fact in the hero is computed from live store data.
                'facts' => array_filter([
                    'delivery' => $zones->isNotEmpty() ? [
                        'label' => 'Delivery',
                        'value' => 'From '.money($zones->min('charge')),
                        'note' => $zones->sortBy('min_days')->first()->name.', '.$zones->sortBy('min_days')->first()->estimate(),
                    ] : null,
                    'cod' => PaymentMethod::active()->where('code', 'cod')->exists() ? [
                        'label' => 'Payment',
                        'value' => 'Cash on delivery',
                        'note' => PaymentMethod::active()->where('code', '!=', 'cod')->pluck('name')->map(fn ($n) => str($n)->before(' ('))->implode(', ') ?: 'Pay when it arrives',
                    ] : null,
                    'warranty' => ($months = (clone $visible)->max('warranty_months')) ? [
                        'label' => 'Warranty',
                        'value' => 'Up to '.($months % 12 === 0 ? ($months / 12).' '.str('year')->plural($months / 12) : $months.' months'),
                        'note' => 'Stitching, edges and hardware',
                    ] : null,
                    'engraving' => ($price = (clone $visible)->where('engraving_available', true)->min('engraving_price')) !== null ? [
                        'label' => 'Engraving',
                        'value' => 'From '.money($price),
                        'note' => 'Names, initials or logos',
                    ] : null,
                ]),
                'sections' => HomeSection::where('is_active', true)->with('media')->orderBy('sort_order')->get()->keyBy('key'),
                'categories' => Category::active()->whereNull('parent_id')->withCount('activeProducts')->with('media')
                    ->orderBy('sort_order')->get(['id', 'name', 'slug', 'tagline'])
                    ->filter(fn (Category $c) => $c->active_products_count > 0)->values(),
                'featured' => $featured,
                'compare' => Product::visible()->whereHas('category', fn ($q) => $q->whereIn('slug', ['smart-wallets', 'classic-wallets', 'card-holders']))
                    ->where('is_featured', true)->orderBy('sort_order')->limit(3)
                    ->get(['id', 'name', 'slug', 'regular_price', 'sale_price', 'leather_type', 'rfid_protection', 'nfc_enabled', 'warranty_months', 'card_capacity', 'cash_capacity', 'engraving_available']),
                'corporateProduct' => Product::visible()->forCard()->whereHas('category', fn ($q) => $q->where('slug', 'corporate-gifts'))->orderBy('sort_order')->first(),
                'reviews' => Review::approved()->where('rating', '>=', 4)->with('product:id,name,slug')->latest()->limit(3)->get(),
                'faqs' => Faq::active()->where('show_on_home', true)->limit(6)->get(),
                'fonts' => EngravingRequest::FONTS,
            ];
        }, 900);

        $seo = Seo::make(null, setting('seo_default_description'))
            ->home()
            ->canonical(route('home'))
            ->image($data['hero']?->getFirstMediaUrl('image', 'desktop') ?: null);

        return view('home.index', [...$data, 'seo' => $seo]);
    }
}
