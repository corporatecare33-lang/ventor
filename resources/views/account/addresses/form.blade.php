@extends('layouts.app')

@section('content')
<x-account-layout :title="$address->exists ? 'Edit address' : 'Add an address'">
    <form method="POST" action="{{ $address->exists ? route('account.addresses.update', $address) : route('account.addresses.store') }}" class="grid max-w-2xl gap-5 sm:grid-cols-2" x-data="{ busy: false }" @submit="busy = true" novalidate>
        @csrf
        @if ($address->exists) @method('PUT') @endif
        <x-form.input name="label" label="Label" required :value="$address->label ?? 'Home'" hint="For example Home or Office." />
        <x-form.input name="name" label="Recipient name" required :value="$address->name" autocomplete="name" />
        <x-form.input name="phone" label="Mobile number" type="tel" required :value="$address->phone" inputmode="numeric" autocomplete="tel" />
        <x-form.input name="city" label="City" required :value="$address->city ?? 'Dhaka'" autocomplete="address-level2" />
        <x-form.input name="address" label="Address" required class="sm:col-span-2" :value="$address->address" autocomplete="street-address" />
        <x-form.input name="area" label="Area / thana" :value="$address->area" />
        <x-form.input name="district" label="District" :value="$address->district" />
        <x-form.input name="postal_code" label="Postcode" :value="$address->postal_code" inputmode="numeric" autocomplete="postal-code" />
        <x-form.checkbox name="is_default" label="Use as my default address" :checked="$address->is_default" class="self-end pb-3" />
        <div class="flex gap-3 sm:col-span-2">
            <x-form.submit label="Save address" loading="Saving…" />
            <a href="{{ route('account.addresses.index') }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</x-account-layout>
@endsection
