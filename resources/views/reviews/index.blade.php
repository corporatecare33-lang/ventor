@extends('layouts.app')

@section('content')
<x-page-header title="Customer reviews" :crumbs="['Reviews' => null]">
    <div class="mt-6 flex items-center gap-4">
        <span class="font-display text-5xl font-semibold">{{ number_format((float) $stats->average, 1) }}</span>
        <div><x-stars :rating="$stats->average" size="size-5" /><p class="mt-1 text-xs text-white/60">Based on {{ number_format($stats->total) }} verified reviews</p></div>
    </div>
</x-page-header>
<section class="container-lx py-14">
    @if ($reviews->isEmpty())
        <p class="text-center text-sm text-ink-600">No reviews yet.</p>
    @else
        <div class="columns-1 gap-5 sm:columns-2 lg:columns-3">
            @foreach ($reviews as $review)
                <figure class="mb-5 break-inside-avoid rounded-2xl border border-ink-900/8 bg-white p-6">
                    <x-stars :rating="$review->rating" />
                    @if ($review->title)<p class="mt-3 text-sm font-semibold">{{ $review->title }}</p>@endif
                    <blockquote class="mt-2 text-sm leading-6 text-ink-700">{{ $review->body }}</blockquote>
                    <figcaption class="mt-4 text-xs text-ink-600"><span class="font-semibold text-ink-950">{{ $review->name }}</span>
                        @if ($review->product) on <a href="{{ route('products.show', $review->product) }}" class="underline hover:text-gold-600">{{ $review->product->name }}</a>@endif
                    </figcaption>
                </figure>
            @endforeach
        </div>
        <div class="mt-10">{{ $reviews->links('components.pagination') }}</div>
    @endif
</section>
@endsection
