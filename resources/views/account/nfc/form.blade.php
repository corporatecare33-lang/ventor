@extends('layouts.app')

@section('content')
<x-account-layout :title="$profile->exists ? 'Edit Tap profile' : 'Create a Tap profile'">
    <p class="max-w-2xl text-[15px] text-ink-600">Everything you add here is shown publicly when someone taps your wallet or scans its QR code. Leave out anything you do not want to share.</p>
    <form method="POST" action="{{ $profile->exists ? route('account.nfc.update', $profile) : route('account.nfc.store') }}" class="mt-8 grid max-w-2xl gap-5 sm:grid-cols-2" x-data="{ busy: false }" @submit="busy = true" novalidate>
        @csrf
        @if ($profile->exists) @method('PUT') @endif
        @if ($items->count() > 1)
            <x-form.select name="order_item_id" label="Linked product" class="sm:col-span-2"
                :options="$items->mapWithKeys(fn ($i) => [$i->id => $i->product_name.' — order '.$i->order->order_number])->all()" />
        @endif
        <x-form.input name="name" label="Display name" required :value="$profile->name" autocomplete="name" />
        <x-form.input name="designation" label="Job title" :value="$profile->designation" autocomplete="organization-title" />
        <x-form.input name="company" label="Company" :value="$profile->company" autocomplete="organization" />
        <x-form.input name="phone" label="Phone" type="tel" :value="$profile->phone" autocomplete="tel" />
        <x-form.input name="email" label="Email" type="email" :value="$profile->email" autocomplete="email" />
        <x-form.input name="whatsapp" label="WhatsApp number" type="tel" :value="$profile->whatsapp" hint="With country code, e.g. 880…" />
        <x-form.input name="website" label="Website" type="url" :value="$profile->website" hint="Full address starting with https://" class="sm:col-span-2" />
        <x-form.input name="facebook" label="Facebook page URL" type="url" :value="$profile->facebook" />
        <x-form.input name="instagram" label="Instagram profile URL" type="url" :value="$profile->instagram" />
        <x-form.input name="linkedin" label="LinkedIn profile URL" type="url" :value="$profile->linkedin" class="sm:col-span-2" />
        <x-form.textarea name="bio" label="Short bio" :value="$profile->bio" rows="3" maxlength="500" class="sm:col-span-2" />
        <div class="flex flex-wrap gap-3 sm:col-span-2">
            <x-form.submit :label="$profile->exists ? 'Save profile' : 'Create profile'" loading="Saving…" />
            <a href="{{ route('account.nfc.index') }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
    @if ($profile->exists)
        <form method="POST" action="{{ route('account.nfc.destroy', $profile) }}" class="mt-10 border-t border-line pt-6" x-data @submit="if (! confirm('Delete this profile? Your chip and QR code will stop showing it.')) $event.preventDefault()">
            @csrf @method('DELETE')
            <button class="text-sm text-danger underline underline-offset-4">Delete this profile</button>
        </form>
    @endif
</x-account-layout>
@endsection
