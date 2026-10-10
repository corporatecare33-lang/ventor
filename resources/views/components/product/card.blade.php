@props(['product', 'eager' => false, 'heading' => 'h3', 'sizes' => '(min-width: 1280px) 22vw, (min-width: 768px) 30vw, 46vw'])
@php
    $purchasable = $product->isPurchasable();
    $url = route('products.show', $product);
@endphp
<article {{ $attributes->merge(['class' => 'group relative flex flex-col']) }}>
    <div class="relative aspect-[4/5] overflow-hidden rounded-sm bg-paper">
        <a href="{{ $url }}" tabindex="-1" aria-hidden="true" class="block size-full">
            <x-product.media :product="$product" :eager="$eager" :sizes="$sizes" class="transition-transform duration-700 group-hover:scale-[1.025]" />
        </a>
        @if ($product->hasDiscount() || ! $purchasable)
            <p class="absolute top-3 left-3 flex gap-1.5">
                @unless ($purchasable)
                    <span class="rounded-xs bg-white px-2 py-1 text-[11px] font-semibold tracking-wide text-ink-950 uppercase">Sold out</span>
                @elseif ($product->hasDiscount())
                    <span class="rounded-xs bg-ink-950 px-2 py-1 text-[11px] font-semibold tracking-wide text-white uppercase">Save {{ $product->discountPercent() }}%</span>
                @endunless
            </p>
        @endif
        <button type="button" x-data @click="$dispatch('quick-view', { id: {{ $product->id }} })"
                class="btn-inverse btn-sm absolute inset-x-3 bottom-3 hidden opacity-0 shadow-pop group-hover:opacity-100 focus-visible:opacity-100 lg:inline-flex">
            Quick view<span class="sr-only">: {{ $product->name }}</span>
        </button>
    </div>

    <div class="mt-4 flex flex-1 flex-col">
        @if ($product->category)
            <p class="text-[13px] text-ink-500">{{ $product->category->name }}</p>
        @endif
        <{{ $heading }} class="mt-1 font-sans text-[15px] leading-snug font-medium text-ink-950">
            <a href="{{ $url }}" class="decoration-line-strong underline-offset-4 hover:underline">{{ $product->name }}</a>
        </{{ $heading }}>
        @if ($line = $product->featureLine())
            <p class="mt-1 text-[13px] text-ink-500">{{ $line }}</p>
        @endif
        <div class="mt-3 flex items-center justify-between gap-3">
            <x-product.price :current="$product->currentPrice()" :regular="$product->hasDiscount() ? $product->regular_price : null" />
            <x-product.swatches :swatches="$product->colorSwatches()" />
        </div>
        @if ($product->reviews_count)
            <x-ui.rating :rating="$product->rating_avg" :count="$product->reviews_count" class="mt-2" size="size-3" />
        @endif
    </div>
</article>
