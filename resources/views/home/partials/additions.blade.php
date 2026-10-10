{{--
    "Smart additions" carousel: key holders, trackers and desk pieces from the additions
    categories. A native horizontal scroll-snap track (swipe, trackpad or arrow keys once
    focused) with prev / next buttons centred above it, as on Ventro. Buttons scroll by one
    card plus the gap and are marked disabled at either end.
--}}
@php
    $titleId = 'additions-title';
    $trackId = 'additions-track';
@endphp
<section aria-labelledby="{{ $titleId }}" @class(['bg-white py-14 sm:py-16 lg:py-20', 'border-t border-line' => $divider ?? false])
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
    <div class="container-site">
        <header class="reveal text-center">
            <h2 id="{{ $titleId }}" class="title-collection text-cognac-500">{{ $section->title }}</h2>
            @if ($section->subtitle)
                <p class="mx-auto mt-4 max-w-xl text-[15px] leading-relaxed text-ink-700">{{ $section->subtitle }}</p>
            @endif
        </header>

        {{-- aria-disabled (not disabled) keeps keyboard focus on the button when the track reaches an end. --}}
        <div class="mt-6 flex justify-center gap-3 sm:mt-8" x-show="canPrev || canNext">
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

        {{-- `relative` keeps the cards' sr-only text from widening the page on mobile. --}}
        <div id="{{ $trackId }}" x-ref="track" @scroll="onScroll()" tabindex="0" role="region"
             aria-label="{{ $section->title }}: product carousel, scrolls horizontally"
             class="relative mt-8 snap-x snap-mandatory overflow-x-auto overscroll-x-contain scrollbar-none motion-safe:scroll-smooth sm:mt-10">
            <ul role="list" x-ref="list" class="flex gap-4 lg:gap-5">
                @foreach ($products as $product)
                    <li class="flex w-[72%] shrink-0 snap-start sm:w-[44%] md:w-[31%] lg:w-[calc((100%-3*1.25rem)/4)]">
                        <x-product.card :product="$product" class="w-full"
                                        sizes="(min-width: 1280px) 284px, (min-width: 1024px) 22vw, (min-width: 768px) 30vw, (min-width: 640px) 42vw, 70vw" />
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</section>
