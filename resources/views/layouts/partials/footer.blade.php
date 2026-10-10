@php
    $zones = $layout['zones'];
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
@endphp

{{-- Service information: delivery, payment and exchanges — all read from admin settings --}}
<section aria-labelledby="service-info" class="border-t border-line bg-paper">
    <h2 id="service-info" class="sr-only">Delivery, payment and exchanges</h2>
    <div class="container-site grid gap-10 py-12 md:grid-cols-3 md:gap-8">
        @if ($zones->isNotEmpty())
            <div>
                <p class="flex items-center gap-2 text-sm font-semibold text-ink-950"><x-lx-icon name="truck" class="size-5 text-leather-600" /> Delivery</p>
                <ul class="mt-3 space-y-1.5 text-sm text-ink-600">
                    @foreach ($zones as $zone)
                        <li class="flex justify-between gap-4"><span>{{ $zone->name }} · {{ $zone->estimate() }}</span><span class="text-ink-950">{{ money($zone->charge) }}</span></li>
                    @endforeach
                </ul>
                <a href="{{ route('pages.delivery-policy') }}" class="mt-3 inline-block text-[13px] text-leather-700 underline underline-offset-4">Delivery policy</a>
            </div>
        @endif
        @if ($layout['paymentMethods'])
            <div>
                <p class="flex items-center gap-2 text-sm font-semibold text-ink-950"><x-lx-icon name="cash" class="size-5 text-leather-600" /> Payment</p>
                <p class="mt-3 text-sm text-ink-600">{{ implode(' · ', $layout['paymentMethods']) }}</p>
                <p class="mt-1.5 text-sm text-ink-600">We call to confirm every order before it is dispatched.</p>
            </div>
        @endif
        <div>
            <p class="flex items-center gap-2 text-sm font-semibold text-ink-950"><x-lx-icon name="refresh" class="size-5 text-leather-600" /> Exchanges & warranty</p>
            <p class="mt-3 text-sm text-ink-600">Exchange conditions, warranty cover and how to make a claim are set out in our policies.</p>
            <p class="mt-3 flex gap-4 text-[13px]">
                <a href="{{ route('pages.return-policy') }}" class="text-leather-700 underline underline-offset-4">Return policy</a>
                <a href="{{ route('warranty') }}" class="text-leather-700 underline underline-offset-4">Warranty</a>
            </p>
        </div>
    </div>
</section>

<footer class="bg-ink-950 text-white/75">
    <div class="container-site grid gap-12 py-14 lg:grid-cols-12 lg:gap-8 lg:py-16">
        <div class="lg:col-span-4">
            <x-logo inverse height="h-12" />
            @if ($about = setting('footer_about'))
                <p class="mt-5 max-w-sm text-sm leading-relaxed">{{ $about }}</p>
            @endif
            @if ($socials)
                <ul class="mt-6 flex gap-2" aria-label="Social media">
                    @foreach ($socials as $network => $url)
                        <li>
                            <a href="{{ $url }}" target="_blank" rel="noopener" class="inline-flex size-10 items-center justify-center rounded-xs border border-white/15 text-white/80 hover:border-white hover:text-white">
                                <x-lx-icon :name="$network" class="size-4" /><span class="sr-only">{{ ucfirst($network) }} (opens in a new tab)</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <nav class="grid grid-cols-2 gap-8 sm:grid-cols-3 lg:col-span-8" aria-label="Footer">
            <div>
                <h2 class="label-caps font-sans text-white">Shop</h2>
                <ul class="mt-4 space-y-2.5 text-sm">
                    @foreach ($layout['menuCategories'] as $category)
                        <li><a href="{{ route('categories.show', $category) }}" class="hover:text-white hover:underline hover:underline-offset-4">{{ $category->name }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <h2 class="label-caps font-sans text-white">Services</h2>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="{{ route('tap') }}" class="hover:text-white hover:underline hover:underline-offset-4">LeatherX Tap NFC profile</a></li>
                    <li><a href="{{ route('engraving') }}" class="hover:text-white hover:underline hover:underline-offset-4">Laser engraving</a></li>
                    <li><a href="{{ route('corporate') }}" class="hover:text-white hover:underline hover:underline-offset-4">Corporate gifting</a></li>
                    <li><a href="{{ route('warranty') }}" class="hover:text-white hover:underline hover:underline-offset-4">Warranty registration</a></li>
                    <li><a href="{{ route('orders.track') }}" class="hover:text-white hover:underline hover:underline-offset-4">Track an order</a></li>
                </ul>
            </div>
            <div class="col-span-2 sm:col-span-1">
                <h2 class="label-caps font-sans text-white">Company</h2>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="{{ route('pages.about') }}" class="hover:text-white hover:underline hover:underline-offset-4">About LeatherX</a></li>
                    <li><a href="{{ route('reviews.index') }}" class="hover:text-white hover:underline hover:underline-offset-4">Customer reviews</a></li>
                    <li><a href="{{ route('blog.index') }}" class="hover:text-white hover:underline hover:underline-offset-4">Journal</a></li>
                    <li><a href="{{ route('faq') }}" class="hover:text-white hover:underline hover:underline-offset-4">FAQ</a></li>
                    <li><a href="{{ route('contact') }}" class="hover:text-white hover:underline hover:underline-offset-4">Contact</a></li>
                </ul>
                @if ($contact)
                    <address class="mt-6 space-y-1.5 text-sm not-italic">
                        @isset($contact['phone'])<a href="tel:{{ preg_replace('/[^0-9+]/', '', $contact['phone']) }}" class="block hover:text-white">{{ $contact['phone'] }}</a>@endisset
                        @isset($contact['email'])<a href="mailto:{{ $contact['email'] }}" class="block hover:text-white">{{ $contact['email'] }}</a>@endisset
                        @isset($contact['address'])<span class="block">{{ $contact['address'] }}</span>@endisset
                        @isset($contact['hours'])<span class="block text-white/60">{{ $contact['hours'] }}</span>@endisset
                    </address>
                @endif
            </div>
        </nav>
    </div>

    <div class="border-t border-white/10">
        <div class="container-site flex flex-col gap-3 py-6 text-[13px] text-white/60 sm:flex-row sm:items-center sm:justify-between">
            <p>{{ setting('copyright_text') ?: '© '.date('Y').' '.setting('site_name', 'LeatherX').'. All rights reserved.' }}</p>
            <ul class="flex flex-wrap gap-x-5 gap-y-2">
                <li><a href="{{ route('pages.privacy-policy') }}" class="hover:text-white">Privacy policy</a></li>
                <li><a href="{{ route('pages.terms') }}" class="hover:text-white">Terms & conditions</a></li>
                <li><a href="{{ route('pages.return-policy') }}" class="hover:text-white">Returns</a></li>
                <li><a href="{{ route('sitemap') }}" class="hover:text-white">Sitemap</a></li>
            </ul>
        </div>
    </div>
</footer>

@if ($wa = setting('contact_whatsapp'))
    <a href="https://wa.me/{{ preg_replace('/\D/', '', $wa) }}" target="_blank" rel="noopener"
       class="fixed right-4 bottom-20 z-30 inline-flex size-12 lg:bottom-6 items-center justify-center rounded-full bg-[#1d7a46] text-white shadow-pop transition-transform hover:scale-105">
        <x-lx-icon name="chat" class="size-6" /><span class="sr-only">Chat with us on WhatsApp (opens in a new tab)</span>
    </a>
@endif
