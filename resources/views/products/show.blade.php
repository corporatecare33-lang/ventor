@extends('layouts.app')

@section('content')
@php
    $images = $product->getMedia('images');
    $specs = array_filter([
        'Material' => $product->material ?: $product->leather_type,
        'Card capacity' => $product->card_capacity ? 'Up to '.$product->card_capacity.' cards' : null,
        'Cash capacity' => $product->cash_capacity ? 'Up to '.$product->cash_capacity.' notes' : null,
        'RFID-blocking lining' => $product->rfid_protection ? 'Yes' : null,
        'NFC contact profile' => $product->nfc_enabled ? 'Yes — LeatherX Tap' : null,
        'Laser engraving' => $product->engraving_available ? 'Available, '.money($product->engraving_price).' per item' : null,
        'Warranty' => $product->warranty_months ? $product->warranty_months.' months' : null,
        ...($product->specifications ?? []),
        'SKU' => $product->sku,
    ]);
    $cheapestZone = $zones->sortBy('charge')->first();
@endphp

<div class="container-site pt-6">
    <x-ui.breadcrumbs :items="array_filter([
        'Shop' => route('shop'),
        ($product->category?->name ?? '') => $product->category ? route('categories.show', $product->category) : null,
        $product->name => null,
    ], fn ($v, $k) => $k !== '', ARRAY_FILTER_USE_BOTH)" />
</div>

<section class="container-site grid gap-10 pt-6 pb-16 lg:grid-cols-12 lg:gap-12 lg:pt-8" aria-label="Product">
    {{-- Gallery --}}
    {{-- Images can be tagged with a colour (media custom property "color"); choosing that colour shows its photo. --}}
    <div class="lg:col-span-7" x-data="{ active: 0, colors: @js($images->map(fn ($m) => $m->getCustomProperty('color'))->values()) }"
         @variant-selected.window="if ($event.detail.productId === {{ $product->id }}) { const i = colors.indexOf($event.detail.color); if (i > -1) active = i }">
        <div class="relative aspect-[4/5] overflow-hidden rounded-card bg-paper">
            @forelse ($images as $i => $image)
                <div x-show="active === {{ $i }}" @if ($i > 0) x-cloak @endif class="size-full">
                    <x-product.media :product="$product" :index="$i" :eager="$i === 0" sizes="(min-width: 1024px) 55vw, 100vw" />
                </div>
            @empty
                <x-product.media :product="$product" eager />
            @endforelse
            @if ($product->hasDiscount())
                <span class="absolute top-4 left-4 rounded-full bg-sale px-2.5 py-1 text-[11px] font-semibold text-white">Save {{ $product->discountPercent() }}%</span>
            @endif
        </div>
        @if ($images->count() > 1)
            <div class="mt-3 flex gap-3" role="group" aria-label="Product images">
                @foreach ($images as $i => $image)
                    <button type="button" @click="active = {{ $i }}" :aria-pressed="(active === {{ $i }}).toString()"
                            class="size-20 overflow-hidden rounded-sm ring-offset-2 transition sm:size-24" :class="active === {{ $i }} ? 'ring-2 ring-ink-950' : 'opacity-75 hover:opacity-100'">
                        <img src="{{ $image->getAvailableUrl(['thumb']) }}" alt="Show image {{ $i + 1 }} of {{ $images->count() }}" class="size-full object-cover" loading="lazy" width="96" height="120">
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Purchase panel --}}
    <div class="lg:col-span-5">
        <div class="lg:sticky lg:top-28">
            @if ($product->category)
                <a href="{{ route('categories.show', $product->category) }}" class="text-[13px] text-ink-500 underline-offset-4 hover:text-ink-950 hover:underline">{{ $product->category->name }}</a>
            @endif
            <h1 class="mt-2 text-[clamp(2rem,1.7rem+1.2vw,2.75rem)] leading-[1.08] text-ink-950">{{ $product->name }}</h1>
            @if ($product->reviews_count)
                <a href="#reviews" class="mt-3 inline-flex items-center gap-2 text-sm text-ink-600 hover:text-ink-950">
                    <x-ui.rating :rating="$product->rating_avg" size="size-4" /> {{ number_format((float) $product->rating_avg, 1) }} · {{ $product->reviews_count }} {{ Str::plural('review', $product->reviews_count) }}
                </a>
            @endif
            @if ($product->short_description)
                <p class="mt-4 text-[15px] leading-relaxed text-ink-600">{{ $product->short_description }}</p>
            @endif

            @if ($product->status === \App\Enums\ProductStatus::Draft)
                <x-ui.alert type="warning" class="mt-6">Preview only — this product is a draft and hidden from customers.</x-ui.alert>
            @endif

            <div id="purchase" class="mt-6 border-t border-line pt-6">
                <livewire:add-to-cart :product="$product" />
            </div>

            <ul class="mt-8 space-y-3 border-t border-line pt-6 text-sm text-ink-600">
                @if ($cheapestZone)
                    <li class="flex gap-3"><x-lx-icon name="truck" class="mt-0.5 size-5 shrink-0 text-leather-600" />
                        <span>Delivery from {{ money($cheapestZone->charge) }} · {{ $zones->sortBy('min_days')->first()->name }} {{ $zones->sortBy('min_days')->first()->estimate() }}. <a href="#delivery" class="text-leather-700 underline underline-offset-4">All areas</a></span></li>
                @endif
                <li class="flex gap-3"><x-lx-icon name="cash" class="mt-0.5 size-5 shrink-0 text-leather-600" /><span>Cash on delivery — we call to confirm before dispatch.</span></li>
                @if ($product->warranty_months)
                    <li class="flex gap-3"><x-lx-icon name="badge" class="mt-0.5 size-5 shrink-0 text-leather-600" />
                        <span>{{ $product->warranty_months }}-month warranty. <a href="{{ route('warranty') }}" class="text-leather-700 underline underline-offset-4">How to register</a></span></li>
                @endif
            </ul>
        </div>
    </div>
</section>

{{-- Details --}}
<section class="border-t border-line" aria-labelledby="details-title">
    <div class="container-site grid gap-12 py-14 lg:grid-cols-12 lg:gap-12 lg:py-20">
        <div class="lg:col-span-6">
            <h2 id="details-title" class="text-h2 text-ink-950">About this piece</h2>
            <div class="prose-site mt-6">{!! clean_html($product->description ?: '<p>'.e($product->short_description).'</p>') !!}</div>
        </div>
        <div class="lg:col-span-5 lg:col-start-8">
            <h2 class="font-display text-2xl text-ink-950">Specifications</h2>
            <dl class="mt-5 border-t border-line text-[15px]">
                @foreach ($specs as $label => $value)
                    <div class="grid grid-cols-[minmax(0,10rem)_1fr] gap-4 border-b border-line py-3">
                        <dt class="text-ink-500">{{ $label }}</dt>
                        <dd class="text-ink-950">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>

            <h2 id="delivery" class="mt-12 scroll-mt-28 font-display text-2xl text-ink-950">Delivery & returns</h2>
            <dl class="mt-5 border-t border-line text-[15px]">
                @foreach ($zones as $zone)
                    <div class="flex justify-between gap-4 border-b border-line py-3">
                        <dt class="text-ink-600">{{ $zone->name }} <span class="text-ink-500">· {{ $zone->estimate() }}</span></dt>
                        <dd class="text-ink-950">{{ money($zone->charge) }}@if ($zone->free_shipping_threshold)<span class="block text-right text-[13px] text-ink-500">Free over {{ money($zone->free_shipping_threshold) }}</span>@endif</dd>
                    </div>
                @endforeach
            </dl>
            <p class="mt-4 text-sm text-ink-600">
                @if ($product->engraving_available)Engraved items need two to three extra working days. @endif
                Read the <a href="{{ route('pages.return-policy') }}" class="text-leather-700 underline underline-offset-4">return & exchange policy</a>.
            </p>
        </div>
    </div>
</section>

{{-- Reviews --}}
<section id="reviews" class="scroll-mt-24 bg-paper" aria-labelledby="reviews-title">
    <div class="container-site grid gap-12 py-14 lg:grid-cols-12 lg:py-20">
        <div class="lg:col-span-4">
            <h2 id="reviews-title" class="text-h2 text-ink-950">Reviews</h2>
            @if ($product->reviews_count)
                <div class="mt-5 flex items-center gap-4">
                    <span class="font-display text-5xl font-semibold text-ink-950">{{ number_format((float) $product->rating_avg, 1) }}</span>
                    <div><x-ui.rating :rating="$product->rating_avg" size="size-4" /><p class="mt-1 text-[13px] text-ink-500">{{ $product->reviews_count }} {{ Str::plural('review', $product->reviews_count) }}</p></div>
                </div>
                <dl class="mt-5 space-y-1.5">
                    @for ($star = 5; $star >= 1; $star--)
                        @php $count = (int) ($ratingBreakdown[$star] ?? 0); $pct = $product->reviews_count ? round($count / $product->reviews_count * 100) : 0; @endphp
                        <div class="flex items-center gap-3 text-[13px] text-ink-600">
                            <dt class="w-12">{{ $star }} star</dt>
                            <dd class="h-1.5 flex-1 overflow-hidden rounded-full bg-paper-300"><span class="block h-full bg-brass-500" style="width: {{ $pct }}%"></span></dd>
                            <dd class="w-6 text-right">{{ $count }}</dd>
                        </div>
                    @endfor
                </dl>
            @else
                <p class="mt-4 text-[15px] text-ink-600">No reviews yet. Customers who buy this piece can review it from this page after signing in.</p>
            @endif

            @auth
                <form method="POST" action="{{ route('reviews.store', $product) }}" class="mt-8 space-y-4 border-t border-line-strong pt-6" x-data="{ rating: {{ (int) old('rating', 5) }}, busy: false }" @submit="busy = true">
                    @csrf
                    <h3 class="font-sans text-base font-medium text-ink-950">Write a review</h3>
                    <fieldset>
                        <legend class="label">Your rating</legend>
                        <div class="flex gap-1">
                            @for ($i = 1; $i <= 5; $i++)
                                <label class="cursor-pointer text-brass-500">
                                    <input type="radio" name="rating" value="{{ $i }}" x-model.number="rating" class="peer sr-only">
                                    <x-lx-icon name="star-solid" class="size-7 peer-focus-visible:outline-2 peer-focus-visible:outline-brass-500" ::class="rating >= {{ $i }} ? '' : 'opacity-25'" />
                                    <span class="sr-only">{{ $i }} {{ Str::plural('star', $i) }}</span>
                                </label>
                            @endfor
                        </div>
                    </fieldset>
                    <x-form.input name="title" label="Title (optional)" maxlength="150" />
                    <x-form.textarea name="body" label="Your review" required minlength="10" maxlength="2000" rows="4" />
                    @error('body', 'review')<p class="error">{{ $message }}</p>@enderror
                    <x-form.submit label="Submit review" loading="Submitting…" class="w-full" />
                    <p class="text-[13px] text-ink-500">Reviews are published after moderation.</p>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn-secondary mt-8">Sign in to write a review</a>
            @endauth
        </div>

        <div class="lg:col-span-7 lg:col-start-6">
            @if ($reviews->isNotEmpty())
                <ul class="border-t border-line-strong">
                    @foreach ($reviews as $review)
                        <li class="border-b border-line-strong py-6">
                            <article>
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <x-ui.rating :rating="$review->rating" />
                                    <time class="text-[13px] text-ink-500" datetime="{{ $review->created_at->toDateString() }}">{{ $review->created_at->format('j M Y') }}</time>
                                </div>
                                @if ($review->title)<h3 class="mt-3 font-sans text-base font-medium text-ink-950">{{ $review->title }}</h3>@endif
                                <p class="mt-2 text-[15px] leading-relaxed text-ink-700">{{ $review->body }}</p>
                                <p class="mt-3 text-[13px] text-ink-600"><span class="font-medium text-ink-950">{{ $review->name }}</span>@if ($review->is_verified_purchase) · Verified purchase @endif</p>
                                @if ($review->admin_reply)
                                    <p class="mt-4 border-l-2 border-brass-500 pl-4 text-sm text-ink-600"><span class="font-medium text-ink-950">Reply from {{ setting('site_name', 'LeatherX') }}:</span> {{ $review->admin_reply }}</p>
                                @endif
                            </article>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</section>

@if ($related->isNotEmpty())
    <section class="section-y" aria-labelledby="related-title">
        <div class="container-site">
            <div class="flex flex-wrap items-end justify-between gap-6">
                <x-ui.section-heading id="related-title" title="You may also like" />
                @if ($product->category)
                    <a href="{{ route('categories.show', $product->category) }}" class="link-arrow">More {{ $product->category->name }} <x-lx-icon name="arrow-right" class="size-4" /></a>
                @endif
            </div>
            <div class="mt-10 grid grid-cols-2 gap-x-4 gap-y-10 sm:gap-x-6 lg:grid-cols-4">
                @foreach ($related as $item)
                    <x-product.card :product="$item" />
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- Mobile purchase bar: appears once the buy buttons scroll out of view (the assistant launcher steps aside meanwhile) --}}
<div x-data="{ show: false }" x-init="new IntersectionObserver(([entry]) => show = ! entry.isIntersecting && entry.boundingClientRect.top < 0).observe(document.getElementById('purchase'))"
     x-effect="$dispatch('purchase-bar', { shown: show })"
     x-cloak x-show="show" x-transition.opacity class="fixed inset-x-0 bottom-0 z-30 border-t border-line bg-white/95 backdrop-blur lg:hidden">
    <div class="container-site flex items-center justify-between gap-4 py-3">
        <div class="min-w-0">
            <p class="truncate text-sm font-medium text-ink-950">{{ $product->name }}</p>
            <p class="text-sm text-ink-600">{{ money($product->currentPrice()) }}</p>
        </div>
        <a href="#purchase" class="btn-primary btn-sm shrink-0">Choose & add to cart</a>
    </div>
</div>
@endsection
