@php
    $desktop = $hero?->getFirstMedia('image');
    $mobile = $hero?->getFirstMedia('mobile_image') ?? $desktop;
@endphp
@if ($desktop)
    {{-- Photographic hero: image on top for phones, full-bleed with text over the dark side on large screens --}}
    <section class="relative isolate bg-ink-950 text-white" aria-labelledby="hero-title">
        <picture class="block aspect-[4/3] sm:aspect-[16/9] lg:absolute lg:inset-0 lg:-z-10 lg:aspect-auto">
            <source media="(max-width: 639px)" srcset="{{ $mobile->getUrl('mobile') }} 800w" sizes="100vw">
            <x-ui.picture :media="$desktop" eager sizes="100vw" :alt="$desktop->getCustomProperty('alt', '')" width="1920" height="720" />
        </picture>
        <div class="absolute inset-0 -z-10 hidden bg-[linear-gradient(90deg,var(--color-ink-950)_0%,rgb(22_19_15/0.86)_38%,rgb(22_19_15/0.15)_72%,transparent_100%)] lg:block"></div>

        <div class="container-site grid py-10 sm:py-14 lg:min-h-[min(40rem,78vh)] lg:items-center lg:py-20">
            <div class="max-w-xl">
                <p class="eyebrow-inverse">{{ $hero->eyebrow }}</p>
                <h1 id="hero-title" class="mt-4 text-[clamp(2.125rem,1.6rem+2.2vw,3.5rem)] leading-[1.06] text-white">{{ $hero->title }}</h1>
                @if ($hero->subtitle)
                    <p class="mt-5 text-[1.0625rem] leading-relaxed text-white/80">{{ $hero->subtitle }}</p>
                @endif
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ $hero->button_link ?: route('shop') }}" class="btn-inverse">{{ $hero->button_text ?: 'Shop the collection' }}</a>
                    @if ($hero->secondary_button_text)
                        <a href="{{ $hero->secondary_button_link }}" class="btn-ghost-inverse">{{ $hero->secondary_button_text }}</a>
                    @endif
                </div>
            </div>
        </div>
    </section>
@else
    {{-- Typographic hero used until a banner photograph is uploaded --}}
    <section class="bg-paper" aria-labelledby="hero-title">
        <div class="container-site py-14 sm:py-20">
            <div class="max-w-3xl">
                <p class="eyebrow">{{ $hero->eyebrow ?? 'Wallets · Card holders · Bags · NFC smart wallets' }}</p>
                <h1 id="hero-title" class="mt-4 text-[clamp(2.125rem,1.6rem+2.2vw,3.5rem)] leading-[1.06] text-ink-950">{{ $hero->title ?? 'Leather goods for everyday carry, made smarter with LeatherX Tap.' }}</h1>
                @if ($hero?->subtitle)<p class="lead mt-5 max-w-xl">{{ $hero->subtitle }}</p>@endif
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ $hero->button_link ?? route('shop') }}" class="btn-primary">{{ $hero->button_text ?? 'Shop the collection' }}</a>
                    @if ($hero?->secondary_button_text)<a href="{{ $hero->secondary_button_link }}" class="btn-secondary">{{ $hero->secondary_button_text }}</a>@endif
                </div>
            </div>
        </div>
    </section>
@endif

@if ($facts)
    {{-- Store facts, all computed from live settings and catalogue data --}}
    <section aria-label="Delivery, payment, warranty and engraving" class="border-b border-line bg-paper">
        <dl class="container-site grid grid-cols-2 gap-x-6 gap-y-6 py-7 lg:grid-cols-4">
            @foreach ($facts as $fact)
                <div class="lg:border-l lg:border-line-strong lg:pl-6 lg:first:border-l-0 lg:first:pl-0">
                    <dt class="label-caps text-ink-500">{{ $fact['label'] }}</dt>
                    <dd class="mt-1.5 text-[15px] font-medium text-ink-950">{{ $fact['value'] }}</dd>
                    <dd class="mt-0.5 text-[13px] leading-snug text-ink-500">{{ $fact['note'] }}</dd>
                </div>
            @endforeach
        </dl>
    </section>
@endif
