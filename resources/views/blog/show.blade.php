@extends('layouts.app')

@section('content')
<article>
    <header class="border-b border-line bg-paper">
        <div class="container-site pt-6 pb-12 lg:pb-16">
            <x-ui.breadcrumbs :items="['Journal' => route('blog.index'), $post->title => null]" />
            <div class="mt-10 max-w-3xl">
                <p class="eyebrow">{{ $post->category }}</p>
                <h1 class="mt-3 text-h1 text-ink-950">{{ $post->title }}</h1>
                @if ($post->excerpt)<p class="lead mt-4">{{ $post->excerpt }}</p>@endif
                <p class="mt-6 text-[13px] text-ink-500"><time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('j F Y') }}</time> · {{ $post->readingMinutes() }} min read</p>
            </div>
        </div>
    </header>
    @if ($cover = $post->getFirstMedia('cover'))
        <div class="container-site mt-10"><img src="{{ $cover->getUrl() }}" alt="{{ $cover->getCustomProperty('alt', '') }}" class="aspect-[2/1] w-full rounded-sm object-cover" fetchpriority="high" width="1600" height="800"></div>
    @endif
    <div class="container-site py-12 lg:py-16"><div class="prose-site">{!! clean_html($post->body) !!}</div></div>
</article>
@if ($related->isNotEmpty())
    <section class="border-t border-line bg-paper" aria-labelledby="more-title">
        <div class="container-site py-14">
            <h2 id="more-title" class="text-h2 text-ink-950">More from the journal</h2>
            <ul class="mt-8 grid gap-8 sm:grid-cols-3">
                @foreach ($related as $item)
                    <li class="border-t border-line-strong pt-5">
                        <p class="text-[13px] text-ink-500">{{ $item->published_at->format('j F Y') }}</p>
                        <h3 class="mt-2 font-display text-xl leading-snug text-ink-950"><a href="{{ route('blog.show', $item) }}" class="underline-offset-4 hover:underline">{{ $item->title }}</a></h3>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
@endif
@endsection
