<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Support\Seo;
use Illuminate\View\View;

class PageController extends Controller
{
    public function show(Page $page): View
    {
        abort_unless($page->is_active, 404);

        return $this->render($page);
    }

    /** Fixed URLs such as /about and /return-policy backed by CMS pages. */
    public function fixed(string $slug): View
    {
        return $this->render(Page::where('slug', $slug)->where('is_active', true)->firstOrFail());
    }

    private function render(Page $page): View
    {
        $seo = Seo::make($page->seo_title ?: $page->title, $page->seo_description ?: str(strip_tags((string) $page->content))->limit(155))
            ->canonical(url()->current())
            ->breadcrumbs([['Home', route('home')], [$page->title, url()->current()]]);

        return view('pages.show', compact('page', 'seo'));
    }
}
