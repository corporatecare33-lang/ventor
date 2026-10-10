@php
    $announcementText = $layout['announcement']->title ?? (setting('announcement_enabled') ? setting('announcement_text') : null);
    $announcementLink = $layout['announcement']->button_link ?? setting('announcement_link');
    $nav = [
        ['Shop', route('shop')],
        ['LeatherX Tap', route('tap')],
        ['Engraving', route('engraving')],
        ['Corporate', route('corporate')],
        ['Reviews', route('reviews.index')],
    ];
@endphp
@if ($announcementText)
    <div class="bg-ink-950 text-center text-[12px] tracking-wide text-tan-300">
        <div class="container-lx py-2">
            @if ($announcementLink)
                <a href="{{ $announcementLink }}" class="hover:text-gold-300">{{ $announcementText }}</a>
            @else
                {{ $announcementText }}
            @endif
        </div>
    </div>
@endif

<header x-data="{ menu: false, search: false }" class="sticky top-0 z-40 border-b border-white/5 bg-ink-950 text-white">
    <div class="container-lx flex h-16 items-center gap-4 lg:h-[72px]">
        <button type="button" class="-ml-2 p-2 lg:hidden" @click="menu = true" aria-label="Open menu">
            <x-lx-icon name="menu" class="size-6" />
        </button>

        <a href="{{ route('home') }}" class="flex items-center gap-2" aria-label="{{ setting('site_name', 'LeatherX') }} home">
            @if ($logo = setting('site_logo'))
                <img src="{{ Storage::disk('public')->url($logo) }}" alt="{{ setting('site_name', 'LeatherX') }}" class="h-8 w-auto" width="140" height="32">
            @else
                <span class="font-display text-2xl font-semibold tracking-[0.18em]">LEATHER<span class="text-gold-400">X</span></span>
            @endif
        </a>

        <nav class="ml-8 hidden items-center gap-7 text-[13px] tracking-wide text-white/80 lg:flex" aria-label="Main">
            <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                <a href="{{ route('shop') }}" class="flex items-center gap-1 py-6 hover:text-gold-300 {{ request()->routeIs('shop', 'categories.*') ? 'text-gold-300' : '' }}">
                    Shop <x-lx-icon name="chevron-down" class="size-3.5" />
                </a>
                <div x-cloak x-show="open" x-transition.opacity class="absolute top-full left-0 w-72 rounded-2xl border border-white/10 bg-ink-900 p-2 shadow-2xl">
                    @foreach ($layout['menuCategories'] as $category)
                        <a href="{{ route('categories.show', $category) }}" class="block rounded-xl px-4 py-2.5 hover:bg-white/5">
                            <span class="block text-sm text-white">{{ $category->name }}</span>
                        </a>
                    @endforeach
                    <a href="{{ route('shop') }}" class="mt-1 block rounded-xl px-4 py-2.5 text-xs text-gold-300 hover:bg-white/5">View all products →</a>
                </div>
            </div>
            @foreach (array_slice($nav, 1) as [$label, $url])
                <a href="{{ $url }}" class="hover:text-gold-300 {{ url()->current() === $url ? 'text-gold-300' : '' }}">{{ $label }}</a>
            @endforeach
        </nav>

        <div class="ml-auto flex items-center gap-1 sm:gap-2">
            <button type="button" class="rounded-full p-2 hover:bg-white/5" @click="search = !search; $nextTick(() => $refs.q?.focus())" aria-label="Search">
                <x-lx-icon name="search" class="size-5" />
            </button>
            <a href="{{ auth()->check() ? route('account.dashboard') : route('login') }}" class="rounded-full p-2 hover:bg-white/5" aria-label="Account">
                <x-lx-icon name="user" class="size-5" />
            </a>
            <livewire:cart-counter />
        </div>
    </div>

    <div x-cloak x-show="search" x-transition.opacity class="border-t border-white/5 bg-ink-900">
        <form action="{{ route('shop') }}" method="GET" class="container-lx flex items-center gap-3 py-3" role="search">
            <x-lx-icon name="search" class="size-5 text-white/50" />
            <input x-ref="q" type="search" name="q" value="{{ request('q') }}" placeholder="Search wallets, bags, card holders…" class="w-full bg-transparent py-2 text-sm text-white placeholder:text-white/40 focus:outline-none" aria-label="Search products">
            <button class="btn-gold px-5 py-2">Search</button>
        </form>
    </div>

    {{-- Mobile drawer --}}
    <div x-cloak x-show="menu" class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-black/60" @click="menu = false" x-show="menu" x-transition.opacity></div>
        <div class="absolute inset-y-0 left-0 flex w-[86%] max-w-sm flex-col overflow-y-auto bg-ink-900 p-6" x-show="menu" x-transition:enter="transition duration-300 ease-luxe" x-transition:enter-start="-translate-x-full" x-transition:leave="transition duration-200" x-transition:leave-end="-translate-x-full">
            <div class="mb-8 flex items-center justify-between">
                <span class="font-display text-xl font-semibold tracking-[0.18em]">LEATHER<span class="text-gold-400">X</span></span>
                <button type="button" @click="menu = false" class="p-2" aria-label="Close menu"><x-lx-icon name="x" class="size-6" /></button>
            </div>
            <p class="eyebrow mb-3 text-tan-400">Shop by category</p>
            <div class="mb-6 space-y-1">
                @foreach ($layout['menuCategories'] as $category)
                    <a href="{{ route('categories.show', $category) }}" class="block rounded-lg py-2 text-white/85">{{ $category->name }}</a>
                @endforeach
            </div>
            <div class="space-y-1 border-t border-white/10 pt-6">
                @foreach ($nav as [$label, $url])
                    <a href="{{ $url }}" class="block py-2 text-white/85">{{ $label }}</a>
                @endforeach
                <a href="{{ route('orders.track') }}" class="block py-2 text-white/85">Track order</a>
                <a href="{{ route('warranty') }}" class="block py-2 text-white/85">Warranty</a>
                <a href="{{ route('contact') }}" class="block py-2 text-white/85">Contact</a>
            </div>
            <div class="mt-auto pt-8">
                @auth
                    <a href="{{ route('account.dashboard') }}" class="btn-outline-light w-full">My account</a>
                @else
                    <a href="{{ route('login') }}" class="btn-gold w-full">Sign in / Register</a>
                @endauth
            </div>
        </div>
    </div>
</header>
