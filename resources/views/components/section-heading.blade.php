@props(['eyebrow' => null, 'title', 'subtitle' => null, 'align' => 'center', 'dark' => false])
<div {{ $attributes->merge(['class' => ($align === 'center' ? 'mx-auto text-center' : '').' max-w-2xl']) }}>
    @if ($eyebrow)<p class="eyebrow {{ $dark ? 'text-gold-400' : '' }}">{{ $eyebrow }}</p>@endif
    <h2 class="mt-3 font-display text-3xl font-semibold leading-tight sm:text-4xl lg:text-[44px] {{ $dark ? 'text-white' : 'text-ink-950' }}">{{ $title }}</h2>
    @if ($subtitle)<p class="mt-4 text-sm leading-6 sm:text-base {{ $dark ? 'text-white/65' : 'text-ink-600' }}">{{ $subtitle }}</p>@endif
</div>
