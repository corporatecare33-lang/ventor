@php
    $announcementText = $layout['announcement']->title ?? (setting('announcement_enabled') ? setting('announcement_text') : null);
    $announcementLink = $layout['announcement']->button_link ?? setting('announcement_link');
    // Plain links after the mega-menu groups (Wallets, Bags, Smart Accessories — see App\Support\MegaMenu).
    $nav = [
        ['Engraving', route('engraving'), request()->routeIs('engraving')],
        ['LeatherX Tap™', route('tap'), request()->routeIs('tap')],
        ['Corporate Gifts', route('corporate'), request()->routeIs('corporate')],
        ['Contact', route('contact'), request()->routeIs('contact')],
        ['About', route('pages.about'), request()->routeIs('pages.about')],
    ];
    $pill = 'inline-flex min-h-11 items-center gap-1 rounded-full px-3.5 whitespace-nowrap transition-colors xl:px-4';
@endphp
@if ($announcementText)
    <aside class="bg-ink-950 text-white/85" aria-label="Store announcement">
        <p class="container-site py-2.5 text-center text-[12.5px] leading-snug italic">
            @if ($announcementLink)
                <a href="{{ $announcementLink }}" class="underline-offset-4 hover:text-white hover:underline">{{ $announcementText }}</a>
            @else
                {{ $announcementText }}
            @endif
        </p>
    </aside>
@endif
<header class="sticky top-0 z-40 {{ $announcementText ? 'bg-ink-950' : 'bg-white' }}"
        x-data="{
            menu: false, search: false, scrolled: false, mega: null, megaTimer: null,
            // Hover intent: a short delay before the first panel opens; switching between items is instant.
            openMega(key) { clearTimeout(this.megaTimer); this.megaTimer = setTimeout(() => { this.mega = key; this.search = false }, this.mega ? 0 : 90) },
            closeMega(key, delay = 180) { clearTimeout(this.megaTimer); this.megaTimer = setTimeout(() => { if (this.mega === key) this.mega = null }, delay) },
        }"
        @scroll.window.throttle.100ms="scrolled = window.scrollY > 40"
        @keydown.escape.window="menu = false; search = false; mega = null">
    <div class="border-b border-line bg-white transition-[border-radius] duration-200 {{ $announcementText ? 'rounded-t-[1.25rem]' : '' }}" :class="scrolled && 'rounded-t-none'">
        {{-- Utility row: menu + search · logo · account + cart --}}
        <div class="container-wide grid h-16 grid-cols-[1fr_auto_1fr] items-center lg:h-[4.75rem]">
            <div class="-ml-2.5 flex items-center">
                <button type="button" class="inline-flex size-11 items-center justify-center text-ink-950 hover:text-leather-700" @click="menu = true"
                        aria-controls="mobile-menu" :aria-expanded="menu.toString()" aria-label="Open menu">
                    <x-lx-icon name="menu" class="size-6" />
                </button>
                <button type="button" class="inline-flex size-11 items-center justify-center text-ink-950 hover:text-leather-700"
                        @click="search = ! search; mega = null; $nextTick(() => search && $refs.q.focus())" aria-controls="site-search" :aria-expanded="search.toString()" aria-label="Search products">
                    <x-lx-icon name="search" class="size-5" />
                </button>
            </div>

            <a href="{{ route('home') }}" class="justify-self-center" @if (request()->routeIs('home')) aria-current="page" @endif>
                <x-logo height="h-[17px] sm:h-5 lg:h-[22px]" />
            </a>

            <div class="-mr-2.5 flex items-center justify-self-end">
                <a href="{{ auth()->check() ? route('account.dashboard') : route('login') }}" class="hidden size-11 items-center justify-center text-ink-950 hover:text-leather-700 sm:inline-flex"
                   aria-label="{{ auth()->check() ? 'My account' : 'Sign in' }}">
                    <x-lx-icon name="user" class="size-5" />
                </a>
                <livewire:cart-counter />
            </div>
        </div>

        {{--
            Primary navigation (desktop). Wallets, Bags and Smart Accessories open a full-width
            panel (Ventro-style): collection names on the left — hovering or focusing one switches
            the product row on the right. Product rows are fetched once, on first open.
        --}}
        <nav class="hidden lg:block" aria-label="Primary" @click.outside="mega = null">
            <ul class="container-wide flex h-14 items-center justify-center gap-0.5 text-[14px] xl:gap-1.5 xl:text-[15px]">
                @foreach ($layout['megaMenu'] as $key => $group)
                    @php($groupActive = $group['categories']->contains(fn ($category) => request()->is('category/'.$category->slug)))
                    <li class="flex" @mouseenter="openMega(@js($key))" @mouseleave="closeMega(@js($key))"
                        @focusout="if (! $el.contains($event.relatedTarget)) closeMega(@js($key), 0)"
                        @keydown.escape.stop="mega = null; $refs.trigger.focus()">
                        <button type="button" x-ref="trigger" @click="mega = mega === @js($key) ? null : @js($key)"
                                :aria-expanded="(mega === @js($key)).toString()" aria-expanded="false" aria-controls="mega-{{ $key }}"
                                class="{{ $pill }} {{ $groupActive ? 'bg-mist-200 text-ink-950' : 'text-ink-800 hover:bg-mist-200 hover:text-ink-950' }}"
                                :class="mega === @js($key) && '!bg-ink-950 !text-white'">
                            {{ $group['label'] }}
                        </button>

                        <div id="mega-{{ $key }}" x-cloak x-show="mega === @js($key)"
                             x-transition:enter="transition duration-150 ease-out-quart" x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:leave="transition duration-100" x-transition:leave-end="opacity-0"
                             class="absolute inset-x-0 top-full max-h-[calc(100svh-10rem)] overflow-y-auto border-t border-line bg-white shadow-pop"
                             x-data="{
                                active: @js($group['categories']->first()->slug), html: '', loading: false, loaded: false, failed: false,
                                load() {
                                    if (this.loaded || this.loading) return;
                                    this.loading = true; this.failed = false;
                                    fetch(@js(route('menu.group', $key, false)), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                                        .then((response) => response.ok ? response.text() : Promise.reject(response.status))
                                        .then((html) => { this.html = html; this.loaded = true })
                                        .catch(() => { this.failed = true })
                                        .finally(() => { this.loading = false });
                                },
                             }"
                             x-effect="if (mega === @js($key)) load()"
                             x-init="$watch('mega', (value) => value === @js($key) && window.lxMotion?.menuPanel($el))">
                            <div class="container-wide grid grid-cols-[minmax(13rem,17rem)_minmax(0,1fr)] gap-10 py-9 xl:grid-cols-[minmax(15rem,22rem)_minmax(0,1fr)] xl:gap-14">
                                <div>
                                    <p class="text-[15px] font-medium text-ink-950">Collections</p>
                                    <ul class="mt-5 space-y-2.5">
                                        @foreach ($group['categories'] as $category)
                                            <li>
                                                <a href="{{ route('categories.show', $category) }}"
                                                   @mouseenter="active = @js($category->slug)" @focus="active = @js($category->slug)"
                                                   class="block text-[clamp(1.375rem,0.9rem+0.9vw,1.875rem)] leading-[1.12] font-bold tracking-[-0.03em] text-pretty transition-colors"
                                                   :class="active === @js($category->slug) ? 'text-ink-950' : 'text-ink-500 hover:text-ink-950'">{{ $category->name }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                    <a href="{{ route('shop') }}" class="link-arrow mt-7 min-h-11">Shop all products <x-lx-icon name="arrow-right" class="size-4" /></a>
                                </div>

                                <div class="min-w-0">
                                    {{-- Loading: the row's shape, so the panel doesn't jump when products arrive --}}
                                    <div x-show="! loaded && ! failed" aria-hidden="true">
                                        <div class="flex items-center justify-between"><div class="skeleton h-3 w-24"></div><div class="skeleton h-5 w-44"></div></div>
                                        <div class="mt-6 flex gap-4 overflow-hidden">
                                            @for ($i = 0; $i < 4; $i++)
                                                <div class="w-[15rem] shrink-0 xl:w-[16.5rem]"><div class="skeleton aspect-square w-full rounded-card"></div><div class="skeleton mt-4 h-4 w-3/4"></div><div class="skeleton mt-2 h-4 w-1/3"></div></div>
                                            @endfor
                                        </div>
                                    </div>
                                    <p x-show="failed" x-cloak class="text-[15px] text-ink-600">
                                        We couldn't load the products just now.
                                        <button type="button" class="font-medium text-leather-700 underline underline-offset-4" @click="load()">Try again</button>
                                        or open a collection on the left.
                                    </p>
                                    <div x-html="html"></div>
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
                @foreach ($nav as [$label, $url, $active])
                    <li class="flex">
                        <a href="{{ $url }}" @if ($active) aria-current="page" @endif
                           class="{{ $pill }} {{ $active ? 'bg-mist-200 text-ink-950' : 'text-ink-800 hover:bg-mist-200 hover:text-ink-950' }}">{{ $label }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>
    </div>

    <div id="site-search" x-cloak x-show="search" x-transition.opacity.duration.150ms class="absolute inset-x-0 top-full border-b border-line bg-white shadow-pop">
        <form action="{{ route('shop') }}" method="GET" role="search" class="container-wide flex items-center gap-3 py-4">
            <label for="header-q" class="sr-only">Search products</label>
            <x-lx-icon name="search" class="size-5 shrink-0 text-ink-500" />
            <input x-ref="q" id="header-q" type="search" name="q" value="{{ request('q') }}" placeholder="Search wallets, card holders, bags…" autocomplete="off"
                   class="min-h-11 w-full bg-transparent text-base text-ink-950 placeholder:text-ink-500 focus:outline-none">
            <button class="btn-primary btn-sm">Search</button>
        </form>
    </div>

    {{-- Mobile navigation drawer --}}
    <div id="mobile-menu" x-cloak x-show="menu" class="fixed inset-0 z-50" role="dialog" aria-modal="true" aria-label="Menu">
        <div class="absolute inset-0 bg-ink-950/50" @click="menu = false" x-show="menu" x-transition.opacity.duration.200ms></div>
        <div class="absolute inset-y-0 left-0 flex w-[min(22rem,88vw)] flex-col bg-white" x-show="menu" x-trap.noscroll.inert="menu"
             x-transition:enter="transition duration-300 ease-out-quart" x-transition:enter-start="-translate-x-full"
             x-transition:leave="transition duration-200 ease-out-quart" x-transition:leave-end="-translate-x-full">
            <div class="flex h-16 items-center justify-between border-b border-line px-5">
                <x-logo height="h-[17px]" />
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
                    @foreach ([...$nav, ['Track an order', route('orders.track'), request()->routeIs('orders.track')], ['Warranty registration', route('warranty'), request()->routeIs('warranty')], ['Journal', route('blog.index'), request()->routeIs('blog.*')]] as [$label, $url, $active])
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
