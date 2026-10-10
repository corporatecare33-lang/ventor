@php $banner = $hero->first(); @endphp
<section class="relative isolate overflow-hidden bg-ink-950 text-white">
    @if ($banner && $banner->hasMedia('image'))
        <picture class="absolute inset-0 -z-10">
            @if ($banner->hasMedia('mobile_image'))
                <source media="(max-width: 767px)" srcset="{{ $banner->getFirstMediaUrl('mobile_image') }}">
            @endif
            <img src="{{ $banner->getFirstMediaUrl('image') }}" alt="" class="size-full object-cover opacity-55" fetchpriority="high" decoding="async" width="1920" height="900">
        </picture>
    @else
        <div class="absolute inset-0 -z-10 placeholder-leather opacity-80"></div>
    @endif
    <div class="absolute inset-0 -z-10 bg-gradient-to-r from-ink-950 via-ink-950/80 to-transparent"></div>

    <div class="container-lx grid items-center gap-10 py-16 sm:py-24 lg:grid-cols-2 lg:py-28">
        <div>
            <p class="eyebrow text-gold-400">{{ $banner->eyebrow ?? 'Handcrafted · NFC enabled' }}</p>
            <h1 class="mt-5 font-display text-[42px] leading-[1.05] font-semibold sm:text-6xl lg:text-7xl">
                {{ $banner->title ?? 'Leather, crafted for the way you carry today' }}
            </h1>
            <p class="mt-6 max-w-xl text-[15px] leading-7 text-white/70">
                {{ $banner->subtitle ?? 'Full-grain leather wallets and bags with RFID protection and a built-in NFC digital profile.' }}
            </p>
            <div class="mt-9 flex flex-wrap gap-3">
                <a href="{{ $banner->button_link ?? route('shop') }}" class="btn-gold">{{ $banner->button_text ?? 'Shop the collection' }} <x-lx-icon name="arrow-right" class="size-4" /></a>
                @if ($banner?->secondary_button_text)
                    <a href="{{ $banner->secondary_button_link }}" class="btn-outline-light">{{ $banner->secondary_button_text }}</a>
                @endif
            </div>
            @if ($trusted = setting('trusted_customers'))
                <p class="mt-10 flex items-center gap-3 text-xs text-white/55">
                    <x-stars :rating="5" /> {{ $trusted }}
                </p>
            @endif
        </div>

        {{-- Decorative product stage (replaced by the banner photo once uploaded) --}}
        @unless ($banner && $banner->hasMedia('image'))
            <div class="relative mx-auto hidden aspect-square w-full max-w-md lg:block" aria-hidden="true">
                <div class="absolute inset-8 rounded-full bg-gold-500/10 blur-3xl"></div>
                <div class="absolute top-[18%] left-[12%] h-[58%] w-[70%] -rotate-6 rounded-[28px] border border-tan-300/20 bg-gradient-to-br from-leather-700 via-leather-800 to-leather-900 shadow-2xl shadow-black/60">
                    <div class="absolute inset-3 rounded-[22px] border border-dashed border-tan-300/25"></div>
                    <div class="absolute right-6 bottom-6 font-display text-lg tracking-[0.3em] text-gold-400/80">LX</div>
                </div>
                <div class="absolute top-[30%] left-[30%] h-[52%] w-[60%] rotate-6 rounded-[26px] border border-white/10 bg-gradient-to-br from-ink-700 to-ink-950 shadow-2xl shadow-black/70">
                    <div class="absolute inset-3 rounded-[20px] border border-dashed border-white/10"></div>
                    <div class="absolute top-6 left-6 flex items-center gap-2 text-[10px] tracking-[0.2em] text-gold-300/80 uppercase"><x-lx-icon name="signal" class="size-4" /> Tap</div>
                </div>
            </div>
        @endunless
    </div>
</section>
