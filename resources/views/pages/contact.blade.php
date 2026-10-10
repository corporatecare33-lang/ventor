@extends('layouts.app')

@section('content')
@php
    $channels = array_values(array_filter([
        setting('contact_phone') ? ['phone', 'Phone', setting('contact_phone'), 'tel:'.preg_replace('/[^0-9+]/', '', setting('contact_phone'))] : null,
        setting('contact_whatsapp') ? ['chat', 'WhatsApp', setting('contact_whatsapp'), 'https://wa.me/'.preg_replace('/\D/', '', setting('contact_whatsapp'))] : null,
        setting('contact_email') ? ['mail', 'Email', setting('contact_email'), 'mailto:'.setting('contact_email')] : null,
        setting('contact_address') ? ['map-pin', 'Address', setting('contact_address'), null] : null,
        setting('business_hours') ? ['clock', 'Hours', setting('business_hours'), null] : null,
    ]));
@endphp
<x-ui.page-header title="Contact us" intro="Questions about an order, engraving, warranty or corporate gifting — send us a message and we will reply as soon as we can." :crumbs="['Contact' => null]" />
<section class="container-site grid gap-12 py-12 lg:grid-cols-12 lg:py-16">
    <div class="lg:col-span-4">
        @if ($channels)
            <h2 class="font-display text-2xl text-ink-950">Reach us directly</h2>
            <dl class="mt-5 border-t border-line">
                @foreach ($channels as [$icon, $label, $value, $href])
                    <div class="flex gap-4 border-b border-line py-4">
                        <x-lx-icon :name="$icon" class="mt-0.5 size-5 shrink-0 text-leather-600" />
                        <div>
                            <dt class="text-[13px] text-ink-500">{{ $label }}</dt>
                            <dd class="mt-0.5 text-[15px] text-ink-950">
                                @if ($href)<a href="{{ $href }}" class="underline-offset-4 hover:underline" @if (str_starts_with($href, 'http')) target="_blank" rel="noopener" @endif>{{ $value }}</a>@else{{ $value }}@endif
                            </dd>
                        </div>
                    </div>
                @endforeach
            </dl>
        @endif
        <p class="{{ $channels ? 'mt-6' : '' }} text-[15px] text-ink-600">Looking for an order? <a href="{{ route('orders.track') }}" class="text-leather-700 underline underline-offset-4">Track it with your order number</a>.</p>
    </div>
    <div class="lg:col-span-7 lg:col-start-6">
        <h2 class="font-display text-2xl text-ink-950">Send a message</h2>
        <form method="POST" action="{{ route('contact.store') }}" class="mt-6 grid gap-5 sm:grid-cols-2" x-data="{ busy: false }" @submit="busy = true" novalidate>
            @csrf
            @if ($errors->any())<x-ui.alert type="error" class="sm:col-span-2">Please check the highlighted fields.</x-ui.alert>@endif
            <x-form.input name="name" label="Name" required autocomplete="name" />
            <x-form.input name="email" label="Email" type="email" required autocomplete="email" />
            <x-form.input name="phone" label="Phone (optional)" type="tel" autocomplete="tel" />
            <x-form.input name="subject" label="Subject (optional)" />
            <x-form.textarea name="message" label="Message" required minlength="10" rows="6" class="sm:col-span-2" />
            <div class="hidden" aria-hidden="true"><label for="website">Leave this field empty</label><input id="website" type="text" name="website" tabindex="-1" autocomplete="off"></div>
            <x-form.submit label="Send message" loading="Sending…" class="sm:col-span-2 sm:justify-self-start" />
        </form>
    </div>
</section>
@endsection
