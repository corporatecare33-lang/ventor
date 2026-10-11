{{--
    Product rows for one mega-menu group (fetched by the header on first open and injected
    into the panel). Each row belongs to one category and is shown while that category is
    active in the panel's list — `active` comes from the panel's Alpine scope. When a row is
    wider than the panel, round prev/next buttons scroll it by one card.
--}}
@foreach ($rows as ['category' => $category, 'products' => $products])
    <div x-show="active === @js($category->slug)" @if (! $loop->first) x-cloak @endif
         x-data="{
            canPrev: false, canNext: false,
            update() { const row = $refs.row; this.canPrev = row.scrollLeft > 4; this.canNext = row.scrollLeft + row.clientWidth < row.scrollWidth - 4 },
            step(direction) { const row = $refs.row; row.scrollBy({ left: direction * (row.firstElementChild.offsetWidth + 16), behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' }) },
         }"
         x-effect="if (active === @js($category->slug)) $nextTick(() => update())" @resize.window.debounce.150ms="update()">
        <div class="flex items-center justify-between gap-6">
            <p class="text-[12px] font-medium tracking-[0.12em] text-ink-500 uppercase">Most popular</p>
            <div class="flex items-center gap-2">
                <template x-if="canPrev || canNext">
                    <div class="flex gap-2">
                        <button type="button" @click="step(-1)" :disabled="! canPrev" aria-label="Previous {{ $category->name }}"
                                class="inline-flex size-11 items-center justify-center rounded-full border border-line-strong text-ink-950 transition-colors hover:border-ink-950 disabled:cursor-default disabled:opacity-35">
                            <x-lx-icon name="chevron-left" class="size-4" />
                        </button>
                        <button type="button" @click="step(1)" :disabled="! canNext" aria-label="More {{ $category->name }}"
                                class="inline-flex size-11 items-center justify-center rounded-full border border-line-strong text-ink-950 transition-colors hover:border-ink-950 disabled:cursor-default disabled:opacity-35">
                            <x-lx-icon name="chevron-right" class="size-4" />
                        </button>
                    </div>
                </template>
                <a href="{{ route('categories.show', $category) }}" class="group/all ml-2 inline-flex min-h-11 items-center gap-2 text-[17px] font-bold tracking-tight text-ink-950">
                    All {{ $category->name }} <span class="font-medium text-ink-500">({{ $category->active_products_count }})</span>
                    <x-lx-icon name="arrow-right" class="size-5 transition-transform group-hover/all:translate-x-0.5" />
                </a>
            </div>
        </div>
        <ul x-ref="row" @scroll.throttle.100ms="update()" role="list" aria-label="Most popular {{ $category->name }}"
            class="relative mt-3 flex snap-x snap-mandatory gap-4 overflow-x-auto pb-1 scrollbar-none">
            @foreach ($products as $product)
                <li class="w-[15rem] shrink-0 snap-start xl:w-[16.5rem]">
                    <x-product.card :product="$product" :show-category="false" heading="p" sizes="270px" class="h-full" />
                </li>
            @endforeach
        </ul>
    </div>
@endforeach
