{{--
    "Smart additions" carousel: key holders, trackers and desk pieces from the additions
    categories, laid out like Ventro's "Smart Additions" slider. A native horizontal scroll-snap
    track (swipe, trackpad or arrow keys once focused) with large round prev / next buttons
    centred above it. Buttons scroll by one card plus the gap and are marked disabled at either
    end. The title is deliberately smaller than the collection titles above, so it reads as a
    coda rather than another collection.
--}}
@php
    $titleId = 'additions-title';
    $trackId = 'additions-track';
@endphp
{{--
    The section is a size container so the track's frame can use 100cqw (the page width
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
                <p class="mx-auto mt-4 max-w-2xl text-[clamp(1rem,1.052vw,1.25rem)] leading-normal text-pretty text-ink-800">{{ $section->subtitle }}</p>
            @endif
        </header>

        {{-- aria-disabled (not disabled) keeps keyboard focus on the button when the track reaches an end. --}}
        @php($arrow = 'grid size-12 place-items-center rounded-full border border-ink-950 text-ink-950 transition-colors not-aria-disabled:hover:bg-ink-950 not-aria-disabled:hover:text-white aria-disabled:cursor-default aria-disabled:border-line aria-disabled:text-ink-500/50 lg:size-14 2xl:size-16')
        <div class="mt-6 flex justify-center gap-3 lg:mt-8" x-show="canPrev || canNext">
            <button type="button" @click="go(-1)" aria-controls="{{ $trackId }}" aria-label="Previous products"
                    aria-disabled="true" :aria-disabled="(! canPrev).toString()" class="{{ $arrow }}">
                <x-lx-icon name="chevron-left" class="size-5 lg:size-6" />
            </button>
            <button type="button" @click="go(1)" aria-controls="{{ $trackId }}" aria-label="Next products"
                    aria-disabled="false" :aria-disabled="(! canNext).toString()" class="{{ $arrow }}">
                <x-lx-icon name="chevron-right" class="size-5 lg:size-6" />
            </button>
        </div>
    </div>

    {{--
        Full-bleed track, as Ventro's slider: it spans the page and its padding (--frame) is the
        frame the cards snap to: the page gutter below lg, then Ventro's 3rem gutter around a frame
        of at most 1900px. Cards fill that frame (3 across from lg, 4 from xl), the next one peeks
        in from the right gutter and scrolled cards pass under the left one. The list is w-max so
        the track's right padding survives at the end of the scroll (browsers drop it after
        overflowing content); card widths are therefore cqw of the track's content box. `relative`
        stops the cards' sr-only text widening the page. The focus ring keeps the default outset:
        an inset ring is painted under the cards, while the outset one shows above and below the
        track (only its sides are clipped at the screen edges).
    --}}
    <div id="{{ $trackId }}" x-ref="track" @scroll="onScroll()" tabindex="0" role="region"
         aria-label="{{ $section->title }}: product carousel, scrolls horizontally"
         class="@container relative mt-6 snap-x snap-mandatory scroll-px-(--frame) overflow-x-auto overscroll-x-contain px-(--frame) scrollbar-none [--frame:1.25rem] motion-safe:scroll-smooth sm:mt-8 sm:[--frame:2rem] lg:mt-12 lg:[--frame:max(3rem,50cqw-59.375rem)]">
        <ul role="list" x-ref="list" data-anim="cards" class="flex w-max gap-(--gap) [--gap:0.75rem] sm:[--gap:1rem] lg:[--gap:clamp(1rem,1.263vw,1.5rem)]">
            @foreach ($products as $product)
                <li class="flex w-[60cqw] shrink-0 snap-start sm:w-[40cqw] md:w-[30cqw] lg:w-[calc((100cqw-var(--gap)*2)/3)] xl:w-[calc((100cqw-var(--gap)*3)/4)]">
                    <x-product.card :product="$product" class="w-full"
                                    sizes="(min-width: 1280px) 24vw, (min-width: 1024px) 31vw, (min-width: 768px) 30vw, (min-width: 640px) 40vw, 60vw" />
                </li>
            @endforeach
        </ul>
    </div>
</section>
