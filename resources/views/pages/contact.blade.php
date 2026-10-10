@extends('layouts.app')

@section('content')
<x-page-header title="Contact us" subtitle="Questions about an order, engraving, warranty or corporate gifting? We're here to help." :crumbs="['Contact' => null]" />
<section class="container-lx grid gap-10 py-14 lg:grid-cols-[1fr_1.3fr]">
    <div class="space-y-4">
        @foreach ([
            ['phone', 'Phone', setting('contact_phone'), setting('contact_phone') ? 'tel:'.preg_replace('/[^0-9+]/', '', setting('contact_phone')) : null],
            ['chat', 'WhatsApp', setting('contact_whatsapp'), setting('contact_whatsapp') ? 'https://wa.me/'.preg_replace('/\D/', '', setting('contact_whatsapp')) : null],
            ['mail', 'Email', setting('contact_email'), setting('contact_email') ? 'mailto:'.setting('contact_email') : null],
            ['map-pin', 'Address', setting('contact_address'), null],
            ['clock', 'Hours', setting('business_hours'), null],
        ] as [$icon, $label, $value, $href])
            @if ($value)
                <div class="card flex items-start gap-4 p-5">
                    <span class="grid size-11 shrink-0 place-items-center rounded-full bg-ink-900 text-gold-400"><x-lx-icon :name="$icon" class="size-5" /></span>
                    <div><p class="text-xs text-ink-600">{{ $label }}</p>
                        @if ($href)<a href="{{ $href }}" class="text-sm font-medium hover:text-gold-600" @if (str_starts_with($href, 'http')) target="_blank" rel="noopener" @endif>{{ $value }}</a>@else<p class="text-sm font-medium">{{ $value }}</p>@endif
                    </div>
                </div>
            @endif
        @endforeach
        @if ($map = setting('google_map_embed'))
            <iframe src="{{ $map }}" class="h-64 w-full rounded-2xl border-0" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Map"></iframe>
        @endif
    </div>
    <form method="POST" action="{{ route('contact.store') }}" class="card grid gap-4 p-6 sm:grid-cols-2 sm:p-8">
        @csrf
        <x-input name="name" label="Name" required />
        <x-input name="email" label="Email" type="email" required />
        <x-input name="phone" label="Phone" type="tel" />
        <x-input name="subject" label="Subject" />
        <div class="sm:col-span-2">
            <label for="message" class="label">Message <span class="text-red-700">*</span></label>
            <textarea id="message" name="message" rows="5" required minlength="10" class="field">{{ old('message') }}</textarea>
            @error('message')<p class="error">{{ $message }}</p>@enderror
        </div>
        <div class="hidden" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
        <button class="btn-dark sm:col-span-2">Send message</button>
    </form>
</section>
@endsection
