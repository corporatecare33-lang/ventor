{{--
    Product card: square photo with sale / rating chips, name + price, colours, and two
    feature callouts built from real product attributes. Layout adapts to the card's own
    width (container queries), so it works in 2-up mobile grids and 4-up desktop rows alike.
--}}
@props(['product', 'eager' => false, 'heading' => 'h3', 'sizes' => '(min-width: 1280px) 22vw, (min-width: 768px) 30vw, 46vw'])
@php
    $purchasable = $product->isPurchasable();
    $url = route('products.show', $product);
    $features = $product->cardFeatures();
@endphp
<article {{ $attributes->merge(['class' => '@container group relative flex flex-col overflow-hidden rounded-card bg-paper']) }}>
    <div class="relative aspect-square overflow-hidden bg-paper-200">
        <a href="{{ $url }}" tabindex="-1" aria-hidden="true" class="block size-full">
            <x-product.media :product="$product" :eager="$eager" :sizes="$sizes" class="transition-transform duration-700 ease-out-quart group-hover:scale-[1.04]" />
        </a>

        @unless ($purchasable)
            <span class="absolute top-3 left-3 rounded-full bg-white px-2.5 py-1 text-[11px] font-semibold text-ink-950">Sold out</span>
        @elseif ($product->hasDiscount())
            <span class="absolute top-3 left-3 rounded-full bg-sale px-2.5 py-1 text-[11px] font-semibold text-white">Save {{ $product->discountPercent() }}%</span>
        @endunless

        @if ($product->reviews_count)
            <span class="absolute top-3 right-3 inline-flex items-center gap-1 rounded-full bg-white/95 px-2 py-1 text-[11px] font-semibold text-ink-950">
                <x-lx-icon name="star-solid" class="size-3 text-brass-600" />{{ number_format((float) $product->rating_avg, 1) }}
                <span class="sr-only">out of 5</span>
            </span>
        @endif

        <button type="button" x-data @click="$dispatch('quick-view', { id: {{ $product->id }} })"
                class="absolute bottom-3 left-1/2 z-10 hidden -translate-x-1/2 translate-y-1 rounded-full bg-white/95 px-4 py-2 text-[12px] font-semibold whitespace-nowrap text-ink-950 opacity-0 shadow-pop transition duration-200 group-hover:translate-y-0 group-hover:opacity-100 hover:bg-white focus-visible:translate-y-0 focus-visible:opacity-100 lg:inline-flex">
            Quick view<span class="sr-only">: {{ $product->name }}</span>
        </button>
    </div>

    <div class="flex flex-1 flex-col px-3.5 pt-3.5 pb-4 @[15rem]:px-4">
        @if ($product->category)
            <p class="text-[10.5px] font-semibold tracking-[0.14em] text-ink-500 uppercase">{{ $product->category->name }}</p>
        @endif
        <div class="mt-1.5 flex flex-col gap-1.5 @[15rem]:flex-row @[15rem]:items-start @[15rem]:justify-between @[15rem]:gap-3">
            <{{ $heading }} class="min-w-0 font-sans text-[14px] leading-snug font-semibold tracking-[-0.01em] text-ink-950 @[15rem]:text-[15px]">
                <a href="{{ $url }}" class="after:absolute after:inset-0 after:content-[''] hover:text-leather-700">{{ $product->name }}</a>
            </{{ $heading }}>
            <x-product.price :current="$product->currentPrice()" :regular="$product->hasDiscount() ? $product->regular_price : null"
                             size="text-[14px] @[15rem]:text-[15px]" class="shrink-0" />
        </div>
        @if ($product->reviews_count)
            <x-ui.rating :rating="$product->rating_avg" :count="$product->reviews_count" class="mt-2" size="size-3" />
        @endif
        <x-product.swatches :swatches="$product->colorSwatches()" class="mt-3" />
    </div>

    @if ($features)
        <ul class="mt-auto grid grid-cols-1 gap-2.5 border-t border-line px-3.5 py-3 @[15rem]:grid-cols-2 @[15rem]:px-4">
            @foreach ($features as $feature)
                <li class="flex min-w-0 items-start gap-2 {{ $loop->index > 0 ? '@max-[15rem]:hidden' : '' }}">
                    <x-lx-icon :name="$feature['icon']" class="mt-px size-4 shrink-0 text-ink-600" />
                    <span class="min-w-0 text-[11px] leading-tight">
                        <span class="line-clamp-2 block font-semibold text-ink-950">{{ $feature['title'] }}</span>
                        <span class="block truncate text-ink-500">{{ $feature['note'] }}</span>
                    </span>
                </li>
            @endforeach
        </ul>
    @endif
</article>
