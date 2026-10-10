<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Support\Seo;
use Illuminate\View\View;

/** The product grid itself is the Livewire `ProductListing` component (filters, sort, pagination). */
class ShopController extends Controller
{
    public function index(): View
    {
        $seo = Seo::make('Shop all leather wallets, bags & accessories', 'Browse LeatherX wallets, NFC smart wallets, card holders, bags, passport holders and corporate gifts.')
            ->canonical(route('shop'))
            ->breadcrumbs([['Home', route('home')], ['Shop', route('shop')]]);

        return view('shop.index', ['category' => null, 'seo' => $seo]);
    }

    public function category(Category $category): View
    {
        abort_unless($category->is_active, 404);

        $seo = Seo::make($category->seo_title ?: $category->name, $category->seo_description ?: $category->description)
            ->canonical(route('categories.show', $category))
            ->image($category->getFirstMediaUrl('image') ?: null)
            ->breadcrumbs([['Home', route('home')], ['Shop', route('shop')], [$category->name, route('categories.show', $category)]]);

        return view('shop.index', ['category' => $category, 'seo' => $seo]);
    }
}
