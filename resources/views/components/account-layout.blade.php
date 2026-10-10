@props(['title'])
@php
    $links = [
        ['account.dashboard', 'Dashboard', 'home'],
        ['account.orders', 'My orders', 'bag'],
        ['account.addresses.index', 'Addresses', 'map-pin'],
        ['account.nfc.index', 'NFC profile', 'signal'],
        ['account.warranty', 'Warranty', 'badge'],
        ['account.profile', 'Profile & password', 'user'],
    ];
@endphp
<section class="bg-cream-50">
    <div class="container-lx grid gap-8 py-10 lg:grid-cols-[240px_1fr] lg:py-14">
        <aside>
            <div class="rounded-2xl bg-ink-900 p-5 text-white">
                <p class="text-xs text-white/50">Signed in as</p>
                <p class="mt-1 truncate font-semibold">{{ auth()->user()->name }}</p>
            </div>
            <nav class="scrollbar-none mt-3 flex gap-1 overflow-x-auto lg:flex-col" aria-label="Account">
                @foreach ($links as [$route, $label, $icon])
                    @php $active = request()->routeIs($route) || request()->routeIs(Str::beforeLast($route, '.').'.*') && $route !== 'account.dashboard'; @endphp
                    <a href="{{ route($route) }}" class="flex shrink-0 items-center gap-3 rounded-xl px-4 py-2.5 text-sm {{ $active ? 'bg-white font-semibold text-ink-950 shadow-sm' : 'text-ink-700 hover:bg-white/60' }}">
                        <x-lx-icon :name="$icon" class="size-4" /> {{ $label }}
                    </a>
                @endforeach
                <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                    @csrf
                    <button class="flex w-full items-center gap-3 rounded-xl px-4 py-2.5 text-sm text-ink-700 hover:bg-white/60"><x-lx-icon name="logout" class="size-4" /> Sign out</button>
                </form>
            </nav>
        </aside>
        <div class="min-w-0">
            <h1 class="mb-6 font-display text-3xl font-semibold sm:text-4xl">{{ $title }}</h1>
            {{ $slot }}
        </div>
    </div>
</section>
