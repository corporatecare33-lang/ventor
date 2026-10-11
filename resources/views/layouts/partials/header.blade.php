@php
    $announcementText = $layout['announcement']->title ?? (setting('announcement_enabled') ? setting('announcement_text') : null);
    $announcementLink = $layout['announcement']->button_link ?? setting('announcement_link');
    // Plain links after the mega-menu groups (Wallets, Bags, Smart Accessories — see App\Support\MegaMenu).
    $nav = [
        ['Engraving', route('engraving'), request()->routeIs('engraving')],
        ['LeatherX Tap™', route('tap'), request()->routeIs('tap')],
        ['Contact Us', route('contact'), request()->routeIs('contact')],
    ];
    // "About Us" opens a short dropdown, as on Ventro.
    $about = [
        ['Our story', route('pages.about'), request()->routeIs('pages.about')],
        ['Corporate gifts', route('corporate'), request()->routeIs('corporate')],
        ['Warranty registration', route('warranty'), request()->routeIs('warranty')],
    ];
    $aboutActive = collect($about)->contains(fn ($link) => $link[2]);
    $iconButton = 'inline-flex size-11 items-center justify-center text-ink-950 transition-colors hover:text-leather-700';
    $navItem = 'flex pb-(--header-pad)';
@endphp
{{-- Announcement bar (Ventro): 48px (56px from 1536px), italic 13–14px type on near-black --}}
@if ($announcementText)
    <aside id="announcement-bar" class="flex h-12 items-center bg-ink-900 text-[13px] text-white/95 2xl:h-14 2xl:text-sm" aria-label="Store announcement">
        <p class="container-site text-center leading-tight italic">
            @if ($announcementLink)
                <a href="{{ $announcementLink }}" class="underline-offset-4 hover:text-white hover:underline">{{ $announcementText }}</a>
            @else
                {{ $announcementText }}
            @endif
        </p>
    </aside>
@endif
{{--
    Header (Ventro): a white panel with rounded top corners over the announcement bar, edge to edge
    with 20/36/48px sides. 24px above and below on phones, 32px from tablets (--header-pad). The nav
    items carry the bottom padding, so a panel opens flush with no hover gap.
    Once it sticks (the announcement bar has scrolled away) the corners square off and a hairline
    appears. Its height never changes with scrolling: a shrinking sticky header moves the scroll
    position, which can flip the state back and leave it stuck.
--}}
<header class="group/header sticky top-0 z-40 [--header-pad:1.5rem] md:[--header-pad:2rem] {{ $announcementText ? 'bg-ink-900' : 'bg-white' }}"
        x-data="{
            menu: false, search: false, scrolled: false, mega: null, megaTimer: null, scrollFrame: null,
            // Hover intent: a short delay before the first panel opens; switching between items is instant.
            openMega(key) { clearTimeout(this.megaTimer); this.megaTimer = setTimeout(() => { this.mega = key; this.search = false }, this.mega ? 0 : 90) },
            closeMega(key, delay = 180) { clearTimeout(this.megaTimer); this.megaTimer = setTimeout(() => { if (this.mega === key) this.mega = null }, delay) },
            // Checked on the next frame, so the last scroll position always counts (a throttle can drop it).
            checkScroll() {
                if (this.scrollFrame) return;
                this.scrollFrame = requestAnimationFrame(() => {
                    this.scrollFrame = null;
                    this.scrolled = window.scrollY > 0 && window.scrollY >= (document.getElementById('announcement-bar')?.offsetHeight ?? 0);
                });
            },
        }"
        x-init="checkScroll()" :data-scrolled="scrolled"
        @scroll.window.passive="checkScroll()" @resize.window.passive="checkScroll()" @pageshow.window="checkScroll()"
        @keydown.escape.window="menu = false; search = false; mega = null">
    <div class="border-b border-transparent bg-white transition-[border-radius,border-color] duration-200 group-data-scrolled/header:rounded-t-none group-data-scrolled/header:border-line {{ $announcementText ? 'rounded-t-[clamp(1rem,1.578vw,1.875rem)]' : '' }}">
        {{-- Utility row: menu + search · logo · account + cart (phones: menu · logo · search + cart) --}}
        <div class="grid grid-cols-[1fr_auto_1fr] items-center px-5 pt-(--header-pad) pb-(--header-pad) md:px-9 lg:px-12 lg:pb-0">
            <div class="-ml-2.5 flex items-center gap-1.5">
                <button type="button" class="{{ $iconButton }}" @click="menu = true"
                        aria-controls="mobile-menu" :aria-expanded="menu.toString()" aria-label="Open menu">
                    <x-lx-icon name="menu" class="size-6" />
                </button>
                <button type="button" class="{{ $iconButton }} max-lg:hidden"
                        @click="search = ! search; mega = null; $nextTick(() => search && $refs.q.focus())" aria-controls="site-search" :aria-expanded="search.toString()" aria-label="Search products">
                    <x-lx-icon name="search" class="size-6" />
                </button>
            </div>

            <a href="{{ route('home') }}" class="flex items-center justify-self-center lg:h-12" @if (request()->routeIs('home')) aria-current="page" @endif>
                <x-logo height="h-6 sm:h-7 lg:h-8" />
            </a>

            <div class="-mr-2.5 flex items-center gap-1.5 justify-self-end">
                <button type="button" class="{{ $iconButton }} lg:hidden"
                        @click="search = ! search; mega = null; $nextTick(() => search && $refs.q.focus())" aria-controls="site-search" :aria-expanded="search.toString()" aria-label="Search products">
                    <x-lx-icon name="search" class="size-6" />
                </button>
                <a href="{{ auth()->check() ? route('account.dashboard') : route('login') }}" class="{{ $iconButton }} max-lg:hidden"
                   aria-label="{{ auth()->check() ? 'My account' : 'Sign in' }}">
                    <x-lx-icon name="account" class="size-6" />
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
            <ul class="flex items-start justify-center px-12 pt-4">
                @foreach ($layout['megaMenu'] as $key => $group)
                    @php($groupActive = $group['categories']->contains(fn ($category) => request()->is('category/'.$category->slug)))
                    <li class="{{ $navItem }}" @mouseenter="openMega(@js($key))" @mouseleave="closeMega(@js($key))"
                        @focusout="if (! $el.contains($event.relatedTarget)) closeMega(@js($key), 0)"
                        @keydown.escape.stop="mega = null; $refs.trigger.focus()">
                        <button type="button" x-ref="trigger" @click="mega = mega === @js($key) ? null : @js($key)"
                                :aria-expanded="(mega === @js($key)).toString()" aria-expanded="false" aria-controls="mega-{{ $key }}"
                                class="nav-pill {{ $groupActive ? 'bg-mist-200' : '' }}">
                            <span class="nav-pill__text">{{ $group['label'] }}</span><span class="nav-pill__fill" aria-hidden="true">{{ $group['label'] }}</span>
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
                    <li class="{{ $navItem }}">
                        <a href="{{ $url }}" @if ($active) aria-current="page" @endif class="nav-pill">
                            <span class="nav-pill__text">{{ $label }}</span><span class="nav-pill__fill" aria-hidden="true">{{ $label }}</span>
                        </a>
                    </li>
                @endforeach
                <li class="relative {{ $navItem }}" @mouseenter="openMega('about')" @mouseleave="closeMega('about')"
                    @focusout="if (! $el.contains($event.relatedTarget)) closeMega('about', 0)"
                    @keydown.escape.stop="mega = null; $refs.aboutTrigger.focus()">
                    <button type="button" x-ref="aboutTrigger" @click="mega = mega === 'about' ? null : 'about'"
                            :aria-expanded="(mega === 'about').toString()" aria-expanded="false" aria-controls="about-menu"
                            class="nav-pill {{ $aboutActive ? 'bg-mist-200' : '' }}">
                        <span class="nav-pill__text">About Us</span><span class="nav-pill__fill" aria-hidden="true">About Us</span>
                    </button>
                    <div id="about-menu" x-cloak x-show="mega === 'about'"
                         x-transition:enter="transition duration-150 ease-out-quart" x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:leave="transition duration-100" x-transition:leave-end="opacity-0"
                         class="absolute top-full -left-3 w-max min-w-[15.625rem] rounded-b-[clamp(1rem,1.052vw,1.25rem)] bg-white px-8 pt-6 pb-10 shadow-pop">
                        <ul class="flex flex-col gap-1.5 text-[clamp(0.875rem,0.8115rem+0.1587vw,1rem)] xl:gap-2">
                            @foreach ($about as [$label, $url, $active])
                                <li><a href="{{ $url }}" @if ($active) aria-current="page" @endif class="link-reveal leading-normal text-ink-950">{{ $label }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                </li>
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
                <x-logo height="h-6" />
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
                    @foreach ([...$nav, ...$about, ['Track an order', route('orders.track'), request()->routeIs('orders.track')], ['Journal', route('blog.index'), request()->routeIs('blog.*')]] as [$label, $url, $active])
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
