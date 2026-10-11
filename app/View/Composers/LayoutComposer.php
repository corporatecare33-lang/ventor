<?php

namespace App\View\Composers;

use App\Enums\BannerPosition;
use App\Models\Banner;
use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\ShippingZone;
use App\Support\MegaMenu;
use App\Support\StorefrontCache;
use Illuminate\View\View;

/** Shared, cached data for the storefront layout (menu, footer, service information). */
class LayoutComposer
{
    public function compose(View $view): void
    {
        $view->with('layout', StorefrontCache::remember('layout', fn () => [
            'menuCategories' => $categories = Category::active()->whereNull('parent_id')->withCount('activeProducts')
                ->orderBy('sort_order')->get(['id', 'name', 'slug', 'tagline']),
            'megaMenu' => MegaMenu::groups($categories),
            'announcement' => Banner::live(BannerPosition::Announcement)->first(['title', 'button_link']),
            'zones' => ShippingZone::active()->get(['id', 'name', 'charge', 'free_shipping_threshold', 'min_days', 'max_days']),
            'paymentMethods' => PaymentMethod::active()->pluck('name')->all(),
        ], 600));
    }
}
