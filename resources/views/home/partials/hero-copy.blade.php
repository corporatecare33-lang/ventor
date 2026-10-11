{{--
    Hero copy, laid out as on Ventro: a frosted badge with a live dot, a two-line title (brass second line),
    a short text, two pill buttons and up to three store facts. Shared by the photo slides and the typographic fallback.
    Expects: $banner (Banner|null), $title, $eyebrow, $heading ('h1' | 'h2'), $trust.
--}}
@php [$line1, $line2] = array_pad(array_map('trim', explode('|', $title, 2)), 2, null); @endphp

@if ($eyebrow)
    <p class="mb-5 inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/[0.06] px-4 py-1.5 text-[11px] font-medium tracking-[0.23em] text-white/70 uppercase backdrop-blur-sm" data-hero-item>
        <span class="size-1.5 shrink-0 animate-pulse rounded-full bg-live" aria-hidden="true"></span>{{ $eyebrow }}
    </p>
@endif
<{{ $heading }} @if ($heading === 'h1') id="hero-title" @endif data-hero-title
    class="mb-4 max-w-[50rem] font-sans text-[2rem] leading-[1.05] font-bold tracking-[-0.025em] text-white min-[30.0625rem]:text-[clamp(2.25rem,6vw,4.5rem)]">
    {{ $line1 }}
    @if ($line2)<span class="block text-brass-300">{{ $line2 }}</span>@endif
</{{ $heading }}>
@if ($banner?->subtitle)
    <p class="mb-8 max-w-[32.5rem] text-[clamp(0.875rem,1.4vw,1.125rem)] leading-[1.6] text-white/65" data-hero-item>{{ $banner->subtitle }}</p>
@endif

{{-- Full-width stacked pills on phones, side by side from md --}}
<div class="grid w-full gap-4 md:flex md:w-auto md:flex-wrap md:items-center {{ $banner?->subtitle ? '' : 'mt-4' }}" data-hero-item>
    <a href="{{ $banner?->button_link ?: route('shop') }}"
       class="btn-brass relative overflow-hidden px-[2.125rem] text-[13px] tracking-[0.08em] transition-[color,background-color,translate,box-shadow] duration-300 before:absolute before:inset-0 before:-translate-x-full before:bg-linear-to-r before:from-transparent before:via-white/25 before:to-transparent before:transition-transform before:duration-500 hover:-translate-y-0.5 hover:bg-brass-300 hover:shadow-[0_8px_28px_rgb(215_180_122/0.35)] hover:before:translate-x-full">
        {{ $banner?->button_text ?: 'Explore collection' }} <x-lx-icon name="arrow-right" class="size-4" />
    </a>
    @if ($banner?->secondary_button_text && $banner?->secondary_button_link)
        <a href="{{ $banner->secondary_button_link }}"
           class="btn-outline-inverse border-white/25 px-[1.875rem] font-medium tracking-[0.04em] text-white/85 transition-[color,background-color,border-color,translate] duration-300 hover:-translate-y-0.5 hover:border-white/50 hover:bg-white/[0.08] hover:text-white">
            {{ $banner->secondary_button_text }}
        </a>
    @endif
</div>

@if ($trust->isNotEmpty())
    <ul class="mt-9 flex flex-wrap items-center gap-3 text-[12px] font-medium tracking-[0.04em] text-white/55 uppercase md:gap-6" data-hero-item>
        @foreach ($trust as $fact)
            <li class="flex items-center gap-2">
                @unless ($loop->first)<span class="mr-4 hidden h-4 w-px bg-white/15 md:block" aria-hidden="true"></span>@endunless
                <x-lx-icon :name="$fact['icon']" class="size-4 shrink-0 opacity-50" />
                {{ $fact['value'] }}
            </li>
        @endforeach
    </ul>
@endif
