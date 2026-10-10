@extends('layouts.app')

@section('content')
<x-ui.page-header title="Customer reviews" intro="Reviews are published after moderation. Reviews from customers who bought the product are marked “Verified purchase”." :crumbs="['Reviews' => null]">
    @if ($stats->total)
        <div class="mt-6 flex items-center gap-4">
            <span class="font-display text-5xl font-semibold text-ink-950">{{ number_format((float) $stats->average, 1) }}</span>
            <div><x-ui.rating :rating="$stats->average" size="size-4" /><p class="mt-1 text-[13px] text-ink-500">{{ number_format($stats->total) }} {{ Str::plural('review', $stats->total) }}</p></div>
        </div>
    @endif
</x-ui.page-header>
<section class="container-site py-12 lg:py-16">
    @if ($reviews->isEmpty())
        <x-ui.empty-state icon="star" title="No reviews yet" text="Bought something from us? Sign in and review it from the product page.">
            <a href="{{ route('shop') }}" class="btn-secondary">Browse products</a>
        </x-ui.empty-state>
    @else
        <h2 class="sr-only">All reviews</h2>
        <ul class="grid gap-x-10 border-t border-line-strong md:grid-cols-2">
            @foreach ($reviews as $review)
                <li class="border-b border-line-strong py-6">
                    <article>
                        <div class="flex items-center justify-between gap-3"><x-ui.rating :rating="$review->rating" /><time class="text-[13px] text-ink-500" datetime="{{ $review->created_at->toDateString() }}">{{ $review->created_at->format('j M Y') }}</time></div>
                        @if ($review->title)<h3 class="mt-3 font-sans text-base font-medium text-ink-950">{{ $review->title }}</h3>@endif
                        <p class="mt-2 text-[15px] leading-relaxed text-ink-700">{{ $review->body }}</p>
                        <p class="mt-3 text-[13px] text-ink-600"><span class="font-medium text-ink-950">{{ $review->name }}</span>@if ($review->is_verified_purchase) · Verified purchase @endif
                            @if ($review->product) · <a href="{{ route('products.show', $review->product) }}" class="text-leather-700 underline underline-offset-4">{{ $review->product->name }}</a>@endif</p>
                    </article>
                </li>
            @endforeach
        </ul>
        <div class="mt-12">{{ $reviews->links('components.pagination') }}</div>
    @endif
</section>
@endsection
