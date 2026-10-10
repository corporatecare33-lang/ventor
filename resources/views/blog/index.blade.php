@extends('layouts.app')

@section('content')
<x-page-header eyebrow="Journal" title="Stories, care guides & news" :crumbs="['Journal' => null]" />
<section class="container-lx py-14">
    @if ($categories->isNotEmpty())
        <div class="mb-10 flex flex-wrap gap-2">
            <a href="{{ route('blog.index') }}" class="rounded-full px-4 py-2 text-xs font-medium {{ ! $activeCategory ? 'bg-ink-900 text-white' : 'border border-ink-900/15' }}">All</a>
            @foreach ($categories as $cat)
                <a href="{{ route('blog.index', ['category' => $cat]) }}" class="rounded-full px-4 py-2 text-xs font-medium {{ $activeCategory === $cat ? 'bg-ink-900 text-white' : 'border border-ink-900/15' }}">{{ $cat }}</a>
            @endforeach
        </div>
    @endif
    <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($posts as $post)
            <article class="group">
                <a href="{{ route('blog.show', $post) }}" class="block aspect-[16/10] overflow-hidden rounded-2xl bg-cream-100">
                    @if ($cover = $post->getFirstMedia('cover'))
                        <img src="{{ $cover->getAvailableUrl(['card']) }}" alt="{{ $post->title }}" class="size-full object-cover transition duration-500 group-hover:scale-[1.03]" loading="lazy" width="800" height="500">
                    @else
                        <div class="placeholder-leather grid size-full place-items-center"><x-lx-icon name="document" class="size-10 text-tan-300/50" /></div>
                    @endif
                </a>
                <p class="mt-4 text-xs text-ink-600">{{ $post->category }} · {{ $post->published_at->format('d M Y') }} · {{ $post->readingMinutes() }} min read</p>
                <h2 class="mt-2 font-display text-2xl leading-snug font-semibold"><a href="{{ route('blog.show', $post) }}" class="hover:text-leather-700">{{ $post->title }}</a></h2>
                @if ($post->excerpt)<p class="mt-2 text-sm leading-6 text-ink-600">{{ $post->excerpt }}</p>@endif
            </article>
        @empty
            <p class="text-sm text-ink-600">No posts yet.</p>
        @endforelse
    </div>
    <div class="mt-12">{{ $posts->links('components.pagination') }}</div>
</section>
@endsection
