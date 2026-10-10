@extends('layouts.app')

@section('content')
<x-account-layout title="Addresses">
    <a href="{{ route('account.addresses.create') }}" class="btn-dark mb-6"><x-lx-icon name="plus" class="size-4" /> Add address</a>
    <div class="grid gap-4 sm:grid-cols-2">
        @forelse ($addresses as $address)
            <div class="card p-5 text-sm">
                <div class="flex items-center justify-between"><p class="font-semibold">{{ $address->label }}</p>@if ($address->is_default)<span class="rounded-full bg-gold-500/15 px-2.5 py-1 text-[10px] font-semibold text-gold-600">DEFAULT</span>@endif</div>
                <p class="mt-2 text-ink-700">{{ $address->name }} · {{ $address->phone }}<br>{{ $address->full_address }}</p>
                <div class="mt-4 flex gap-4 text-xs">
                    <a href="{{ route('account.addresses.edit', $address) }}" class="text-leather-700 underline">Edit</a>
                    <form method="POST" action="{{ route('account.addresses.destroy', $address) }}" onsubmit="return confirm('Remove this address?')">@csrf @method('DELETE')<button class="text-red-700 underline">Remove</button></form>
                </div>
            </div>
        @empty
            <p class="text-sm text-ink-600">No saved addresses yet.</p>
        @endforelse
    </div>
</x-account-layout>
@endsection
