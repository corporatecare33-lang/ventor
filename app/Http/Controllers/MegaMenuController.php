<?php

namespace App\Http\Controllers;

use App\Support\MegaMenu;
use App\Support\StorefrontCache;
use Illuminate\Http\Response;

/** HTML fragment with the product rows of one mega-menu group, fetched when the menu first opens. */
class MegaMenuController extends Controller
{
    public function __invoke(string $group): Response
    {
        abort_unless(array_key_exists($group, MegaMenu::GROUPS), 404);

        $html = StorefrontCache::remember('menu.'.$group, fn () => view('partials.mega-menu-products', [
            'rows' => MegaMenu::products($group),
        ])->render(), 600);

        return response($html)
            ->header('Cache-Control', 'public, max-age=120')
            ->header('X-Robots-Tag', 'noindex');
    }
}
