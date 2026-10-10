@props(['title', 'intro' => null])
<section class="grid min-h-[calc(100dvh-4.5rem)] lg:grid-cols-2">
    <div class="relative hidden overflow-hidden bg-ink-950 lg:block">
        <img src="{{ asset('images/og-default.jpg') }}" alt="" class="absolute inset-0 size-full object-cover object-right opacity-70" width="1200" height="630" loading="lazy">
        <div class="relative flex h-full flex-col justify-end p-12 text-white">
            <p class="max-w-md font-display text-[2rem] leading-tight">Track orders, manage your LeatherX Tap profile and register warranty in one place.</p>
        </div>
    </div>
    <div class="flex items-center justify-center px-5 py-12 sm:py-16">
        <div class="w-full max-w-md">
            <h1 class="text-h1 text-ink-950">{{ $title }}</h1>
            @if ($intro)<p class="mt-3 text-[15px] text-ink-600">{{ $intro }}</p>@endif
            {{ $slot }}
        </div>
    </div>
</section>
