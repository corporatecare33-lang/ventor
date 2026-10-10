@php
    $announcementText = $layout['announcement']->title ?? (setting('announcement_enabled') ? setting('announcement_text') : null);
    $announcementLink = $layout['announcement']->button_link ?? setting('announcement_link');
    $nav = [
        ['LeatherX Tap', route('tap'), request()->routeIs('tap')],
        ['Engraving', route('engraving'), request()->routeIs('engraving')],
        ['Corporate gifting', route('corporate'), request()->routeIs('corporate')],
        ['Journal', route('blog.index'), request()->routeIs('blog.*')],
    ];
    $shopActive = request()->routeIs('shop', 'categories.show', 'products.show');
@endphp
<header class="sticky top-0 z-40 bg-white" x-data="{ menu: false, search: false }" @keydown.escape.window="menu = false; search = false">
    @if ($announcementText)
        <div class="bg-ink-950 text-[13px] text-white/85">
            <p class="container-site py-2 text-center">
                @if ($announcementLink)
                    <a href="{{ $announcementLink }}" class="underline-offset-4 hover:text-white hover:underline">{{ $announcementText }}</a>
                @else
                    {{ $announcementText }}
                @endif
            </p>
        </div>
    @endif

    <div class="border-b border-line">
        <div class="container-site flex h-16 items-center gap-3 lg:h-[4.5rem]">
            <button type="button" class="-ml-2 inline-flex size-11 items-center justify-center lg:hidden" @click="menu = true"
                    aria-controls="mobile-menu" :aria-expanded="menu.toString()" aria-label="Open menu">
                <x-lx-icon name="menu" class="size-6" />
            </button>

            <a href="{{ route('home') }}" class="shrink-0 max-lg:absolute max-lg:left-1/2 max-lg:-translate-x-1/2" @if (request()->routeIs('home')) aria-current="page" @endif>
                @if ($logo = setting('site_logo'))
                    <img src="{{ Storage::disk('public')->url($logo) }}" alt="{{ setting('site_name', 'LeatherX') }}" class="h-7 w-auto" width="132" height="28">
                @else
                    <x-logo class="text-[1.375rem] sm:text-2xl" />
                @endif
            </a>

            <nav class="ml-10 hidden h-full items-stretch gap-8 text-sm lg:flex" aria-label="Primary">
                <div class="relative flex items-stretch" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @focusout="if (! $el.contains($event.relatedTarget)) open = false">
                    <button type="button" @click="open = ! open" :aria-expanded="open.toString()" aria-controls="shop-menu"
                            class="flex items-center gap-1 border-b-2 {{ $shopActive ? 'border-ink-950 text-ink-950' : 'border-transparent text-ink-600 hover:text-ink-950' }}">
                        Shop <x-lx-icon name="chevron-down" class="size-3.5 transition-transform" ::class="open && 'rotate-180'" />
                    </button>
                    <div id="shop-menu" x-cloak x-show="open" x-transition.opacity.duration.150ms
                         class="absolute top-full left-0 w-[34rem] border border-line bg-white p-6 shadow-pop">
                        <p class="label-caps mb-3 text-ink-500">Categories</p>
                        <ul class="grid grid-cols-2 gap-x-6">
                            @foreach ($layout['menuCategories'] as $category)
                                <li>
                                    <a href="{{ route('categories.show', $category) }}" class="flex items-baseline justify-between gap-3 border-b border-line py-2.5 text-ink-950 hover:text-leather-700">
                                        <span>{{ $category->name }}</span>
                                        <span class="text-xs text-ink-500">{{ $category->active_products_count }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                        <a href="{{ route('shop') }}" class="link-arrow mt-5">All products <x-lx-icon name="arrow-right" class="size-4" /></a>
                    </div>
                </div>
                @foreach ($nav as [$label, $url, $active])
                    <a href="{{ $url }}" @if ($active) aria-current="page" @endif
                       class="flex items-center border-b-2 {{ $active ? 'border-ink-950 text-ink-950' : 'border-transparent text-ink-600 hover:text-ink-950' }}">{{ $label }}</a>
                @endforeach
            </nav>

            <div class="ml-auto flex items-center">
                <button type="button" class="inline-flex size-11 items-center justify-center text-ink-950 hover:text-leather-700"
                        @click="search = ! search; $nextTick(() => search && $refs.q.focus())" aria-controls="site-search" :aria-expanded="search.toString()" aria-label="Search products">
                    <x-lx-icon name="search" class="size-5" />
                </button>
                <a href="{{ auth()->check() ? route('account.dashboard') : route('login') }}" class="hidden size-11 items-center justify-center text-ink-950 hover:text-leather-700 sm:inline-flex"
                   aria-label="{{ auth()->check() ? 'My account' : 'Sign in' }}">
                    <x-lx-icon name="user" class="size-5" />
                </a>
                <livewire:cart-counter />
            </div>
        </div>
    </div>

    <div id="site-search" x-cloak x-show="search" x-transition.opacity.duration.150ms class="absolute inset-x-0 top-full border-b border-line bg-white shadow-pop">
        <form action="{{ route('shop') }}" method="GET" role="search" class="container-site flex items-center gap-3 py-4">
            <label for="header-q" class="sr-only">Search products</label>
            <x-lx-icon name="search" class="size-5 shrink-0 text-ink-500" />
            <input x-ref="q" id="header-q" type="search" name="q" value="{{ request('q') }}" placeholder="Search wallets, card holders, bags…" autocomplete="off"
                   class="min-h-11 w-full bg-transparent text-base text-ink-950 placeholder:text-ink-500 focus:outline-none">
            <button class="btn-primary btn-sm">Search</button>
        </form>
    </div>

    {{-- Mobile navigation drawer --}}
    <div id="mobile-menu" x-cloak x-show="menu" class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true" aria-label="Menu">
        <div class="absolute inset-0 bg-ink-950/50" @click="menu = false" x-show="menu" x-transition.opacity.duration.200ms></div>
        <div class="absolute inset-y-0 left-0 flex w-[min(22rem,88vw)] flex-col bg-white" x-show="menu" x-trap.noscroll.inert="menu"
             x-transition:enter="transition duration-300 ease-out-quart" x-transition:enter-start="-translate-x-full"
             x-transition:leave="transition duration-200 ease-out-quart" x-transition:leave-end="-translate-x-full">
            <div class="flex h-16 items-center justify-between border-b border-line px-5">
                <x-logo class="text-[1.375rem]" />
                <button type="button" @click="menu = false" class="-mr-2 inline-flex size-11 items-center justify-center" aria-label="Close menu">
                    <x-lx-icon name="x" class="size-6" />
                </button>
            </div>
            <nav class="flex-1 overflow-y-auto px-5 py-6" aria-label="Mobile">
                <p class="label-caps text-ink-500">Shop</p>
                <ul class="mt-2">
                    <li><a href="{{ route('shop') }}" class="flex min-h-12 items-center border-b border-line text-[15px] {{ request()->routeIs('shop') ? 'font-semibold' : '' }}">All products</a></li>
                    @foreach ($layout['menuCategories'] as $category)
                        <li>
                            <a href="{{ route('categories.show', $category) }}" class="flex min-h-12 items-center justify-between border-b border-line text-[15px]">
                                {{ $category->name }} <span class="text-xs text-ink-500">{{ $category->active_products_count }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
                <p class="label-caps mt-8 text-ink-500">Discover</p>
                <ul class="mt-2">
                    @foreach ([...$nav, ['Track an order', route('orders.track'), request()->routeIs('orders.track')], ['Warranty', route('warranty'), request()->routeIs('warranty')], ['Contact', route('contact'), request()->routeIs('contact')]] as [$label, $url, $active])
                        <li><a href="{{ $url }}" @if ($active) aria-current="page" @endif class="flex min-h-12 items-center border-b border-line text-[15px] {{ $active ? 'font-semibold' : '' }}">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </nav>
            <div class="border-t border-line p-5">
                @auth
                    <a href="{{ route('account.dashboard') }}" class="btn-secondary w-full">My account</a>
                @else
                    <a href="{{ route('login') }}" class="btn-primary w-full">Sign in or create account</a>
                @endauth
            </div>
        </div>
    </div>
</header>
