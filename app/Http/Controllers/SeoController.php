<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use App\Support\StorefrontCache;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $xml = StorefrontCache::remember('sitemap', function () {
            $urls = collect([
                [route('home'), now(), '1.0'],
                [route('shop'), now(), '0.9'],
                [route('tap'), null, '0.8'],
                [route('engraving'), null, '0.6'],
                [route('corporate'), null, '0.6'],
                [route('warranty'), null, '0.4'],
                [route('reviews.index'), null, '0.5'],
                [route('faq'), null, '0.5'],
                [route('contact'), null, '0.4'],
                [route('blog.index'), null, '0.6'],
            ]);

            Category::active()->get(['slug', 'updated_at'])->each(fn ($c) => $urls->push([route('categories.show', $c), $c->updated_at, '0.8']));
            Product::visible()->get(['slug', 'updated_at'])->each(fn ($p) => $urls->push([route('products.show', $p), $p->updated_at, '0.8']));
            Post::published()->get(['slug', 'updated_at'])->each(fn ($p) => $urls->push([route('blog.show', $p), $p->updated_at, '0.5']));
            Page::where('is_active', true)->get(['slug', 'updated_at'])->each(function ($p) use ($urls) {
                $fixed = ['about', 'return-policy', 'privacy-policy', 'terms-and-conditions', 'delivery-policy'];
                $urls->push([in_array($p->slug, $fixed, true) ? url($p->slug) : route('pages.show', $p), $p->updated_at, '0.3']);
            });

            return view('seo.sitemap', ['urls' => $urls])->render();
        }, 3600);

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $lines = setting('seo_allow_indexing', true)
            ? ['User-agent: *', 'Disallow: /admin', 'Disallow: /account', 'Disallow: /cart', 'Disallow: /checkout', 'Disallow: /tap/', 'Disallow: /livewire']
            : ['User-agent: *', 'Disallow: /'];

        if ($extra = setting('robots_extra')) {
            $lines[] = trim($extra);
        }

        $lines[] = '';
        $lines[] = 'Sitemap: '.route('sitemap');

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
