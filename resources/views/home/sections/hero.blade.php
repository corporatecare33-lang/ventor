@php
    // Slides come from Admin → Banners (position "Hero"). Only banners with a photograph are shown as slides.
    // In the title, text after a "|" is set on its own line in brass, e.g. "Full-grain leather,|made smarter."
    $slides = $heroes->filter(fn ($banner) => $banner->hasMedia('image'))->values();
    $trust = collect(['warranty', 'nfc', 'engraving', 'cod'])->map(fn ($key) => $facts[$key] ?? null)->filter()->take(3)->values();
    $splitTitle = fn (string $title) => array_map('trim', explode('|', $title, 2));
@endphp

@if ($slides->isNotEmpty())
    <section class="relative isolate overflow-hidden bg-ink-950 text-white" aria-roledescription="carousel" aria-label="Featured collections"
             x-data="{
                i: 0,
                n: {{ $slides->count() }},
                playing: {{ $slides->count() > 1 ? 'true' : 'false' }},
                timer: null,
                start() { this.stop(); if (this.playing && this.n > 1) this.timer = setInterval(() => this.go(this.i + 1), 7000) },
                stop() { clearInterval(this.timer) },
                go(k) { this.i = (k + this.n) % this.n },
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
                    [$line1, $line2] = array_pad($splitTitle($slide->title), 2, null);
                    $image = $slide->getFirstMedia('image');
                    $mobile = $slide->getFirstMedia('mobile_image');
                @endphp
                <div class="relative isolate col-start-1 row-start-1 grid min-h-[36rem] transition-opacity duration-700 ease-out-quart sm:min-h-[38rem] lg:min-h-[min(46rem,calc(100svh-9rem))] {{ $index === 0 ? 'opacity-100' : 'pointer-events-none opacity-0' }}"
                     :class="i === {{ $index }} ? 'opacity-100' : 'pointer-events-none opacity-0'"
                     :inert="i !== {{ $index }}" :aria-hidden="(i !== {{ $index }}).toString()"
                     @if ($index > 0) inert aria-hidden="true" @endif
                     role="group" aria-roledescription="slide" aria-label="{{ $index + 1 }} of {{ $slides->count() }}">
                    <picture class="absolute inset-0 -z-10">
                        @if ($mobile)
                            <source media="(max-width: 639px)" srcset="{{ $mobile->getUrl('mobile') }} 800w" sizes="100vw">
                        @endif
                        <x-ui.picture :media="$image" :eager="$index === 0" sizes="100vw" width="1920" height="1080"
                                      class="size-full scale-[1.02] object-cover transition-transform duration-[7000ms] ease-out"
                                      ::class="i === {{ $index }} && 'scale-[1.06]'" />
                    </picture>
                    {{-- Shade behind the copy only: the bottom on phones; the bottom and left on desktop, where the copy sits low in a centred column --}}
                    <div class="absolute inset-0 -z-10 bg-[linear-gradient(to_top,rgb(22_19_15/0.94)_0%,rgb(22_19_15/0.62)_50%,rgb(22_19_15/0.15)_100%)] lg:bg-[linear-gradient(to_top,rgb(22_19_15/0.88)_0%,rgb(22_19_15/0.4)_45%,rgb(22_19_15/0)_80%),linear-gradient(90deg,rgb(22_19_15/0.82)_0%,rgb(22_19_15/0.5)_45%,rgb(22_19_15/0)_80%)]"></div>

                    {{-- Copy sits in the lower part of the photo; on desktop inside a centred 1000px column (as on Ventro) --}}
                    <div class="container-site flex items-end pt-40 pb-24 sm:pt-48 sm:pb-28 lg:pt-36 lg:pb-32">
                        <div class="w-full lg:mx-auto lg:max-w-[62.5rem]">
                            <div class="max-w-[40rem]">
                                @if ($slide->eyebrow)
                                    <p class="pill-label"><span class="size-1.5 rounded-full bg-brass-300" aria-hidden="true"></span>{{ $slide->eyebrow }}</p>
                                @endif
                                <{{ $index === 0 ? 'h1' : 'h2' }} @if ($index === 0) id="hero-title" @endif
                                    class="mt-5 font-sans text-[clamp(2.25rem,1.5rem+2.8vw,3.75rem)] leading-[1.04] font-bold tracking-[-0.035em] text-white">
                                    {{ $line1 }}
                                    @if ($line2)<span class="block text-brass-300">{{ $line2 }}</span>@endif
                                </{{ $index === 0 ? 'h1' : 'h2' }}>
                                @if ($slide->subtitle)
                                    <p class="mt-4 max-w-[34rem] text-[15px] leading-relaxed text-white/75 sm:text-base">{{ $slide->subtitle }}</p>
                                @endif
                                {{-- Full-width pills on phones, side by side from sm --}}
                                <div class="mt-8 grid gap-3 sm:flex sm:flex-wrap sm:items-center">
                                    <a href="{{ $slide->button_link ?: route('shop') }}" class="btn-brass">
                                        {{ $slide->button_text ?: 'Explore collection' }} <x-lx-icon name="arrow-right" class="size-4" />
                                    </a>
                                    @if ($slide->secondary_button_text && $slide->secondary_button_link)
                                        <a href="{{ $slide->secondary_button_link }}" class="btn-outline-inverse">{{ $slide->secondary_button_text }}</a>
                                    @endif
                                </div>
                                @if ($trust->isNotEmpty())
                                    <ul class="mt-8 flex flex-wrap gap-x-6 gap-y-2.5 text-[11px] font-semibold tracking-[0.12em] text-white/60 uppercase">
                                        @foreach ($trust as $fact)
                                            <li class="flex items-center gap-2">
                                                <x-lx-icon :name="$fact['icon']" class="size-4 text-brass-300" />
                                                {{ $fact['value'] }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($slides->count() > 1)
            {{-- The bar lets touches through except on its buttons, so a swipe can start anywhere on the photo --}}
            <div class="pointer-events-none absolute inset-x-0 bottom-0 z-10">
                <div class="container-site pb-6 sm:pb-8">
                    <div class="flex items-center justify-between gap-4 lg:mx-auto lg:max-w-[62.5rem]">
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
            </div>
        @endif
    </section>
@else
    {{-- Typographic hero used until a banner photograph is uploaded; same column and type scale as the slides --}}
    @php [$line1, $line2] = array_pad($splitTitle($hero->title ?? 'Leather goods for everyday carry,|made smarter.'), 2, null); @endphp
    <section class="bg-ink-950 text-white" aria-labelledby="hero-title">
        <div class="container-site py-20 sm:py-28">
            <div class="w-full lg:mx-auto lg:max-w-[62.5rem]">
                <div class="max-w-[40rem]">
                    <p class="pill-label"><span class="size-1.5 rounded-full bg-brass-300" aria-hidden="true"></span>{{ $hero->eyebrow ?? 'Wallets · Card holders · Bags' }}</p>
                    <h1 id="hero-title" class="mt-5 font-sans text-[clamp(2.25rem,1.5rem+2.8vw,3.75rem)] leading-[1.04] font-bold tracking-[-0.035em]">
                        {{ $line1 }} @if ($line2)<span class="block text-brass-300">{{ $line2 }}</span>@endif
                    </h1>
                    @if ($hero?->subtitle)<p class="mt-4 max-w-[34rem] text-[15px] leading-relaxed text-white/75 sm:text-base">{{ $hero->subtitle }}</p>@endif
                    <div class="mt-8 grid gap-3 sm:flex sm:flex-wrap sm:items-center">
                        <a href="{{ $hero?->button_link ?: route('shop') }}" class="btn-brass">{{ $hero?->button_text ?: 'Explore collection' }} <x-lx-icon name="arrow-right" class="size-4" /></a>
                        @if ($hero?->secondary_button_text && $hero?->secondary_button_link)
                            <a href="{{ $hero->secondary_button_link }}" class="btn-outline-inverse">{{ $hero->secondary_button_text }}</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
