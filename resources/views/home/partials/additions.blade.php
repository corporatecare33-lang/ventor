{{--
    "Smart additions" carousel: key holders, trackers and desk pieces from the additions
    categories. A native horizontal scroll-snap track (swipe, trackpad or arrow keys once
    focused) with prev / next buttons centred above it, as on Ventro. Buttons scroll by one
    card plus the gap and are marked disabled at either end. The title is deliberately smaller
    than the collection titles above, so it reads as a coda rather than another collection.
--}}
@php
    $titleId = 'additions-title';
    $trackId = 'additions-track';
@endphp
{{--
    The section is a size container so the track's lg bleed can use 100cqw (the page width
    without the scrollbar, unlike 100vw); overflow-x-clip is a safety net for sub-pixel overshoot.
--}}
<section aria-labelledby="{{ $titleId }}" @class(['@container overflow-x-clip bg-white py-10 sm:py-12 lg:py-14', 'border-t border-line' => $divider ?? false])
         x-data="{
             canPrev: false,
             canNext: true,
             ticking: false,
             step() {
                 const item = this.$refs.list.firstElementChild;
                 const gap = parseFloat(getComputedStyle(this.$refs.list).columnGap) || 0;
                 return item ? item.getBoundingClientRect().width + gap : this.$refs.track.clientWidth;
             },
             go(direction) {
                 if (direction < 0 ? ! this.canPrev : ! this.canNext) return;
                 const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                 this.$refs.track.scrollBy({ left: direction * this.step(), behavior: reduce ? 'auto' : 'smooth' });
             },
             update() {
                 const track = this.$refs.track;
                 this.canPrev = track.scrollLeft > 4;
                 this.canNext = track.scrollLeft + track.clientWidth < track.scrollWidth - 4;
             },
             onScroll() {
                 if (this.ticking) return;
                 this.ticking = true;
                 requestAnimationFrame(() => { this.update(); this.ticking = false; });
             },
         }"
         x-init="$nextTick(() => update())"
         @resize.window.debounce.150ms="update()">
    <div class="container-wide">
        <header class="reveal text-center">
            <h2 id="{{ $titleId }}" class="font-display text-[clamp(1.875rem,1.3rem+2vw,2.75rem)] leading-[1.05] font-bold tracking-[-0.04em] text-balance text-cognac-500">{{ $section->title }}</h2>
            @if ($section->subtitle)
                <p class="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-pretty text-ink-600">{{ $section->subtitle }}</p>
            @endif
        </header>

        {{-- aria-disabled (not disabled) keeps keyboard focus on the button when the track reaches an end. --}}
        <div class="mt-5 flex justify-center gap-3 sm:mt-6" x-show="canPrev || canNext">
            <button type="button" @click="go(-1)" aria-controls="{{ $trackId }}" aria-label="Previous products"
                    aria-disabled="true" :aria-disabled="(! canPrev).toString()"
                    class="grid size-11 place-items-center rounded-full border border-line-strong text-ink-950 transition-colors not-aria-disabled:hover:border-ink-950 aria-disabled:cursor-default aria-disabled:border-line aria-disabled:text-ink-500/50">
                <x-lx-icon name="chevron-left" class="size-4" />
            </button>
            <button type="button" @click="go(1)" aria-controls="{{ $trackId }}" aria-label="Next products"
                    aria-disabled="false" :aria-disabled="(! canNext).toString()"
                    class="grid size-11 place-items-center rounded-full border border-line-strong text-ink-950 transition-colors not-aria-disabled:hover:border-ink-950 aria-disabled:cursor-default aria-disabled:border-line aria-disabled:text-ink-500/50">
                <x-lx-icon name="chevron-right" class="size-4" />
            </button>
        </div>

        {{--
            The track bleeds past the container: to both screen edges below lg (padding and scroll
            padding keep the first card in line with the title), to the right viewport edge only
            from lg (negative margin = the container's gutter plus its side margin). The list is
            w-max so the track's right padding survives at the end of the scroll (browsers drop it
            after overflowing content); card widths are therefore cqw of the track. `relative`
            stops the cards' sr-only text widening the page. The focus ring keeps the default
            outset: an inset ring is painted under the cards, while the outset one shows above and
            below the track (only its sides are clipped at the screen edges).
        --}}
        <div id="{{ $trackId }}" x-ref="track" @scroll="onScroll()" tabindex="0" role="region"
             aria-label="{{ $section->title }}: product carousel, scrolls horizontally"
             class="@container relative -mx-5 mt-6 snap-x snap-mandatory scroll-px-5 overflow-x-auto overscroll-x-contain px-5 scrollbar-none motion-safe:scroll-smooth sm:-mx-8 sm:mt-8 sm:scroll-px-8 sm:px-8 lg:mx-0 lg:-mr-[max(2.5rem,calc((100cqw-90rem)/2+2.5rem))] lg:scroll-px-0 lg:px-0 lg:pr-10">
            <ul role="list" x-ref="list" class="flex w-max gap-3 sm:gap-4 lg:gap-5">
                @foreach ($products as $product)
                    <li class="flex w-[60cqw] shrink-0 snap-start sm:w-[40cqw] md:w-[30cqw] lg:w-[18.5rem] xl:w-[20rem]">
                        <x-product.card :product="$product" class="w-full"
                                        sizes="(min-width: 1280px) 320px, (min-width: 1024px) 296px, (min-width: 768px) 30vw, (min-width: 640px) 40vw, 60vw" />
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</section>
