@extends('layouts.app')

@section('content')
<x-account-layout :title="$address->exists ? 'Edit address' : 'Add address'">
    <form method="POST" action="{{ $address->exists ? route('account.addresses.update', $address) : route('account.addresses.store') }}" class="card grid gap-4 p-6 sm:grid-cols-2">
        @csrf
        @if ($address->exists) @method('PUT') @endif
        <x-input name="label" label="Label" required :value="$address->label ?? 'Home'" placeholder="Home, Office…" />
        <x-input name="name" label="Recipient name" required :value="$address->name" />
        <x-input name="phone" label="Mobile" type="tel" required :value="$address->phone" />
        <x-input name="city" label="City" required :value="$address->city ?? 'Dhaka'" />
        <x-input name="address" label="Address" required class="sm:col-span-2" :value="$address->address" />
        <x-input name="area" label="Area / Thana" :value="$address->area" />
        <x-input name="district" label="District" :value="$address->district" />
        <x-input name="postal_code" label="Postal code" :value="$address->postal_code" />
        <label class="flex items-center gap-2 self-end pb-3 text-sm"><input type="hidden" name="is_default" value="0"><input type="checkbox" name="is_default" value="1" class="size-4 accent-ink-900" @checked(old('is_default', $address->is_default))> Default address</label>
        <div class="flex gap-3 sm:col-span-2"><button class="btn-dark">Save address</button><a href="{{ route('account.addresses.index') }}" class="btn-outline">Cancel</a></div>
    </form>
</x-account-layout>
@endsection
