@extends('layouts.app')

@section('content')
    <x-page-header eyebrow="LeatherX Tap" :title="$section->title ?? 'What is LeatherX Tap?'" :subtitle="$section->subtitle ?? null" :crumbs="['LeatherX Tap' => null]">
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="#tap-products" class="btn-gold">Shop Tap wallets</a>
            @auth<a href="{{ route('account.nfc.index') }}" class="btn-outline-light">Manage my profile</a>@endauth
        </div>
    </x-page-header>

    <section class="container-lx py-16 sm:py-20">
        <div class="grid gap-6 md:grid-cols-3">
            @foreach ($section->content['items'] ?? [] as $item)
                <div class="card p-7">
                    <span class="grid size-12 place-items-center rounded-full bg-ink-900 text-gold-400"><x-lx-icon :name="$item['icon'] ?? 'signal'" class="size-6" /></span>
                    <h2 class="mt-5 font-sans text-base font-semibold">{{ $item['title'] }}</h2>
                    <p class="mt-2 text-sm leading-6 text-ink-600">{{ $item['text'] ?? '' }}</p>
                </div>
            @endforeach
        </div>

        <div class="mt-16 grid items-center gap-10 rounded-3xl bg-cream-50 p-8 sm:p-12 lg:grid-cols-2">
            <div>
                <p class="eyebrow">How it works</p>
                <h2 class="section-title mt-3">From wallet to contact list in one tap</h2>
                <ol class="mt-8 space-y-5 text-sm text-ink-700">
                    @foreach (['Order any LeatherX Tap product.', 'Create your digital profile from your account — name, phone, email, company and social links.', 'We link the NFC chip (and a QR code) to your unique profile URL.', 'Tap the wallet on any modern phone: your profile opens and can be saved as a contact.'] as $step)
                        <li class="flex gap-4"><span class="grid size-8 shrink-0 place-items-center rounded-full bg-ink-900 text-xs font-semibold text-white">{{ $loop->iteration }}</span><span class="pt-1.5">{{ $step }}</span></li>
                    @endforeach
                </ol>
            </div>
            <div class="rounded-2xl bg-white p-6 text-sm leading-6 text-ink-600">
                <p class="font-semibold text-ink-950">Good to know</p>
                <ul class="mt-3 list-disc space-y-1.5 pl-5">
                    <li>No battery and no app — the chip is passive.</li>
                    <li>Works with NFC-enabled Android phones and iPhone XS or newer.</li>
                    <li>Update your details any time; the chip never needs re-programming.</li>
                    <li>Your profile can be paused from your account.</li>
                </ul>
            </div>
        </div>
    </section>

    @if ($products->isNotEmpty())
        <section id="tap-products" class="container-lx scroll-mt-24 pb-20">
            <h2 class="section-title">Shop LeatherX Tap</h2>
            <div class="mt-8 grid grid-cols-2 gap-3 sm:gap-5 lg:grid-cols-4">
                @foreach ($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        </section>
    @endif
@endsection
