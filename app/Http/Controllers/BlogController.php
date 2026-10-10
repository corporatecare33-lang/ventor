<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Support\Seo;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->string('category')->toString() ?: null;

        $posts = Post::published()->with('media')
            ->when($category, fn ($q) => $q->where('category', $category))
            ->latest('published_at')->paginate(9)->withQueryString();

        return view('blog.index', [
            'posts' => $posts,
            'categories' => Post::published()->whereNotNull('category')->distinct()->pluck('category'),
            'activeCategory' => $category,
            'seo' => $category
                ? Seo::make($category.' — Journal', $category.' articles from the LeatherX journal.')->canonical(route('blog.index', ['category' => $category]))->pageType('CollectionPage')
                : Seo::make('Journal — leather care, guides & news', 'Leather care tips, LeatherX Tap guides and news.')->canonical(route('blog.index'))->pageType('CollectionPage'),
        ]);
    }

    public function show(Post $post): View
    {
        abort_unless($post->is_published && $post->published_at?->isPast(), 404);

        $post->load(['media', 'author:id,name']);

        $seo = Seo::make($post->seo_title ?: $post->title, $post->seo_description ?: $post->excerpt)
            ->type('article')
            ->canonical(route('blog.show', $post))
            ->image($post->getFirstMediaUrl('cover') ?: null)
            ->breadcrumbs([['Home', route('home')], ['Journal', route('blog.index')], [$post->title, route('blog.show', $post)]])
            ->schema(array_filter([
                '@type' => 'BlogPosting',
                'headline' => $post->title,
                'datePublished' => $post->published_at->toIso8601String(),
                'dateModified' => $post->updated_at->toIso8601String(),
                'author' => ['@type' => 'Organization', 'name' => setting('site_name', 'LeatherX')],
                'image' => $post->getFirstMediaUrl('cover') ?: null,
            ]));

        return view('blog.show', [
            'post' => $post,
            'related' => Post::published()->whereKeyNot($post->id)->latest('published_at')->limit(3)->get(),
            'seo' => $seo,
        ]);
    }
}
