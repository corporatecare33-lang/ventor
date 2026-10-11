<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Page;
use App\Models\PaymentMethod;
use App\Models\Post;
use App\Models\Product;
use App\Models\ShippingZone;
use App\Support\StorefrontCache;
use Illuminate\Http\JsonResponse;
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
            Page::where('is_active', true)->get(['slug', 'updated_at'])
                ->each(fn ($p) => $urls->push([PageController::urlFor($p), $p->updated_at, '0.3']));

            return view('seo.sitemap', ['urls' => $urls])->render();
        }, 3600);

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $lines = setting('seo_allow_indexing', true)
            ? ['User-agent: *', 'Disallow: /admin', 'Disallow: /account', 'Disallow: /cart', 'Disallow: /checkout', 'Disallow: /tap/', 'Disallow: /livewire', 'Disallow: /menu/']
            : ['User-agent: *', 'Disallow: /'];

        if ($extra = setting('robots_extra')) {
            $lines[] = trim($extra);
        }

        $lines[] = '';
        $lines[] = 'Sitemap: '.route('sitemap');

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    /** Plain-text site summary for AI assistants (https://llmstxt.org). Built from live store data. */
    public function llms(): Response
    {
        $text = StorefrontCache::remember('llms', function () {
            $site = setting('site_name', 'LeatherX');
            $lines = [
                '# '.$site,
                '',
                '> '.(setting('seo_default_description') ?: setting('footer_about')),
                '',
                'Online store for leather wallets, card holders, bags, passport and key holders, LeatherX Tap NFC wallets, laser engraving and corporate gifting, delivering within Bangladesh. Prices are in Bangladeshi Taka (BDT).',
                '',
                '## Shop',
                '- [All products]('.route('shop').')',
            ];
            foreach (Category::active()->whereNull('parent_id')->orderBy('sort_order')->get(['name', 'slug', 'tagline']) as $category) {
                $lines[] = '- ['.$category->name.']('.route('categories.show', $category).')'.($category->tagline ? ': '.$category->tagline : '');
            }
            $lines = [...$lines, '', '## Services',
                '- [LeatherX Tap NFC contact profiles]('.route('tap').')',
                '- [Laser engraving]('.route('engraving').')',
                '- [Corporate gifting and bulk orders]('.route('corporate').')',
                '- [Warranty registration and claims]('.route('warranty').')',
                '- [Order tracking]('.route('orders.track').')',
                '', '## Delivery and payment',
            ];
            foreach (ShippingZone::active()->get() as $zone) {
                $lines[] = '- '.$zone->name.': '.money($zone->charge).', '.$zone->estimate();
            }
            $methods = PaymentMethod::active()->pluck('name')->implode(', ');
            if ($methods) {
                $lines[] = '- Payment methods: '.$methods;
            }
            $lines = [...$lines, '', '## Policies and help',
                '- [Delivery policy]('.route('pages.delivery-policy').')',
                '- [Return and exchange policy]('.route('pages.return-policy').')',
                '- [Privacy policy]('.route('pages.privacy-policy').')',
                '- [Terms and conditions]('.route('pages.terms').')',
                '- [FAQ]('.route('faq').')',
                '- [Contact]('.route('contact').')',
                '', '## Optional',
                '- [Journal]('.route('blog.index').')',
                '- [Customer reviews]('.route('reviews.index').')',
                '- [Sitemap]('.route('sitemap').')',
            ];

            return implode("\n", $lines)."\n";
        }, 3600);

        return response($text, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function manifest(): JsonResponse
    {
        return response()->json([
            'name' => setting('site_name', 'LeatherX'),
            'short_name' => setting('site_name', 'LeatherX'),
            'description' => setting('seo_default_description'),
            'start_url' => '/',
            'display' => 'browser',
            'background_color' => '#ffffff',
            'theme_color' => '#16130f',
            'icons' => [
                ['src' => asset('icon-192.png'), 'sizes' => '192x192', 'type' => 'image/png'],
                ['src' => asset('icon-512.png'), 'sizes' => '512x512', 'type' => 'image/png'],
            ],
        ], 200, ['Content-Type' => 'application/manifest+json']);
    }
}
