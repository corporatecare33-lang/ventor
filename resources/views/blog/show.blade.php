@extends('layouts.app')

@section('content')
<article>
    <header class="container-lx max-w-3xl pt-10 text-center">
        <x-breadcrumbs :items="['Journal' => route('blog.index'), $post->title => null]" class="flex justify-center" />
        <p class="eyebrow mt-8">{{ $post->category }}</p>
        <h1 class="mt-3 font-display text-4xl leading-tight font-semibold sm:text-5xl">{{ $post->title }}</h1>
        <p class="mt-4 text-xs text-ink-600">{{ $post->published_at->format('d M Y') }} · {{ $post->readingMinutes() }} min read</p>
    </header>
    @if ($cover = $post->getFirstMedia('cover'))
        <div class="container-lx mt-10 max-w-5xl"><img src="{{ $cover->getUrl() }}" alt="{{ $post->title }}" class="aspect-[16/8] w-full rounded-3xl object-cover" fetchpriority="high" width="1600" height="800"></div>
    @endif
    <div class="container-lx max-w-3xl py-12"><div class="prose-lx">{!! clean_html($post->body) !!}</div></div>
</article>
@if ($related->isNotEmpty())
    <section class="border-t border-ink-900/10 bg-cream-50 py-14">
        <div class="container-lx">
            <h2 class="font-display text-3xl font-semibold">More from the journal</h2>
            <div class="mt-8 grid gap-6 sm:grid-cols-3">
                @foreach ($related as $item)
                    <a href="{{ route('blog.show', $item) }}" class="card block p-6 hover:border-ink-900/30">
                        <p class="text-xs text-ink-600">{{ $item->published_at->format('d M Y') }}</p>
                        <h3 class="mt-2 font-display text-xl font-semibold">{{ $item->title }}</h3>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
@endsection
