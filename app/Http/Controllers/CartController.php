<?php

namespace App\Http\Controllers;

use App\Support\Seo;
use Illuminate\View\View;

/** Thin wrappers: the cart and checkout UIs are Livewire components. */
class CartController extends Controller
{
    public function cart(): View
    {
        return view('cart.index', ['seo' => Seo::make('Your cart')->noindex()]);
    }

    public function checkout(): View
    {
        return view('cart.checkout', ['seo' => Seo::make('Checkout')->noindex()]);
    }
}
