@props(['title', 'subtitle' => null, 'eyebrow' => null, 'crumbs' => []])
<section class="relative overflow-hidden bg-ink-900 text-white">
    <div class="absolute inset-0 opacity-60 placeholder-leather"></div>
    <div class="container-lx relative py-12 sm:py-16">
        <x-breadcrumbs :items="$crumbs" dark />
        @if ($eyebrow)<p class="eyebrow mt-6 text-gold-400">{{ $eyebrow }}</p>@endif
        <h1 class="mt-3 max-w-3xl font-display text-4xl font-semibold leading-tight sm:text-5xl">{{ $title }}</h1>
        @if ($subtitle)<p class="mt-4 max-w-2xl text-sm leading-6 text-white/70 sm:text-base">{{ $subtitle }}</p>@endif
        {{ $slot }}
    </div>
</section>
