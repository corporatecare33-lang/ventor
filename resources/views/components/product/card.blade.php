{{--
    Product card: square photo with sale / rating chips, name + rating + price, colour swatches, and a
    sideways-scrolling strip of feature callouts built from real product attributes. Layout adapts to the
    card's own width (container queries), so it works in 2-up mobile grids and 4-up desktop rows alike.
    showCategory=false drops the category label where the grid already sits under its name.

    Proportions follow ventro.com.bd's card: padding (--card-pad, ~9% of the card's width) and
    the name scale with the card, and from 19rem wide the chips, "Sold out" pill and feature
    strip switch to Ventro's desktop sizes. The feature strip is a white tray under the card.

    Hover gallery (as on ventro.com.bd): with a mouse, a product with more than one photo slides
    to its other photos, picked by where the pointer sits across the photo (two photos: any
    hover shows the second; three: left / right half; four: thirds), and slides back on leave.
    The extra photos are only inserted on hover-capable devices once the card nears the viewport.
    On hover the rating chip makes way for the round quick-view (eye) button, as on Ventro.
--}}
@props(['product', 'eager' => false, 'heading' => 'h3', 'showCategory' => true, 'sizes' => '(min-width: 1280px) 22vw, (min-width: 768px) 30vw, 46vw'])
@php
    $purchasable = $product->isPurchasable();
    $url = route('products.show', $product);
    $features = $product->cardFeatures();
    $slides = min(4, $product->relationLoaded('media')
        ? $product->media->where('collection_name', 'images')->count()
        : $product->getMedia('images')->count());
    $zoom = 'shrink-0 transition-transform duration-700 ease-out-quart group-hover:scale-[1.05]';
@endphp
<article {{ $attributes->merge(['class' => '@container group relative flex flex-col overflow-hidden rounded-card bg-mist [--card-pad:clamp(0.875rem,9.2cqw,2.5rem)]']) }}
    @if ($slides > 1)
        x-data="{ active: 0, ready: false }"
        x-intersect.once.margin.200px="ready = matchMedia('(hover: hover) and (pointer: fine)').matches"
        {{-- Listens on the card: the title's stretched link covers the photo, so the photo never sees the pointer. --}}
        @mousemove="if (ready) { const box = $refs.photo.getBoundingClientRect(); active = $event.clientY > box.bottom ? 0 : 1 + Math.min({{ $slides - 2 }}, Math.floor(($event.clientX - box.left) / box.width * {{ $slides - 1 }})) }"
        @mouseleave="active = 0"
    @endif>
    <div class="relative">
        <div x-ref="photo" class="relative aspect-square overflow-hidden bg-mist-200">
            <a href="{{ $url }}" tabindex="-1" aria-hidden="true" class="block size-full">
                @if ($slides > 1)
                    <div class="flex size-full transition-[translate] duration-600 ease-out-quart motion-reduce:transition-none"
                         :style="'translate: ' + active * -100 + '% 0'">
                        <x-product.media :product="$product" :eager="$eager" :sizes="$sizes" :class="$zoom" />
                        <template x-if="ready">
                            <div class="contents">
                                @for ($i = 1; $i < $slides; $i++)
                                    <x-product.media :product="$product" :index="$i" loading="eager" :sizes="$sizes" :class="$zoom" />
                                @endfor
                            </div>
                        </template>
                    </div>
                @else
                    <x-product.media :product="$product" :eager="$eager" :sizes="$sizes" :class="$zoom" />
                @endif
            </a>

            @if ($product->hasDiscount())
                <span class="absolute top-3 left-3 rounded-full bg-sale px-2.5 py-1.5 text-[11px] leading-none font-medium text-white @[19rem]:top-5 @[19rem]:left-5 @[19rem]:px-4 @[19rem]:py-2.5 @[19rem]:text-sm">Save {{ $product->discountPercent() }}%</span>
            @endif

            @if ($product->reviews_count)
                <span class="absolute top-3 right-3 inline-flex items-center gap-1 rounded-full bg-white/75 px-2.5 py-1.5 text-[11px] leading-none font-medium text-ink-950 backdrop-blur-md transition-opacity duration-200 lg:group-hover:opacity-0 lg:group-has-[[data-quick-view]:focus-visible]:opacity-0 @[19rem]:top-5 @[19rem]:right-5 @[19rem]:gap-1.5 @[19rem]:px-4 @[19rem]:py-2.5 @[19rem]:text-[13px]">
                    <x-lx-icon name="star-solid" class="size-3 text-star @[19rem]:size-3.5" />{{ number_format((float) $product->rating_avg, 1) }}
                    <span class="sr-only">out of 5</span>
                </span>
            @endif

            <button type="button" x-data data-quick-view @click="$dispatch('quick-view', { id: {{ $product->id }} })"
                    class="absolute top-3 right-3 z-10 hidden size-10 place-items-center rounded-full border border-line bg-white text-ink-950 opacity-0 transition duration-200 group-hover:opacity-100 hover:border-ink-950 hover:bg-ink-950 hover:text-white focus-visible:opacity-100 lg:grid @[19rem]:top-4 @[19rem]:right-4 @[19rem]:size-12">
                <x-lx-icon name="eye" class="size-5" />
                <span class="sr-only">Quick view: {{ $product->name }}</span>
            </button>

            @unless ($purchasable)
                {{-- Frosted pill centred near the photo's foot (clear of the photo-dots tab), as on Ventro. --}}
                <span @class([
                    'absolute left-1/2 -translate-x-1/2 rounded-full border border-line bg-white/60 px-4 py-2.5 text-[12px] leading-none font-medium whitespace-nowrap text-ink-950 backdrop-blur-md @[19rem]:px-7 @[19rem]:py-3.5 @[19rem]:text-base',
                    $slides > 2 ? 'bottom-6 @[19rem]:bottom-7' : 'bottom-3 @[19rem]:bottom-5',
                ])>Sold out</span>
            @endunless
        </div>

        @if ($slides > 2)
            {{-- Photo dots on a tab that sits half over the photo and half over the card body (same colour). --}}
            <div aria-hidden="true" :data-active="active || null"
                 class="pointer-events-none absolute -bottom-3.5 left-1/2 z-10 flex h-7 -translate-x-1/2 translate-y-2 items-center gap-2.5 rounded-full bg-mist px-4 opacity-0 transition duration-300 data-active:translate-y-0 data-active:opacity-100">
                @for ($i = 1; $i < $slides; $i++)
                    <span class="rounded-full transition-all duration-300"
                          :class="active === {{ $i }} ? 'size-2 border-[1.5px] border-ink-950' : 'size-1 bg-ink-950'"></span>
                @endfor
            </div>
        @endif
    </div>

    <div class="flex flex-1 flex-col px-(--card-pad) pt-[clamp(0.875rem,5.5cqw,1.5rem)] pb-(--card-pad)">
        @if ($showCategory && $product->category)
            <p class="mb-2 truncate text-[10.5px] leading-none font-medium tracking-[0.1em] text-ink-500 uppercase @[19rem]:text-[11px]">{{ $product->category->name }}</p>
        @endif
        {{--
            Name, rating and price in Ventro's order. Narrow cards stack them; from 15rem the price sits
            beside the name with the rating on its own line; from 24rem all three share one row and the
            rating block is only as wide as its stars, so "4.9 Stars | 12 Reviews" wraps under them.
        --}}
        <div class="grid grid-cols-1 gap-1.5 @[15rem]:grid-cols-[minmax(0,1fr)_auto] @[15rem]:items-baseline @[15rem]:gap-x-2 @[24rem]:grid-cols-[minmax(0,1fr)_auto_auto] @[24rem]:gap-x-3">
            {{-- text-pretty overrides the base heading balance, which split names like "Pop-up" at the hyphen. --}}
            <{{ $heading }} class="min-w-0 font-sans text-[clamp(0.875rem,0.47rem+3.125cqw,1.25rem)] leading-tight font-medium tracking-[-0.01em] text-pretty text-ink-950">
                <a href="{{ $url }}" class="after:absolute after:inset-0 after:content-[''] hover:text-leather-700">{{ $product->name }}</a>
            </{{ $heading }}>
            @if ($product->reviews_count)
                <p class="flex flex-wrap items-center gap-x-1.5 gap-y-1 text-[12px] leading-tight text-ink-700 @[15rem]:col-span-2 @[15rem]:row-start-2 @[19rem]:text-[13px] @[24rem]:col-span-1 @[24rem]:row-start-auto @[24rem]:w-min @[24rem]:text-[15px]">
                    <x-ui.rating :rating="$product->rating_avg" class="shrink-0" size="size-3.5 @[24rem]:size-[1.125rem]" />
                    <span>{{ (float) $product->rating_avg }}&nbsp;{{ Str::plural('Star', (float) $product->rating_avg) }} | {{ number_format($product->reviews_count) }}&nbsp;{{ Str::plural('Review', $product->reviews_count) }}</span>
                </p>
            @endif
            <x-product.price :current="$product->currentPrice()" :regular="$product->hasDiscount() ? $product->regular_price : null" :quiet="true"
                             size="text-[14px] @[15rem]:text-[15px] @[20rem]:text-base"
                             class="shrink-0 @[15rem]:flex-col @[15rem]:items-end @[15rem]:gap-x-0 @[15rem]:gap-y-1 @[15rem]:leading-tight" />
        </div>
        <x-product.swatches :swatches="$product->colorSwatches()" class="mt-3 @[19rem]:mt-4" size="size-6 @[19rem]:size-8" />
    </div>

    @if ($features)
        {{--
            Ventro's spec strip: a white tray (hairline sides and foot) holding every real feature in a
            row that scrolls sideways. Each callout takes at least 60% of a narrow card (icon over text)
            or 40% (never under 8.75rem) from 17rem (icon beside text), so the next one peeks in; edge
            fades show there is more. With a mouse, the strip slides to its end on hover and back on
            leave, as on Ventro. It sits above the title's stretched link so it can be swiped, and is a
            tab stop only when it overflows.
        --}}
        <div class="relative z-1 mt-auto overflow-hidden rounded-b-card border border-t-0 border-line/50 bg-white"
             x-data="{ start: false, end: false, measure() { const el = this.$refs.strip; this.start = el.scrollLeft > 1; this.end = el.scrollLeft + el.clientWidth < el.scrollWidth - 1 } }"
             x-intersect="measure()" @resize.window.debounce.150ms="measure()">
            <ul x-ref="strip" @scroll.passive="measure()" :tabindex="start || end ? 0 : null" aria-label="Features"
                @mouseenter="matchMedia('(hover: hover)').matches && $el.scrollTo({ left: $el.scrollWidth })"
                @mouseleave="matchMedia('(hover: hover)').matches && $el.scrollTo({ left: 0 })"
                class="flex snap-x snap-mandatory overflow-x-auto overscroll-x-contain px-[calc(var(--card-pad)/2)] py-1.5 [scrollbar-width:none] motion-safe:scroll-smooth @[19rem]:py-2 [&::-webkit-scrollbar]:hidden">
                @foreach ($features as $feature)
                    <li class="my-1.5 flex min-w-[60%] shrink-0 grow basis-0 snap-start flex-col items-center gap-2 px-[calc(var(--card-pad)/2)] text-center not-first:border-l not-first:border-line/50 @[17rem]:min-w-[max(40%,8.75rem)] @[17rem]:flex-row @[17rem]:items-start @[17rem]:text-left">
                        <x-lx-icon :name="$feature['icon']" class="size-[1.125rem] shrink-0 text-ink-700 @[17rem]:size-5 @[24rem]:size-6" />
                        <span class="min-w-0 leading-tight">
                            {{-- No `block` here: it would override line-clamp's display: -webkit-box. --}}
                            <span class="line-clamp-2 text-[11px] font-medium text-ink-950 @[19rem]:text-xs">{{ $feature['title'] }}</span>
                            <span class="mt-1 line-clamp-2 text-[10px] text-ink-500 @[19rem]:text-[10.5px]">{{ $feature['note'] }}</span>
                        </span>
                    </li>
                @endforeach
            </ul>
            <span x-cloak x-show="start" x-transition.opacity.duration.300ms aria-hidden="true"
                  class="pointer-events-none absolute inset-y-0 left-0 w-8 bg-linear-to-r from-white to-transparent @[24rem]:w-12"></span>
            <span x-cloak x-show="end" x-transition.opacity.duration.300ms aria-hidden="true"
                  class="pointer-events-none absolute inset-y-0 right-0 w-8 bg-linear-to-l from-white to-transparent @[24rem]:w-12"></span>
        </div>
    @endif
</article>
