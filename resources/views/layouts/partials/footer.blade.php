<footer class="mt-auto bg-ink-950 text-white/70">
    <div class="container-lx grid gap-10 py-14 sm:grid-cols-2 lg:grid-cols-5">
        <div class="lg:col-span-2">
            <span class="font-display text-2xl font-semibold tracking-[0.18em] text-white">LEATHER<span class="text-gold-400">X</span></span>
            <p class="mt-4 max-w-sm text-sm leading-6">{{ setting('footer_about') }}</p>
            <div class="mt-6 flex gap-3">
                @foreach (['facebook' => 'social_facebook', 'instagram' => 'social_instagram', 'youtube' => 'social_youtube', 'linkedin' => 'social_linkedin'] as $icon => $key)
                    @if ($url = setting($key))
                        <a href="{{ $url }}" target="_blank" rel="noopener" class="grid size-10 place-items-center rounded-full border border-white/10 hover:border-gold-400 hover:text-gold-300" aria-label="{{ ucfirst($icon) }}">
                            <x-lx-icon :name="$icon" class="size-4" />
                        </a>
                    @endif
                @endforeach
            </div>
        </div>

        <div>
            <h2 class="mb-4 font-sans text-xs font-semibold tracking-[0.2em] text-white uppercase">Shop</h2>
            <ul class="space-y-2.5 text-sm">
                @foreach ($layout['menuCategories']->take(6) as $category)
                    <li><a href="{{ route('categories.show', $category) }}" class="hover:text-gold-300">{{ $category->name }}</a></li>
                @endforeach
            </ul>
        </div>

        <div>
            <h2 class="mb-4 font-sans text-xs font-semibold tracking-[0.2em] text-white uppercase">Help</h2>
            <ul class="space-y-2.5 text-sm">
                <li><a href="{{ route('orders.track') }}" class="hover:text-gold-300">Track your order</a></li>
                <li><a href="{{ route('warranty') }}" class="hover:text-gold-300">Warranty & claims</a></li>
                <li><a href="{{ route('faq') }}" class="hover:text-gold-300">FAQ</a></li>
                <li><a href="{{ route('pages.delivery-policy') }}" class="hover:text-gold-300">Delivery policy</a></li>
                <li><a href="{{ route('pages.return-policy') }}" class="hover:text-gold-300">Returns & exchange</a></li>
                <li><a href="{{ route('contact') }}" class="hover:text-gold-300">Contact us</a></li>
            </ul>
        </div>

        <div>
            <h2 class="mb-4 font-sans text-xs font-semibold tracking-[0.2em] text-white uppercase">Contact</h2>
            <ul class="space-y-3 text-sm">
                @if ($phone = setting('contact_phone'))
                    <li class="flex gap-2"><x-lx-icon name="phone" class="mt-0.5 size-4 shrink-0 text-gold-400" /><a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" class="hover:text-gold-300">{{ $phone }}</a></li>
                @endif
                @if ($email = setting('contact_email'))
                    <li class="flex gap-2"><x-lx-icon name="mail" class="mt-0.5 size-4 shrink-0 text-gold-400" /><a href="mailto:{{ $email }}" class="hover:text-gold-300">{{ $email }}</a></li>
                @endif
                @if ($address = setting('contact_address'))
                    <li class="flex gap-2"><x-lx-icon name="map-pin" class="mt-0.5 size-4 shrink-0 text-gold-400" />{{ $address }}</li>
                @endif
                @if ($hours = setting('business_hours'))
                    <li class="flex gap-2"><x-lx-icon name="clock" class="mt-0.5 size-4 shrink-0 text-gold-400" />{{ $hours }}</li>
                @endif
            </ul>
            <div class="mt-5 flex flex-wrap gap-2 text-[11px] font-medium">
                @foreach (['Cash on Delivery', 'bKash', 'Nagad'] as $method)
                    <span class="rounded-md border border-white/10 px-2.5 py-1">{{ $method }}</span>
                @endforeach
            </div>
        </div>
    </div>

    <div class="border-t border-white/10">
        <div class="container-lx flex flex-col items-center justify-between gap-3 py-6 text-xs sm:flex-row">
            <p>{{ setting('copyright_text', '© '.date('Y').' LeatherX') }}</p>
            <nav class="flex flex-wrap justify-center gap-x-5 gap-y-2" aria-label="Legal">
                <a href="{{ route('pages.about') }}" class="hover:text-gold-300">About</a>
                <a href="{{ route('blog.index') }}" class="hover:text-gold-300">Journal</a>
                <a href="{{ route('pages.privacy-policy') }}" class="hover:text-gold-300">Privacy</a>
                <a href="{{ route('pages.terms') }}" class="hover:text-gold-300">Terms</a>
            </nav>
        </div>
    </div>
</footer>

@if ($wa = setting('contact_whatsapp'))
    <a href="https://wa.me/{{ preg_replace('/\D/', '', $wa) }}" target="_blank" rel="noopener" class="fixed right-4 bottom-4 z-30 grid size-12 place-items-center rounded-full bg-[#1f9d55] text-white shadow-lg shadow-black/20 transition hover:scale-105" aria-label="Chat on WhatsApp">
        <x-lx-icon name="chat" class="size-6" />
    </a>
@endif
