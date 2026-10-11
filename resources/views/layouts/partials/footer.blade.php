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
    // Shop assistant launcher (bottom-right corner); can be switched off in Website settings → Store.
    $assistant = ! setting('assistant_hidden');
    // Below lg every footer link is a 44px-tall touch target; from lg the lists return to their compact rhythm.
    $footerLink = 'flex w-fit min-h-11 items-center py-2.5 text-white/75 transition-colors hover:text-white lg:min-h-0 lg:py-0';
    $legalLink = 'inline-flex min-h-11 items-center transition-colors hover:text-white lg:min-h-0';
    // Service cards: mist panels (the homepage's card style) whose links sit on the bottom edge, so the row lines up.
    $hasPayment = (bool) $layout['paymentMethods'];
    $serviceCards = 1 + (int) $zones->isNotEmpty() + (int) $hasPayment;
    $serviceCard = 'flex min-w-0 flex-col rounded-card bg-mist p-5 ring-1 ring-line lg:p-6';
    $serviceIcon = 'grid size-10 shrink-0 place-items-center rounded-full bg-ink-950 text-white';
    $serviceLink = 'group flex min-h-11 items-center justify-between gap-3 rounded-card bg-white px-4 text-sm font-medium text-ink-950 ring-1 ring-line transition hover:ring-ink-950';
    $serviceArrow = 'size-4 shrink-0 text-ink-500 transition group-hover:text-ink-950 motion-safe:group-hover:translate-x-0.5';
@endphp

{{--
    Service information: delivery, payment and exchanges — all read from admin settings.
    With all three cards, delivery (the tallest) holds the left column from tablets up and the other two stack beside it.
--}}
<section aria-labelledby="service-info" class="border-t border-line bg-white">
    <h2 id="service-info" class="sr-only">Delivery, payment and exchanges</h2>
    <div data-anim="stagger" @class([
        'container-wide grid gap-3 py-8 lg:gap-4 lg:py-12',
        'md:grid-cols-2' => $serviceCards > 1,
    ])>
        @if ($zones->isNotEmpty())
            <div @class([$serviceCard, 'md:row-span-2' => $serviceCards === 3])>
                <h3 class="flex items-center gap-3 text-base leading-snug text-ink-950">
                    <span class="{{ $serviceIcon }}"><x-lx-icon name="truck" class="size-[18px]" /></span>
                    Delivery charges
                </h3>
                <ul class="relative mt-5 divide-y divide-line rounded-card bg-white ring-1 ring-line">
                    @foreach ($zones as $zone)
                        <li class="flex items-start justify-between gap-4 px-4 py-3">
                            <span class="min-w-0">
                                <span class="block text-sm font-medium text-ink-950">{{ $zone->name }}</span>
                                <span class="mt-1 flex flex-wrap items-center gap-x-2.5 gap-y-1.5 text-[12px] text-ink-500">
                                    <span class="inline-flex items-center gap-1">
                                        <x-lx-icon name="clock" class="size-3.5 shrink-0" /><span class="sr-only">Delivery in</span>{{ $zone->estimate() }}
                                    </span>
                                    @if ((float) $zone->free_shipping_threshold > 0)
                                        <span class="rounded-full bg-success-bg px-2 py-0.5 font-medium text-success">Free over {{ money($zone->free_shipping_threshold) }}</span>
                                    @endif
                                </span>
                            </span>
                            <span class="shrink-0 text-[15px] font-bold tracking-tight text-ink-950 tabular-nums">
                                {{ (float) $zone->charge > 0 ? money($zone->charge) : 'Free' }}
                            </span>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-auto pt-4">
                    <a href="{{ route('pages.delivery-policy') }}" class="{{ $serviceLink }}">
                        Delivery policy<x-lx-icon name="arrow-right" class="{{ $serviceArrow }}" />
                    </a>
                </div>
            </div>
        @endif
        @if ($hasPayment)
            <div class="{{ $serviceCard }}">
                <h3 class="flex items-center gap-3 text-base leading-snug text-ink-950">
                    <span class="{{ $serviceIcon }}"><x-lx-icon name="cash" class="size-[18px]" /></span>
                    Payment
                </h3>
                <ul class="mt-5 flex flex-wrap gap-2" aria-label="Payment methods">
                    @foreach ($layout['paymentMethods'] as $method)
                        <li class="inline-flex items-center gap-1.5 rounded-full bg-white py-1.5 pr-3.5 pl-2.5 text-[13px] font-medium text-ink-950 ring-1 ring-line">
                            <x-lx-icon name="check" class="size-3.5 shrink-0 stroke-2 text-success" />{{ $method }}
                        </li>
                    @endforeach
                </ul>
                <p class="mt-4 flex items-start gap-2.5 text-sm text-ink-600">
                    <x-lx-icon name="phone" class="mt-0.5 size-4 shrink-0 text-leather-700" />We call to confirm every order before it is dispatched.
                </p>
                @unless (request()->routeIs('orders.track*'))
                    <div class="mt-auto pt-4">
                        <a href="{{ route('orders.track') }}" class="{{ $serviceLink }}">
                            Track an order<x-lx-icon name="arrow-right" class="{{ $serviceArrow }}" />
                        </a>
                    </div>
                @endunless
            </div>
        @endif
        <div class="{{ $serviceCard }}">
            <h3 class="flex items-center gap-3 text-base leading-snug text-ink-950">
                <span class="{{ $serviceIcon }}"><x-lx-icon name="refresh" class="size-[18px]" /></span>
                Exchanges &amp; warranty
            </h3>
            <p class="mt-5 text-sm text-ink-600">Exchange conditions, warranty cover and how to make a claim are set out in our policies.</p>
            <ul class="mt-auto grid gap-2 pt-4 sm:grid-cols-2">
                <li>
                    <a href="{{ route('pages.return-policy') }}" class="{{ $serviceLink }}">
                        Return policy<x-lx-icon name="arrow-right" class="{{ $serviceArrow }}" />
                    </a>
                </li>
                <li>
                    <a href="{{ route('warranty') }}" class="{{ $serviceLink }}">
                        Warranty<x-lx-icon name="arrow-right" class="{{ $serviceArrow }}" />
                    </a>
                </li>
            </ul>
        </div>
    </div>
</section>

<footer class="bg-ink-950 text-white/75">
    {{-- Same frame as the header; the brand column sits beside the links from lg, above them below lg --}}
    <div class="container-wide grid gap-8 py-10 lg:grid-cols-12 lg:gap-12 lg:py-16">
        <div class="lg:col-span-3 xl:col-span-4">
            <a href="{{ route('home') }}" class="inline-block">
                <x-logo inverse height="h-6" />
            </a>
            @if ($about = setting('footer_about'))
                <p class="mt-4 max-w-sm text-sm leading-relaxed wrap-break-word text-white/65">{{ $about }}</p>
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

        {{--
            Four equal tracks from md (two on phones). The long Shop list takes two tracks and flows into two columns
            whose gap matches the grid gap, so all five link columns line up. On phones it is a 2-up grid instead.
        --}}
        <nav class="grid grid-cols-2 gap-x-6 gap-y-6 wrap-break-word md:grid-cols-4 lg:col-span-9 lg:gap-x-8 xl:col-span-8" aria-label="Footer">
            <div class="col-span-2">
                <h2 class="text-[11px] font-semibold tracking-[0.16em] text-white/50 uppercase">Shop</h2>
                <ul class="mt-2 grid grid-cols-2 gap-x-6 text-sm md:block md:columns-2 lg:mt-5 lg:gap-x-8 lg:space-y-3">
                    <li class="break-inside-avoid"><a href="{{ route('shop') }}" class="{{ $footerLink }}">All products</a></li>
                    <li class="break-inside-avoid"><a href="{{ route('shop', ['sort' => 'popular']) }}" class="{{ $footerLink }}">Best sellers</a></li>
                    @foreach ($categories as $category)
                        <li class="break-inside-avoid"><a href="{{ route('categories.show', $category) }}" class="{{ $footerLink }}">{{ $category->name }}</a></li>
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
        <div class="container-wide">
            {{--
                Right padding keeps the legal links clear of the fixed WhatsApp and assistant buttons. Below lg the links are 44px
                touch targets, so the bar's own padding shrinks to keep the same visual spacing.
            --}}
            <div class="grid grid-cols-1 items-center pt-6 pb-3 text-[13px] text-white/55 md:grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] md:gap-x-3 md:py-6 {{ $wa || $assistant ? 'pr-16' : '' }}">
                <p>{{ setting('copyright_text') ?: '© '.date('Y').' '.setting('site_name', 'LeatherX').'. All rights reserved.' }}</p>
                <p class="mt-1 w-full text-center md:col-start-2 md:row-start-1 md:mt-0 md:w-auto">
                    Designed and Developed By
                    <a href="https://digitalwebars.com/" target="_blank" rel="noopener" class="{{ $legalLink }} font-medium text-white/75">Digital Webars<span class="sr-only"> (opens in a new tab)</span></a>
                </p>
                <ul class="flex flex-wrap gap-x-5 md:col-start-3 md:row-start-1 md:justify-self-end lg:gap-y-2" aria-label="Legal">
                    <li><a href="{{ route('pages.privacy-policy') }}" class="{{ $legalLink }}">Privacy policy</a></li>
                    <li><a href="{{ route('pages.terms') }}" class="{{ $legalLink }}">Terms &amp; conditions</a></li>
                    <li><a href="{{ route('pages.return-policy') }}" class="{{ $legalLink }}">Returns</a></li>
                    <li><a href="{{ route('sitemap') }}" class="{{ $legalLink }}">Sitemap</a></li>
                </ul>
            </div>
        </div>
    </div>

    {{--
        Floating controls live inside the footer landmark so no page content sits outside a landmark. They float over
        any section, so they override the footer's brass focus ring with a ring drawn on their own surface: leather on
        the dark assistant button and white inset on the green WhatsApp button.
        The assistant launcher holds the bottom-right corner; WhatsApp stacks above it.
    --}}
    @if ($wa)
        <a href="https://wa.me/{{ preg_replace('/\D/', '', $wa) }}" target="_blank" rel="noopener"
           class="fixed right-4 z-30 inline-flex size-12 items-center justify-center rounded-full bg-[#1d7a46] text-white shadow-pop transition-transform hover:scale-105 focus-visible:outline-white focus-visible:-outline-offset-4 print:hidden {{ $assistant ? 'bottom-[calc(max(1rem,env(safe-area-inset-bottom))+4rem)] sm:bottom-20 lg:right-6 lg:bottom-22' : 'bottom-20 lg:bottom-6' }}">
            <x-lx-icon name="chat" class="size-6" /><span class="sr-only">Chat with us on WhatsApp (opens in a new tab)</span>
        </a>
    @endif

    @if ($assistant)
        <livewire:assistant-chat />
    @endif
</footer>
