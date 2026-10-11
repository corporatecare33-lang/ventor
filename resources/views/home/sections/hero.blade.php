@php
    // Slides come from Admin → Banners (position "Hero"). Only banners with a photograph are shown as slides.
    // In the title, text after a "|" is set on its own line in brass, e.g. "Full-grain leather,|made smarter."
    $slides = $heroes->filter(fn ($banner) => $banner->hasMedia('image'))->values();
    $trust = collect(['warranty', 'nfc', 'engraving', 'cod'])->map(fn ($key) => $facts[$key] ?? null)->filter()->take(3)->values();
    // Extra room under the copy when the slide controls sit along the bottom edge
    $copyBottom = $slides->count() > 1 ? 'pb-28 md:pb-[clamp(7rem,12vh,8.5rem)]' : 'pb-20 md:pb-[clamp(3.75rem,10vh,7.5rem)]';
@endphp

{{--
    Full-screen hero as on Ventro: the photo fills the viewport height (500–1000px), the copy sits at the bottom
    of a 1400px column, and the photo darkens towards the bottom before fading into the dark section below.
--}}
@if ($slides->isNotEmpty())
    <section class="relative isolate overflow-hidden bg-ink-950 text-white" aria-roledescription="carousel" aria-label="Featured collections" data-hero
             x-data="{
                i: 0,
                n: {{ $slides->count() }},
                playing: {{ $slides->count() > 1 ? 'true' : 'false' }},
                timer: null,
                scrolled: false,
                start() { this.stop(); if (this.playing && this.n > 1) this.timer = setInterval(() => this.go(this.i + 1), 7000) },
                stop() { clearInterval(this.timer) },
                go(k) { const next = (k + this.n) % this.n; if (next === this.i) return; this.i = next; this.$dispatch('hero:slide', { index: next }) },
                toggle() { this.playing = ! this.playing; this.playing ? this.start() : this.stop() },
                sx: null,
                sy: 0,
                /* Touching the slides stops autoplay for good; a mostly horizontal drag of 40px+ changes slide. */
                touchStart(e) {
                    this.playing = false; this.stop();
                    this.sx = e.touches.length === 1 ? e.touches[0].clientX : null;
                    this.sy = e.touches.length === 1 ? e.touches[0].clientY : 0;
                },
                touchEnd(e) {
                    if (this.sx === null) return;
                    const dx = e.changedTouches[0].clientX - this.sx, dy = e.changedTouches[0].clientY - this.sy;
                    this.sx = null;
                    if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy)) this.go(this.i + (dx < 0 ? 1 : -1));
                },
             }"
             x-init="if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) playing = false; start()"
             @scroll.window.passive="scrolled = window.scrollY > 80"
             @mouseenter="stop()" @mouseleave="start()" @focusin="stop()" @focusout="if (! $el.contains($event.relatedTarget)) start()"
             @keydown.arrow-right="go(i + 1)" @keydown.arrow-left="go(i - 1)">

        {{--
            All slides share one grid cell, so the section is as tall as the tallest slide.
            Swipes are read here rather than on the section, so a tap on the play/pause button is not
            also counted as a touch that stops autoplay (which would undo the button's own toggle).
            touch-action keeps vertical scrolling and pinch-zoom with the browser but leaves horizontal drags
            to the swipe (otherwise the browser may claim them as a back gesture and cancel the touch).
            Slide changes are announced only while the slideshow is not rotating on its own.
        --}}
        <div class="grid touch-pan-y touch-pinch-zoom" aria-live="off" :aria-live="playing ? 'off' : 'polite'"
             @touchstart.passive="touchStart($event)" @touchend="touchEnd($event)">
            @foreach ($slides as $index => $slide)
                @php
                    $image = $slide->getFirstMedia('image');
                    $mobile = $slide->getFirstMedia('mobile_image');
                @endphp
                <div class="relative isolate col-start-1 row-start-1 flex min-h-hero transition-opacity duration-700 ease-out-quart {{ $index === 0 ? 'opacity-100' : 'pointer-events-none opacity-0' }}"
                     :class="i === {{ $index }} ? 'opacity-100' : 'pointer-events-none opacity-0'"
                     :inert="i !== {{ $index }}" :aria-hidden="(i !== {{ $index }}).toString()"
                     @if ($index > 0) inert aria-hidden="true" @endif
                     role="group" aria-roledescription="slide" aria-label="{{ $index + 1 }} of {{ $slides->count() }}" data-hero-slide="{{ $index }}">
                    <picture class="absolute inset-0 -z-10">
                        @if ($mobile)
                            <source media="(max-width: 639px)" srcset="{{ $mobile->getUrl('mobile') }} 800w" sizes="100vw">
                        @endif
                        <x-ui.picture :media="$image" :eager="$index === 0" sizes="100vw" width="1920" height="1080"
                                      class="size-full scale-[1.02] object-cover object-[center_40%] transition-transform duration-[7000ms] ease-out"
                                      ::class="i === {{ $index }} && 'scale-[1.06]'" />
                    </picture>
                    {{-- Light at the top, dark at the bottom where the copy sits; then a fine grain and a soft vignette --}}
                    <div class="absolute inset-0 -z-10 bg-[linear-gradient(180deg,rgb(22_19_15/0.35)_0%,rgb(22_19_15/0.1)_25%,rgb(22_19_15/0.12)_45%,rgb(22_19_15/0.55)_75%,rgb(22_19_15/0.95)_100%)]"></div>
                    <div class="pointer-events-none absolute inset-0 -z-10 bg-grain opacity-[0.04]"></div>
                    <div class="pointer-events-none absolute inset-0 -z-10 shadow-[inset_0_0_150px_40px_rgb(0_0_0/0.4)]"></div>

                    <div class="container-hero flex flex-col items-start justify-end pt-32 {{ $copyBottom }}">
                        @include('home.partials.hero-copy', [
                            'banner' => $slide,
                            'title' => $slide->title,
                            'eyebrow' => $slide->eyebrow,
                            'heading' => $index === 0 ? 'h1' : 'h2',
                        ])
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Fades the photo into the dark section below --}}
        <div class="pointer-events-none absolute inset-x-0 bottom-0 h-15 bg-linear-to-b from-transparent to-ink-950 md:h-20" aria-hidden="true"></div>

        {{-- Scroll cue: a brass drop running down a short line; it fades once the page is scrolled --}}
        <div class="pointer-events-none absolute bottom-6 left-1/2 z-10 hidden -translate-x-1/2 transition-opacity duration-500 md:block"
             :class="scrolled && 'opacity-0'" aria-hidden="true">
            <div class="relative h-7 w-px overflow-hidden bg-linear-to-b from-white/40 to-transparent">
                <span class="absolute inset-0 animate-scroll-drop bg-brass-300"></span>
            </div>
        </div>

        @if ($slides->count() > 1)
            {{-- The bar lets touches through except on its buttons, so a swipe can start anywhere on the photo --}}
            <div class="pointer-events-none absolute inset-x-0 bottom-0 z-10">
                <div class="container-hero flex items-center justify-between gap-4 pb-6 sm:pb-8">
                    {{-- Each indicator is a 44px-wide target; the negative margin lines the first bar up with the copy --}}
                    <div class="pointer-events-auto -ml-1.5 flex min-w-0 flex-wrap items-center">
                        @foreach ($slides as $index => $slide)
                            <button type="button" @click="go({{ $index }})" class="group flex h-11 items-center px-1.5"
                                    :aria-current="(i === {{ $index }}).toString()" aria-label="Show slide {{ $index + 1 }}: {{ str_replace('|', ' ', $slide->title) }}">
                                <span class="block h-0.5 w-8 overflow-hidden rounded-full bg-white/25 transition-[width] duration-300 sm:w-12" :class="i === {{ $index }} && 'w-14 sm:w-20'">
                                    <span class="block h-full bg-brass-300 transition-transform duration-300" :class="i === {{ $index }} ? 'translate-x-0' : '-translate-x-full'"></span>
                                </span>
                            </button>
                        @endforeach
                        {{-- Decorative (the buttons carry aria-current); dropped on the narrowest phones to make room --}}
                        <span class="ml-3 hidden text-[11px] font-semibold tracking-[0.14em] text-white/60 tabular-nums min-[22.5rem]:inline" aria-hidden="true">
                            <span x-text="String(i + 1).padStart(2, '0')">01</span> / {{ str_pad($slides->count(), 2, '0', STR_PAD_LEFT) }}
                        </span>
                    </div>
                    <div class="pointer-events-auto flex shrink-0 items-center gap-2">
                        <button type="button" @click="toggle()" class="inline-flex size-11 items-center justify-center rounded-full border border-white/25 text-white hover:border-white"
                                :aria-label="playing ? 'Pause slideshow' : 'Play slideshow'">
                            <x-lx-icon name="pause" class="size-4" x-show="playing" />
                            <x-lx-icon name="play" class="size-4" x-show="! playing" x-cloak />
                        </button>
                        <button type="button" @click="go(i - 1)" class="hidden size-11 items-center justify-center rounded-full border border-white/25 text-white hover:border-white sm:inline-flex" aria-label="Previous slide">
                            <x-lx-icon name="chevron-left" class="size-4" />
                        </button>
                        <button type="button" @click="go(i + 1)" class="hidden size-11 items-center justify-center rounded-full border border-white/25 text-white hover:border-white sm:inline-flex" aria-label="Next slide">
                            <x-lx-icon name="chevron-right" class="size-4" />
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </section>
@else
    {{-- Typographic hero used until a banner photograph is uploaded; same height, column and type scale as the slides --}}
    <section class="relative isolate flex min-h-hero overflow-hidden bg-ink-950 text-white" aria-labelledby="hero-title" data-hero>
        <div class="pointer-events-none absolute inset-0 -z-10 bg-grain opacity-[0.04]"></div>
        <div class="container-hero flex flex-col items-start justify-end pt-32 {{ $copyBottom }}" data-hero-slide="0">
            @include('home.partials.hero-copy', [
                'banner' => $hero,
                'title' => $hero->title ?? 'Leather goods for everyday carry,|made smarter.',
                'eyebrow' => $hero->eyebrow ?? 'Wallets · Card holders · Bags',
                'heading' => 'h1',
            ])
        </div>
    </section>
@endif
