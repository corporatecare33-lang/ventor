{{--
    Full-bleed ribbon of brand promises in large outline type (Ventro's "Trusted by…" band, with
    LeatherX's own promises — each one backed by the catalogue, see HomeController). The moving
    copy is decorative and hidden from assistive tech; the list below it is read instead.
    The track holds the promises twice and slides by half its width, so the loop is seamless.
--}}
<section data-marquee class="marquee relative overflow-hidden bg-white py-3 sm:py-4" aria-label="What LeatherX offers"
         x-data="{ paused: false }">
    <ul role="list" class="sr-only">
        @foreach ($promises as $promise)
            <li>{{ $promise }}</li>
        @endforeach
    </ul>

    {{--
        Outline type: `text-outline` paints a white fill over the stroke, which hides the variable
        font's overlapping contours (stray lines inside R and A). The fill covers the inner half of
        the 1.5px stroke, so the visible line stays fine at this smaller ribbon size.
        When motion is reduced the strip stands still, so it starts at the page gutter instead of the viewport edge.
    --}}
    <div class="marquee-track flex w-max text-[clamp(1.75rem,1.2rem+2vw,2.75rem)] leading-none font-extrabold tracking-[-0.02em] uppercase motion-reduce:pl-5 sm:motion-reduce:pl-8 lg:motion-reduce:pl-10"
         :style="paused ? { animationPlayState: 'paused' } : {}" aria-hidden="true">
        @foreach ([1, 2] as $copy)
            <div class="flex shrink-0 items-center gap-x-[0.4em] pr-[0.4em]">
                @foreach ($promises as $promise)
                    <span class="text-outline whitespace-nowrap [-webkit-text-stroke-width:1.5px]">{{ $promise }}</span>
                    <svg viewBox="0 0 24 24" class="size-[0.42em] shrink-0 fill-current text-cognac-500">
                        <path d="M12 0c.7 6.4 5.6 11.3 12 12-6.4.7-11.3 5.6-12 12-.7-6.4-5.6-11.3-12-12C6.4 11.3 11.3 6.4 12 0Z" />
                    </svg>
                @endforeach
            </div>
        @endforeach
    </div>

    {{-- Soft white edges so the words fade in and out; the left one is solid under the pause button (Ventro puts it on the left). --}}
    <div class="pointer-events-none absolute inset-y-0 left-0 w-32 bg-linear-to-r from-white from-[55%] to-transparent motion-reduce:hidden sm:w-40 sm:from-45%" aria-hidden="true"></div>
    <div class="pointer-events-none absolute inset-y-0 right-0 w-20 bg-linear-to-l from-white to-transparent sm:w-32" aria-hidden="true"></div>

    {{-- Moving content needs a way to stop it (WCAG 2.2.2); hidden when motion is reduced, as the strip is static then. --}}
    <button type="button" data-marquee-toggle @click="paused = ! paused" :aria-pressed="paused.toString()" aria-pressed="false"
            class="absolute top-1/2 left-3 z-10 grid size-11 -translate-y-1/2 place-items-center rounded-full border border-line-strong bg-white text-ink-950 transition-colors hover:border-ink-950 motion-reduce:hidden sm:left-5">
        <span class="sr-only">Pause the scrolling promises</span>
        <x-lx-icon name="pause" class="size-4" x-show="! paused" />
        <x-lx-icon name="play" class="size-4" x-show="paused" x-cloak />
    </button>
</section>
