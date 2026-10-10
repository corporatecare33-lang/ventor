<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Support\Seo;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageController extends Controller
{
    /** CMS pages that have their own short URL (/about, /return-policy …). */
    public const FIXED = ['about', 'return-policy', 'privacy-policy', 'terms-and-conditions', 'delivery-policy'];

    public static function urlFor(Page $page): string
    {
        return in_array($page->slug, self::FIXED, true) ? url($page->slug) : route('pages.show', $page);
    }

    public function show(Page $page): View|RedirectResponse
    {
        abort_unless($page->is_active, 404);

        // One URL per page: /page/about permanently redirects to /about.
        if (in_array($page->slug, self::FIXED, true)) {
            return redirect(url($page->slug), 301);
        }

        return $this->render($page);
    }

    /** Fixed URLs such as /about and /return-policy backed by CMS pages. */
    public function fixed(string $slug): View
    {
        return $this->render(Page::where('slug', $slug)->where('is_active', true)->firstOrFail());
    }

    private function render(Page $page): View
    {
        $url = self::urlFor($page);
        $seo = Seo::make($page->seo_title ?: $page->title, $page->seo_description ?: str(strip_tags((string) $page->content))->squish()->limit(155))
            ->canonical($url)
            ->pageType($page->slug === 'about' ? 'AboutPage' : 'WebPage')
            ->breadcrumbs([['Home', route('home')], [$page->title, $url]]);

        return view('pages.show', compact('page', 'seo'));
    }
}
