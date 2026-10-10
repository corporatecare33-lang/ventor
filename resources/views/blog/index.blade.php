@extends('layouts.app')

@section('content')
<x-ui.page-header title="Journal" intro="Leather care, LeatherX Tap guides and news from the workshop." :crumbs="['Journal' => null]">
    @if ($categories->isNotEmpty())
        <nav class="mt-8 flex flex-wrap gap-2" aria-label="Journal topics">
            <a href="{{ route('blog.index') }}" @if (! $activeCategory) aria-current="page" @endif class="inline-flex min-h-9 items-center rounded-xs border px-3.5 text-sm {{ ! $activeCategory ? 'border-ink-950 bg-ink-950 text-white' : 'border-line-strong hover:border-ink-950' }}">All</a>
            @foreach ($categories as $cat)
                <a href="{{ route('blog.index', ['category' => $cat]) }}" @if ($activeCategory === $cat) aria-current="page" @endif class="inline-flex min-h-9 items-center rounded-xs border px-3.5 text-sm {{ $activeCategory === $cat ? 'border-ink-950 bg-ink-950 text-white' : 'border-line-strong hover:border-ink-950' }}">{{ $cat }}</a>
            @endforeach
        </nav>
    @endif
</x-ui.page-header>
<section class="container-site py-12 lg:py-16">
    <h2 class="sr-only">Articles</h2>
    @if ($posts->isEmpty())
        <x-ui.empty-state title="No articles yet" text="New guides are published here regularly." />
    @else
        <ul class="divide-y divide-line border-y border-line">
            @foreach ($posts as $post)
                <li>
                    <article class="grid gap-6 py-8 sm:grid-cols-[minmax(0,1fr)_14rem] lg:grid-cols-[12rem_minmax(0,1fr)_16rem] lg:gap-10">
                        <p class="text-[13px] text-ink-500 lg:pt-2"><time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('j F Y') }}</time><span class="block">{{ $post->category }} · {{ $post->readingMinutes() }} min read</span></p>
                        <div>
                            <h3 class="font-display text-[1.75rem] leading-tight text-ink-950"><a href="{{ route('blog.show', $post) }}" class="underline-offset-4 hover:underline">{{ $post->title }}</a></h3>
                            @if ($post->excerpt)<p class="mt-3 max-w-xl text-[15px] leading-relaxed text-ink-600">{{ $post->excerpt }}</p>@endif
                            <a href="{{ route('blog.show', $post) }}" class="link-arrow mt-4">Read article<span class="sr-only">: {{ $post->title }}</span> <x-lx-icon name="arrow-right" class="size-4" /></a>
                        </div>
                        @if ($cover = $post->getFirstMedia('cover'))
                            <a href="{{ route('blog.show', $post) }}" class="block aspect-[4/3] overflow-hidden rounded-xs bg-paper max-sm:order-first" tabindex="-1" aria-hidden="true">
                                <img src="{{ $cover->getAvailableUrl(['card']) }}" alt="" class="size-full object-cover" loading="lazy" width="800" height="600">
                            </a>
                        @endif
                    </article>
                </li>
            @endforeach
        </ul>
        <div class="mt-12">{{ $posts->links('components.pagination') }}</div>
    @endif
</section>
@endsection
