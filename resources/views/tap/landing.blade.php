@extends('layouts.app')

@section('content')
<x-ui.page-header eyebrow="LeatherX Tap" title="A contact card built into your wallet"
    intro="LeatherX Tap wallets carry a small passive NFC chip. Hold the wallet near a phone and your contact profile opens in the browser — no app, no battery."
    :crumbs="['LeatherX Tap' => null]">
    <div class="mt-8 flex flex-wrap gap-3">
        <a href="#tap-products" class="btn-primary">Shop Tap wallets</a>
        @auth<a href="{{ route('account.nfc.index') }}" class="btn-secondary">Manage my profile</a>@endauth
    </div>
</x-ui.page-header>

<section class="section-y" aria-labelledby="how-title">
    <div class="container-site grid gap-12 lg:grid-cols-12">
        <div class="lg:col-span-5">
            <x-ui.section-heading id="how-title" title="How it works" />
            <ol class="mt-8 border-t border-line">
                @foreach ($section->content['items'] ?? [] as $item)
                    <li class="flex gap-5 border-b border-line py-5">
                        <span class="font-display text-2xl leading-none text-leather-600" aria-hidden="true">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <div>
                            <h3 class="font-sans text-base font-medium text-ink-950">{{ $item['title'] }}</h3>
                            @if (! empty($item['text']))<p class="mt-1 text-[15px] leading-relaxed text-ink-600">{{ $item['text'] }}</p>@endif
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
        @if ($image = $section?->getFirstMedia('image'))
            <figure class="lg:col-span-6 lg:col-start-7">
                <div class="aspect-[4/3] overflow-hidden rounded-sm">
                    <x-ui.picture :media="$image" :conversions="['mobile' => 800, 'desktop' => 1400]" sizes="(min-width: 1024px) 45vw, 100vw" width="1400" height="1050" eager />
                </div>
            </figure>
        @endif
    </div>
</section>

<section class="border-y border-line bg-paper" aria-labelledby="know-title">
    <div class="container-site grid gap-10 py-14 lg:grid-cols-12 lg:py-20">
        <h2 id="know-title" class="text-h2 text-ink-950 lg:col-span-4">Good to know</h2>
        <dl class="grid gap-x-10 gap-y-8 sm:grid-cols-2 lg:col-span-8">
            @foreach ([
                ['Which phones work?', 'NFC-enabled Android phones and recent iPhones read the chip from the lock screen or with the NFC toggle on. Every profile also has a QR code for any phone with a camera.'],
                ['What is stored on the chip?', 'Only the web address of your profile — no payment or personal data. Your details live in your LeatherX account.'],
                ['Can I change my details?', 'Yes, at any time from your account. The chip keeps pointing to the same profile, so it never needs re-programming.'],
                ['Can I turn it off?', 'Pause or delete the profile from your account and the link stops showing your details.'],
            ] as [$q, $a])
                <div>
                    <dt class="font-medium text-ink-950">{{ $q }}</dt>
                    <dd class="mt-2 text-[15px] leading-relaxed text-ink-600">{{ $a }}</dd>
                </div>
            @endforeach
        </dl>
    </div>
</section>

@if ($products->isNotEmpty())
    <section id="tap-products" class="section-y scroll-mt-24" aria-labelledby="tap-products-title">
        <div class="container-site">
            <x-ui.section-heading id="tap-products-title" title="LeatherX Tap wallets" intro="Every Tap product includes a personal contact profile." />
            <div class="mt-10 grid grid-cols-2 gap-x-4 gap-y-10 sm:gap-x-6 lg:grid-cols-4">
                @foreach ($products as $product)
                    <x-product.card :product="$product" />
                @endforeach
            </div>
        </div>
    </section>
@endif
@endsection
