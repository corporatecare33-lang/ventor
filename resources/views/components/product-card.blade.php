@props(['product', 'eager' => false])
@php
    $swatches = $product->colorSwatches();
    $purchasable = $product->isPurchasable();
@endphp
<article class="group relative flex flex-col overflow-hidden rounded-2xl border border-ink-900/8 bg-white transition duration-300 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-ink-900/5">
    <a href="{{ route('products.show', $product) }}" class="relative block aspect-square overflow-hidden bg-cream-100" aria-label="{{ $product->name }}">
        <x-product-image :product="$product" :eager="$eager" class="transition duration-500 group-hover:scale-[1.03]" />

        <div class="absolute top-3 left-3 flex flex-col items-start gap-1.5">
            @if ($product->hasDiscount())
                <span class="rounded-full bg-gold-500 px-2.5 py-1 text-[10px] font-bold tracking-wider text-ink-950">-{{ $product->discountPercent() }}%</span>
            @endif
            @unless ($purchasable)
                <span class="rounded-full bg-ink-900 px-2.5 py-1 text-[10px] font-semibold tracking-wider text-white uppercase">Sold out</span>
            @endunless
        </div>
        @if ($product->nfc_enabled)
            <span class="absolute top-3 right-3 inline-flex items-center gap-1 rounded-full bg-white/90 px-2.5 py-1 text-[10px] font-semibold text-ink-900 backdrop-blur">
                <x-lx-icon name="signal" class="size-3" /> NFC
            </span>
        @endif
    </a>


    <div class="flex flex-1 flex-col p-4">
        @if ($product->category)
            <p class="text-[10px] font-semibold tracking-[0.18em] text-tan-500 uppercase">{{ $product->category->name }}</p>
        @endif
        <h3 class="mt-1 line-clamp-2 font-sans text-sm font-medium leading-snug text-ink-950">
            <a href="{{ route('products.show', $product) }}" class="hover:text-leather-700">{{ $product->name }}</a>
        </h3>

        <div class="mt-2 flex items-center gap-1.5 text-[11px] text-ink-600">
            @if ($product->reviews_count)
                <x-stars :rating="$product->rating_avg" size="size-3" /> <span>({{ $product->reviews_count }})</span>
            @else
                <span class="text-ink-600/60">New arrival</span>
            @endif
        </div>

        <div class="mt-auto flex items-end justify-between gap-2 pt-3">
            <div>
                <x-price :current="$product->currentPrice()" :regular="$product->hasDiscount() ? $product->regular_price : null" />
                <div class="mt-2 flex h-4 items-center gap-1.5">
                @if ($swatches)
                        @foreach (array_slice($swatches, 0, 5) as $swatch)
                            <span class="size-3.5 rounded-full ring-1 ring-ink-900/15 ring-offset-1" style="background-color: {{ $swatch['hex'] ?? '#ccc' }}" title="{{ $swatch['name'] }}"></span>
                        @endforeach
                        @if (count($swatches) > 5)<span class="text-[10px] text-ink-600">+{{ count($swatches) - 5 }}</span>@endif
                @endif
                </div>
            </div>
            <button type="button" x-data @click="$dispatch('quick-view', { id: {{ $product->id }} })"
                    class="grid size-9 shrink-0 place-items-center rounded-full border border-ink-900/10 text-ink-900 transition hover:border-ink-900 hover:bg-ink-900 hover:text-white"
                    aria-label="Quick view {{ $product->name }}">
                <x-lx-icon name="eye" class="size-4" />
            </button>
        </div>
    </div>
</article>
