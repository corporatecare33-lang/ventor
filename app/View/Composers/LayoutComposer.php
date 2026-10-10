<?php

namespace App\View\Composers;

use App\Enums\BannerPosition;
use App\Models\Banner;
use App\Models\Category;
use App\Support\StorefrontCache;
use Illuminate\View\View;

/** Shared, cached data for the storefront layout (menu, footer, announcement). */
class LayoutComposer
{
    public function compose(View $view): void
    {
        $view->with('layout', StorefrontCache::remember('layout', fn () => [
            'menuCategories' => Category::active()->whereNull('parent_id')->orderBy('sort_order')->get(['id', 'name', 'slug', 'tagline']),
            'announcement' => Banner::live(BannerPosition::Announcement)->first(['title', 'button_link']),
        ], 600));
    }
}
