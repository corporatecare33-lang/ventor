@extends('layouts.app')

@section('content')
<x-account-layout :title="$profile->exists ? 'Edit Tap profile' : 'Create Tap profile'">
    <form method="POST" action="{{ $profile->exists ? route('account.nfc.update', $profile) : route('account.nfc.store') }}" class="card grid gap-4 p-6 sm:grid-cols-2">
        @csrf
        @if ($profile->exists) @method('PUT') @endif
        @if ($items->count() > 1)
            <div class="sm:col-span-2">
                <label for="order_item_id" class="label">Linked product</label>
                <select id="order_item_id" name="order_item_id" class="field">
                    @foreach ($items as $item)<option value="{{ $item->id }}">{{ $item->product_name }} — order {{ $item->order->order_number }}</option>@endforeach
                </select>
            </div>
        @endif
        <x-input name="name" label="Display name" required :value="$profile->name" />
        <x-input name="designation" label="Designation" :value="$profile->designation" />
        <x-input name="company" label="Company" :value="$profile->company" />
        <x-input name="phone" label="Phone" type="tel" :value="$profile->phone" />
        <x-input name="email" label="Email" type="email" :value="$profile->email" />
        <x-input name="whatsapp" label="WhatsApp number" type="tel" :value="$profile->whatsapp" />
        <x-input name="website" label="Website" type="url" :value="$profile->website" placeholder="https://" />
        <x-input name="facebook" label="Facebook URL" type="url" :value="$profile->facebook" placeholder="https://facebook.com/…" />
        <x-input name="instagram" label="Instagram URL" type="url" :value="$profile->instagram" placeholder="https://instagram.com/…" />
        <x-input name="linkedin" label="LinkedIn URL" type="url" :value="$profile->linkedin" placeholder="https://linkedin.com/in/…" />
        <div class="sm:col-span-2">
            <label for="bio" class="label">Short bio</label>
            <textarea id="bio" name="bio" rows="3" maxlength="500" class="field">{{ old('bio', $profile->bio) }}</textarea>
        </div>
        <div class="flex flex-wrap gap-3 sm:col-span-2">
            <button class="btn-dark">{{ $profile->exists ? 'Save profile' : 'Create profile' }}</button>
            <a href="{{ route('account.nfc.index') }}" class="btn-outline">Cancel</a>
        </div>
    </form>
    @if ($profile->exists)
        <form method="POST" action="{{ route('account.nfc.destroy', $profile) }}" class="mt-4" onsubmit="return confirm('Delete this profile? Your NFC chip will stop showing it.')">
            @csrf @method('DELETE')
            <button class="text-xs text-red-700 underline">Delete profile</button>
        </form>
    @endif
</x-account-layout>
@endsection
