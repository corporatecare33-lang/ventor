@props(['title'])
@php
    $links = [
        ['account.dashboard', 'Overview', 'home', 'account.dashboard'],
        ['account.orders', 'Orders', 'bag', 'account.orders*'],
        ['account.addresses.index', 'Addresses', 'map-pin', 'account.addresses.*'],
        ['account.nfc.index', 'LeatherX Tap profile', 'signal', 'account.nfc.*'],
        ['account.warranty', 'Warranty', 'badge', 'account.warranty'],
        ['account.profile', 'Profile & password', 'user', 'account.profile'],
    ];
@endphp
<div class="border-b border-line bg-paper">
    <div class="container-site py-8 lg:py-10">
        <p class="text-[13px] text-ink-500">Signed in as {{ auth()->user()->name }}</p>
        <h1 class="mt-2 text-h1 text-ink-950">{{ $title }}</h1>
    </div>
</div>
<div class="container-site grid gap-10 py-10 lg:grid-cols-12 lg:py-12">
    <nav class="min-w-0 lg:col-span-3" aria-label="Account">
        <ul class="scrollbar-none -mx-5 flex gap-1 overflow-x-auto px-5 lg:mx-0 lg:flex-col lg:px-0">
            @foreach ($links as [$route, $label, $icon, $pattern])
                @php $active = request()->routeIs($pattern); @endphp
                <li class="shrink-0">
                    <a href="{{ route($route) }}" @if ($active) aria-current="page" @endif
                       class="flex min-h-11 items-center gap-3 rounded-xs px-3 text-[15px] {{ $active ? 'bg-paper font-medium text-ink-950' : 'text-ink-600 hover:bg-paper/60 hover:text-ink-950' }}">
                        <x-lx-icon :name="$icon" class="size-4" /> {{ $label }}
                    </a>
                </li>
            @endforeach
            <li class="shrink-0">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="flex min-h-11 w-full items-center gap-3 rounded-xs px-3 text-[15px] text-ink-600 hover:bg-paper/60 hover:text-ink-950"><x-lx-icon name="logout" class="size-4" /> Sign out</button>
                </form>
            </li>
        </ul>
    </nav>
    <div class="min-w-0 lg:col-span-9">{{ $slot }}</div>
</div>
