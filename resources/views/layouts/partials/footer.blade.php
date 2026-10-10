@php
    $zones = $layout['zones'];
    $categories = $layout['menuCategories']->filter(fn ($category) => $category->active_products_count > 0);
    $contact = array_filter([
        'phone' => setting('contact_phone'),
        'email' => setting('contact_email'),
        'address' => setting('contact_address'),
        'hours' => setting('business_hours'),
    ]);
    $socials = array_filter([
        'facebook' => setting('social_facebook'),
        'instagram' => setting('social_instagram'),
        'youtube' => setting('social_youtube'),
        'linkedin' => setting('social_linkedin'),
    ]);
    $wa = setting('contact_whatsapp');
    // The best-sellers shortcut stays off pages where the visitor is already buying or managing an account.
    $showBestSellers = ! request()->routeIs('products.show', 'cart', 'checkout', 'orders.confirmation', 'orders.invoice', 'account.*', 'login', 'register', 'password.*');
    // Below lg every footer link is a 44px-tall touch target; from lg the lists return to their compact rhythm.
    $footerLink = 'flex w-fit min-h-11 items-center py-2.5 text-white/75 transition-colors hover:text-white lg:min-h-0 lg:py-0';
    $legalLink = 'inline-flex min-h-11 items-center transition-colors hover:text-white lg:min-h-0';
    $serviceLink = 'inline-flex min-h-11 items-center text-leather-700 underline underline-offset-4 hover:text-ink-950 lg:min-h-0';
@endphp

{{-- Service information: delivery, payment and exchanges — all read from admin settings --}}
<section aria-labelledby="service-info" class="border-t border-line bg-paper">
    <h2 id="service-info" class="sr-only">Delivery, payment and exchanges</h2>
    <div class="container-site grid gap-10 py-12 md:grid-cols-3 md:gap-8 lg:gap-12 lg:py-14">
        @if ($zones->isNotEmpty())
            <div>
                <h3 class="flex items-center gap-3 text-sm font-semibold tracking-normal text-ink-950">
                    <span class="grid size-9 shrink-0 place-items-center rounded-full bg-white ring-1 ring-line"><x-lx-icon name="truck" class="size-[18px] text-leather-700" /></span>
                    Delivery charges
                </h3>
                <ul class="mt-4 divide-y divide-line text-sm text-ink-600">
                    @foreach ($zones as $zone)
                        <li class="py-2.5 first:pt-0">
                            <span class="flex items-baseline justify-between gap-4">
                                <span class="text-ink-950">{{ $zone->name }}</span>
                                <span class="font-medium text-ink-950 tabular-nums">{{ money($zone->charge) }}</span>
                            </span>
                            <span class="mt-0.5 block text-[13px]">
                                {{ $zone->estimate() }}@if ((float) $zone->free_shipping_threshold > 0) · free over {{ money($zone->free_shipping_threshold) }}@endif
                            </span>
                        </li>
                    @endforeach
                </ul>
                <a href="{{ route('pages.delivery-policy') }}" class="{{ $serviceLink }} text-sm lg:mt-3">Delivery policy</a>
            </div>
        @endif
        @if ($layout['paymentMethods'])
            <div>
                <h3 class="flex items-center gap-3 text-sm font-semibold tracking-normal text-ink-950">
                    <span class="grid size-9 shrink-0 place-items-center rounded-full bg-white ring-1 ring-line"><x-lx-icon name="cash" class="size-[18px] text-leather-700" /></span>
                    Payment
                </h3>
                <ul class="mt-4 flex flex-wrap gap-2" aria-label="Payment methods">
                    @foreach ($layout['paymentMethods'] as $method)
                        <li class="rounded-full bg-white px-3 py-1 text-[13px] font-medium text-ink-950 ring-1 ring-line">{{ $method }}</li>
                    @endforeach
                </ul>
                <p class="mt-3 text-sm text-ink-600">We call to confirm every order before it is dispatched.</p>
            </div>
        @endif
        <div>
            <h3 class="flex items-center gap-3 text-sm font-semibold tracking-normal text-ink-950">
                <span class="grid size-9 shrink-0 place-items-center rounded-full bg-white ring-1 ring-line"><x-lx-icon name="refresh" class="size-[18px] text-leather-700" /></span>
                Exchanges &amp; warranty
            </h3>
            <p class="mt-4 text-sm text-ink-600">Exchange conditions, warranty cover and how to make a claim are set out in our policies.</p>
            <p class="flex flex-wrap gap-x-5 text-sm lg:mt-3 lg:gap-y-2">
                <a href="{{ route('pages.return-policy') }}" class="{{ $serviceLink }}">Return policy</a>
                <a href="{{ route('warranty') }}" class="{{ $serviceLink }}">Warranty</a>
            </p>
        </div>
    </div>
</section>

<footer class="bg-ink-950 text-white/75">
    <div class="container-site grid gap-12 py-14 lg:grid-cols-12 lg:gap-8 lg:py-20">
        <div class="lg:col-span-4">
            <a href="{{ route('home') }}" class="inline-block">
                <x-logo inverse height="h-12" />
            </a>
            @if ($about = setting('footer_about'))
                <p class="mt-5 max-w-sm text-sm leading-relaxed wrap-break-word text-white/65">{{ $about }}</p>
            @endif
            @if ($contact)
                {{-- Rows are 44px tall below lg (links are tap targets); the plain-text rows match that rhythm with py-3 --}}
                <address class="mt-3 max-w-sm text-sm not-italic lg:mt-6 lg:space-y-2.5">
                    @isset($contact['phone'])
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $contact['phone']) }}" class="{{ $footerLink }} gap-3"><x-lx-icon name="phone" class="size-4 shrink-0 text-brass-300" />{{ $contact['phone'] }}</a>
                    @endisset
                    @isset($contact['email'])
                        <a href="mailto:{{ $contact['email'] }}" class="{{ $footerLink }} gap-3 break-all"><x-lx-icon name="mail" class="size-4 shrink-0 text-brass-300" />{{ $contact['email'] }}</a>
                    @endisset
                    @isset($contact['address'])
                        <span class="flex items-start gap-3 py-3 wrap-break-word lg:py-0"><x-lx-icon name="map-pin" class="mt-0.5 size-4 shrink-0 text-brass-300" />{{ $contact['address'] }}</span>
                    @endisset
                    @isset($contact['hours'])
                        <span class="flex items-start gap-3 py-3 text-white/55 lg:py-0"><x-lx-icon name="clock" class="mt-0.5 size-4 shrink-0 text-brass-300" />{{ $contact['hours'] }}</span>
                    @endisset
                </address>
            @endif
            @if ($socials)
                <ul class="{{ $contact ? 'mt-4 lg:mt-7' : 'mt-7' }} flex flex-wrap gap-2.5" aria-label="Social media">
                    @foreach ($socials as $network => $url)
                        <li>
                            {{-- 40px circle; the ::before pad extends the tap area to 44px --}}
                            <a href="{{ $url }}" target="_blank" rel="noopener"
                               class="relative grid size-10 place-items-center rounded-full text-white/80 ring-1 ring-white/15 transition before:absolute before:-inset-0.5 hover:text-white hover:ring-white">
                                <x-lx-icon :name="$network" class="size-4" /><span class="sr-only">{{ ucfirst($network) }} (opens in a new tab)</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <nav class="grid grid-cols-2 gap-x-6 gap-y-7 wrap-break-word sm:grid-cols-3 lg:col-span-7 lg:col-start-6" aria-label="Footer">
            <div>
                <h2 class="text-[11px] font-semibold tracking-[0.16em] text-white/50 uppercase">Shop</h2>
                <ul class="mt-2 text-sm lg:mt-5 lg:space-y-3">
                    <li><a href="{{ route('shop') }}" class="{{ $footerLink }}">All products</a></li>
                    <li><a href="{{ route('shop', ['sort' => 'popular']) }}" class="{{ $footerLink }}">Best sellers</a></li>
                    @foreach ($categories as $category)
                        <li><a href="{{ route('categories.show', $category) }}" class="{{ $footerLink }}">{{ $category->name }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <h2 class="text-[11px] font-semibold tracking-[0.16em] text-white/50 uppercase">Services</h2>
                <ul class="mt-2 text-sm lg:mt-5 lg:space-y-3">
                    <li><a href="{{ route('tap') }}" class="{{ $footerLink }}">LeatherX Tap NFC profile</a></li>
                    <li><a href="{{ route('engraving') }}" class="{{ $footerLink }}">Laser engraving</a></li>
                    <li><a href="{{ route('corporate') }}" class="{{ $footerLink }}">Corporate gifting</a></li>
                    <li><a href="{{ route('warranty') }}" class="{{ $footerLink }}">Warranty registration</a></li>
                    <li><a href="{{ route('orders.track') }}" class="{{ $footerLink }}">Track an order</a></li>
                </ul>
            </div>
            <div>
                <h2 class="text-[11px] font-semibold tracking-[0.16em] text-white/50 uppercase">Company</h2>
                <ul class="mt-2 text-sm lg:mt-5 lg:space-y-3">
                    <li><a href="{{ route('pages.about') }}" class="{{ $footerLink }}">About LeatherX</a></li>
                    <li><a href="{{ route('reviews.index') }}" class="{{ $footerLink }}">Customer reviews</a></li>
                    <li><a href="{{ route('blog.index') }}" class="{{ $footerLink }}">Journal</a></li>
                    <li><a href="{{ route('faq') }}" class="{{ $footerLink }}">FAQ</a></li>
                    <li><a href="{{ route('contact') }}" class="{{ $footerLink }}">Contact</a></li>
                </ul>
            </div>
        </nav>
    </div>

    <div id="footer-bottom" class="border-t border-white/10">
        <div class="container-site">
            {{--
                Right padding keeps the legal links clear of the fixed WhatsApp button. Below lg the links are 44px
                touch targets, so the bar's own padding shrinks to keep the same visual spacing.
            --}}
            <div class="flex flex-col pt-6 pb-3 text-[13px] text-white/55 sm:flex-row sm:items-center sm:justify-between sm:gap-3 sm:py-3 lg:py-6 {{ $wa ? 'pr-16' : '' }}">
                <p>{{ setting('copyright_text') ?: '© '.date('Y').' '.setting('site_name', 'LeatherX').'. All rights reserved.' }}</p>
                <ul class="flex flex-wrap gap-x-5 lg:gap-y-2" aria-label="Legal">
                    <li><a href="{{ route('pages.privacy-policy') }}" class="{{ $legalLink }}">Privacy policy</a></li>
                    <li><a href="{{ route('pages.terms') }}" class="{{ $legalLink }}">Terms &amp; conditions</a></li>
                    <li><a href="{{ route('pages.return-policy') }}" class="{{ $legalLink }}">Returns</a></li>
                    <li><a href="{{ route('sitemap') }}" class="{{ $legalLink }}">Sitemap</a></li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Floating controls live inside the footer landmark so no page content sits outside a landmark --}}
    @if ($showBestSellers)
        {{--
            Floating "Shop best sellers" shortcut (Ventro-style). Appears after 600px of scrolling, steps aside once the
            footer's legal bar is on screen so it never covers those links, and remembers a dismissal per browser.
            With a WhatsApp button it sits bottom-left on small screens and left of the button on large ones.
        --}}
        <div x-data="{
                 shown: false,
                 dismissed: false,
                 frame: null,
                 init() {
                     try { this.dismissed = localStorage.getItem('lx-bestsellers-dismissed') === '1' } catch (e) {}
                     this.update()
                 },
                 {{-- One check per animation frame: unlike .throttle, the final scroll position is never skipped --}}
                 queue() {
                     if (this.frame) return
                     this.frame = requestAnimationFrame(() => { this.frame = null; this.update() })
                 },
                 update() {
                     const bar = document.getElementById('footer-bottom')
                     const atFooter = bar ? bar.getBoundingClientRect().top < window.innerHeight : false
                     this.shown = ! this.dismissed && window.scrollY > 600 && ! atFooter
                 },
                 dismiss() {
                     this.dismissed = true
                     this.shown = false
                     try { localStorage.setItem('lx-bestsellers-dismissed', '1') } catch (e) {}
                 },
             }"
             @scroll.window.passive="queue()" @resize.window="queue()"
             x-cloak x-show="shown"
             x-transition:enter="transition duration-300 ease-out-quart" x-transition:enter-start="translate-y-3 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
             x-transition:leave="transition duration-200 ease-out-quart" x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="translate-y-3 opacity-0"
             class="fixed bottom-4 z-30 print:hidden lg:bottom-6 {{ $wa ? 'left-4 lg:right-24 lg:left-auto' : 'right-4 lg:right-6' }}">
            <a href="{{ route('shop', ['sort' => 'popular']) }}"
               class="group flex items-center gap-3 rounded-full bg-white py-1.5 pr-10 pl-1.5 text-ink-950 shadow-pop ring-1 ring-line transition hover:ring-line-strong">
                <span class="grid size-11 shrink-0 place-items-center rounded-full bg-ink-950 text-white transition-transform group-hover:scale-105">
                    <x-lx-icon name="sparkles" class="size-5" />
                </span>
                <span class="pr-2 text-[13px] font-medium whitespace-nowrap">Shop best sellers</span>
            </a>
            {{-- Sibling of the link (never nested in it); the ::before pad gives a 44px tap area --}}
            <button type="button" @click="dismiss()" aria-label="Hide best sellers shortcut"
                    class="absolute top-1/2 right-1.5 grid size-8 -translate-y-1/2 place-items-center rounded-full text-ink-500 transition-colors before:absolute before:-inset-1.5 hover:bg-paper hover:text-ink-950">
                <x-lx-icon name="x" class="size-4" />
            </button>
        </div>
    @endif

    @if ($wa)
        <a href="https://wa.me/{{ preg_replace('/\D/', '', $wa) }}" target="_blank" rel="noopener"
           class="fixed right-4 bottom-20 z-30 inline-flex size-12 items-center justify-center rounded-full bg-[#1d7a46] text-white shadow-pop transition-transform hover:scale-105 print:hidden lg:bottom-6">
            <x-lx-icon name="chat" class="size-6" /><span class="sr-only">Chat with us on WhatsApp (opens in a new tab)</span>
        </a>
    @endif
</footer>
