@extends('layouts.app')

@section('content')
@php
    $images = $product->getMedia('images');
    $specs = array_filter([
        'SKU' => $product->sku,
        'Leather' => $product->leather_type,
        'Material' => $product->material !== $product->leather_type ? $product->material : null,
        'Card capacity' => $product->card_capacity ? $product->card_capacity.' cards' : null,
        'Cash capacity' => $product->cash_capacity ? $product->cash_capacity.' notes' : null,
        'RFID protection' => $product->rfid_protection ? 'Yes' : 'No',
        'NFC (LeatherX Tap)' => $product->nfc_enabled ? 'Yes — digital profile included' : null,
        'Warranty' => $product->warranty_months ? $product->warranty_months.' months' : null,
        'Engraving' => $product->engraving_available ? 'Available (+'.money($product->engraving_price).')' : null,
        ...($product->specifications ?? []),
    ]);
@endphp

<div class="container-lx pt-6">
    <x-breadcrumbs :items="array_filter([
        'Shop' => route('shop'),
        $product->category?->name ?? '' => $product->category ? route('categories.show', $product->category) : null,
        $product->name => null,
    ], fn ($v, $k) => $k !== '', ARRAY_FILTER_USE_BOTH)" />
</div>

<section class="container-lx grid gap-10 py-8 lg:grid-cols-2 lg:gap-16">
    {{-- Gallery --}}
    <div x-data="{ active: 0 }" class="lg:sticky lg:top-24 lg:self-start">
        <div class="relative aspect-square overflow-hidden rounded-3xl bg-cream-100">
            @forelse ($images as $i => $image)
                <img x-show="active === {{ $i }}" @if ($i > 0) x-cloak @endif src="{{ $image->getAvailableUrl(['large']) }}" alt="{{ $image->getCustomProperty('alt', $product->name) }}"
                     class="size-full object-cover" width="1200" height="1200" @if ($i === 0) fetchpriority="high" @else loading="lazy" @endif decoding="async">
            @empty
                <x-product-image :product="$product" eager />
            @endforelse
            @if ($product->hasDiscount())
                <span class="absolute top-4 left-4 rounded-full bg-gold-500 px-3 py-1.5 text-xs font-bold text-ink-950">-{{ $product->discountPercent() }}%</span>
            @endif
        </div>
        @if ($images->count() > 1)
            <div class="scrollbar-none mt-3 flex gap-3 overflow-x-auto">
                @foreach ($images as $i => $image)
                    <button type="button" @click="active = {{ $i }}" class="size-20 shrink-0 overflow-hidden rounded-xl ring-offset-2 transition" :class="active === {{ $i }} ? 'ring-2 ring-ink-900' : 'opacity-70 hover:opacity-100'" aria-label="Image {{ $i + 1 }}">
                        <img src="{{ $image->getAvailableUrl(['thumb']) }}" alt="" class="size-full object-cover" loading="lazy" width="80" height="80">
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Summary --}}
    <div>
        @if ($product->category)<a href="{{ route('categories.show', $product->category) }}" class="eyebrow">{{ $product->category->name }}</a>@endif
        <h1 class="mt-3 font-display text-4xl leading-tight font-semibold text-ink-950 sm:text-5xl">{{ $product->name }}</h1>
        <a href="#reviews" class="mt-3 inline-flex items-center gap-2 text-sm text-ink-600">
            <x-stars :rating="$product->rating_avg" size="size-4" />
            {{ $product->reviews_count ? number_format((float) $product->rating_avg, 1).' · '.$product->reviews_count.' reviews' : 'No reviews yet' }}
        </a>
        @if ($product->short_description)
            <p class="mt-5 text-[15px] leading-7 text-ink-600">{{ $product->short_description }}</p>
        @endif

        <div class="mt-5 flex flex-wrap gap-2">
            @if ($product->rfid_protection)<span class="inline-flex items-center gap-1.5 rounded-full bg-cream-100 px-3 py-1.5 text-xs font-medium text-leather-800"><x-lx-icon name="shield" class="size-3.5" /> RFID protection</span>@endif
            @if ($product->nfc_enabled)<span class="inline-flex items-center gap-1.5 rounded-full bg-ink-900 px-3 py-1.5 text-xs font-medium text-gold-300"><x-lx-icon name="signal" class="size-3.5" /> NFC enabled</span>@endif
            @if ($product->warranty_months)<span class="inline-flex items-center gap-1.5 rounded-full bg-cream-100 px-3 py-1.5 text-xs font-medium text-leather-800"><x-lx-icon name="badge" class="size-3.5" /> {{ $product->warranty_months }}-month warranty</span>@endif
            @if ($product->engraving_available)<span class="inline-flex items-center gap-1.5 rounded-full bg-cream-100 px-3 py-1.5 text-xs font-medium text-leather-800"><x-lx-icon name="sparkles" class="size-3.5" /> Engraving</span>@endif
        </div>

        <div class="mt-8 border-t border-ink-900/10 pt-8">
            @if ($product->status === \App\Enums\ProductStatus::Draft)
                <p class="mb-4 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-900">Preview: this product is a draft and not visible to customers.</p>
            @endif
            <livewire:add-to-cart :product="$product" />
        </div>

        <div class="mt-8 grid gap-3 sm:grid-cols-2">
            <div class="flex gap-3 rounded-2xl border border-ink-900/10 p-4">
                <x-lx-icon name="truck" class="size-6 shrink-0 text-gold-600" />
                <div class="text-xs leading-5">
                    <p class="text-sm font-semibold text-ink-950">Delivery estimate</p>
                    @foreach ($zones as $zone)
                        <p class="text-ink-600">{{ $zone->name }}: {{ $zone->estimate() }} · {{ money($zone->charge) }}</p>
                    @endforeach
                </div>
            </div>
            <div class="flex gap-3 rounded-2xl border border-ink-900/10 p-4">
                <x-lx-icon name="badge" class="size-6 shrink-0 text-gold-600" />
                <div class="text-xs leading-5">
                    <p class="text-sm font-semibold text-ink-950">Warranty</p>
                    <p class="text-ink-600">{{ $product->warranty_months ? $product->warranty_months.' months on stitching, edges and hardware.' : 'Covered by our 7-day exchange policy.' }}</p>
                    <a href="{{ route('warranty') }}" class="text-leather-700 underline">Register warranty</a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Tabs --}}
<section class="container-lx py-12" x-data="{ tab: 'description' }">
    <div class="scrollbar-none flex gap-8 overflow-x-auto border-b border-ink-900/10" role="tablist">
        @foreach (['description' => 'Description', 'specifications' => 'Specifications', 'delivery' => 'Delivery & returns'] as $key => $label)
            <button type="button" role="tab" @click="tab = '{{ $key }}'" :aria-selected="tab === '{{ $key }}'"
                    class="-mb-px border-b-2 py-4 text-sm font-medium whitespace-nowrap transition" :class="tab === '{{ $key }}' ? 'border-ink-900 text-ink-950' : 'border-transparent text-ink-600 hover:text-ink-900'">{{ $label }}</button>
        @endforeach
    </div>
    <div class="max-w-3xl py-8">
        <div x-show="tab === 'description'" class="prose-lx">{!! clean_html($product->description ?: '<p>'.e($product->short_description).'</p>') !!}</div>
        <div x-cloak x-show="tab === 'specifications'">
            <dl class="divide-y divide-ink-900/8 rounded-2xl border border-ink-900/10">
                @foreach ($specs as $label => $value)
                    <div class="grid grid-cols-2 gap-4 px-5 py-3.5 text-sm"><dt class="text-ink-600">{{ $label }}</dt><dd class="text-ink-950">{{ $value }}</dd></div>
                @endforeach
            </dl>
        </div>
        <div x-cloak x-show="tab === 'delivery'" class="prose-lx">
            <ul>
                @foreach ($zones as $zone)
                    <li><strong>{{ $zone->name }}</strong>: {{ $zone->estimate() }}, {{ money($zone->charge) }}@if ($zone->free_shipping_threshold) (free over {{ money($zone->free_shipping_threshold) }})@endif</li>
                @endforeach
            </ul>
            <p>Cash on delivery available nationwide. Unused, non-engraved items can be exchanged within 7 days. <a href="{{ route('pages.return-policy') }}">Read the return policy</a>.</p>
        </div>
    </div>
</section>

{{-- Reviews --}}
<section id="reviews" class="scroll-mt-24 bg-cream-50 py-16">
    <div class="container-lx grid gap-12 lg:grid-cols-[320px_1fr]">
        <div>
            <h2 class="font-display text-3xl font-semibold">Customer reviews</h2>
            <div class="mt-4 flex items-center gap-3">
                <span class="font-display text-5xl font-semibold">{{ number_format((float) $product->rating_avg, 1) }}</span>
                <div><x-stars :rating="$product->rating_avg" size="size-4" /><p class="mt-1 text-xs text-ink-600">{{ $product->reviews_count }} reviews</p></div>
            </div>
            <div class="mt-5 space-y-1.5">
                @for ($star = 5; $star >= 1; $star--)
                    @php $count = (int) ($ratingBreakdown[$star] ?? 0); $pct = $product->reviews_count ? round($count / $product->reviews_count * 100) : 0; @endphp
                    <div class="flex items-center gap-3 text-xs text-ink-600">
                        <span class="w-3">{{ $star }}</span>
                        <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-ink-900/10"><div class="h-full rounded-full bg-gold-500" style="width: {{ $pct }}%"></div></div>
                        <span class="w-6 text-right">{{ $count }}</span>
                    </div>
                @endfor
            </div>

            @auth
                <form method="POST" action="{{ route('reviews.store', $product) }}" class="mt-8 space-y-3 rounded-2xl bg-white p-5" x-data="{ rating: {{ old('rating', 5) }} }">
                    @csrf
                    <p class="text-sm font-semibold">Write a review</p>
                    <div class="flex gap-1" role="radiogroup" aria-label="Rating">
                        @for ($i = 1; $i <= 5; $i++)
                            <button type="button" @click="rating = {{ $i }}" class="text-gold-500" aria-label="{{ $i }} star"><x-lx-icon name="star-solid" class="size-6" ::class="rating >= {{ $i }} ? 'opacity-100' : 'opacity-25'" /></button>
                        @endfor
                        <input type="hidden" name="rating" :value="rating">
                    </div>
                    <input type="text" name="title" value="{{ old('title') }}" maxlength="150" class="field" placeholder="Title (optional)">
                    <textarea name="body" rows="3" required minlength="10" maxlength="2000" class="field" placeholder="How do you like it?">{{ old('body') }}</textarea>
                    @error('body', 'review')<p class="error">{{ $message }}</p>@enderror
                    @error('body')<p class="error">{{ $message }}</p>@enderror
                    <button class="btn-dark w-full">Submit review</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn-outline mt-8">Sign in to write a review</a>
            @endauth
        </div>

        <div class="space-y-4">
            @forelse ($reviews as $review)
                <article class="rounded-2xl bg-white p-6">
                    <div class="flex items-center justify-between gap-3">
                        <x-stars :rating="$review->rating" />
                        <time class="text-xs text-ink-600" datetime="{{ $review->created_at->toDateString() }}">{{ $review->created_at->format('d M Y') }}</time>
                    </div>
                    @if ($review->title)<h3 class="mt-3 font-sans text-sm font-semibold">{{ $review->title }}</h3>@endif
                    <p class="mt-2 text-sm leading-6 text-ink-700">{{ $review->body }}</p>
                    <p class="mt-3 text-xs font-medium text-ink-950">{{ $review->name }} @if ($review->is_verified_purchase)<span class="text-emerald-700">· Verified purchase</span>@endif</p>
                    @if ($review->admin_reply)
                        <div class="mt-4 rounded-xl bg-cream-50 p-4 text-xs leading-5 text-ink-700"><span class="font-semibold">LeatherX:</span> {{ $review->admin_reply }}</div>
                    @endif
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-ink-900/15 p-10 text-center text-sm text-ink-600">No reviews yet — be the first to share your experience.</div>
            @endforelse
        </div>
    </div>
</section>

@if ($related->isNotEmpty())
    <section class="container-lx py-16">
        <h2 class="font-display text-3xl font-semibold">You may also like</h2>
        <div class="mt-8 grid grid-cols-2 gap-3 sm:gap-5 lg:grid-cols-4">
            @foreach ($related as $item)
                <x-product-card :product="$item" />
            @endforeach
        </div>
    </section>
@endif
@endsection
