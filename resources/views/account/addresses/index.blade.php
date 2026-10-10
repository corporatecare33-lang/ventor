@extends('layouts.app')

@section('content')
<x-account-layout title="Addresses">
    <a href="{{ route('account.addresses.create') }}" class="btn-primary"><x-lx-icon name="plus" class="size-4" /> Add an address</a>
    @if ($addresses->isEmpty())
        <x-ui.empty-state icon="map-pin" title="No saved addresses" text="Saved addresses fill in automatically at checkout." class="mt-8" />
    @else
        <ul class="mt-8 grid gap-4 sm:grid-cols-2">
            @foreach ($addresses as $address)
                <li class="rounded-sm border border-line p-5 text-[15px]">
                    <p class="flex items-center gap-2 font-medium text-ink-950">{{ $address->label }} @if ($address->is_default)<span class="rounded-xs bg-paper px-2 py-0.5 text-xs font-normal text-ink-600">Default</span>@endif</p>
                    <p class="mt-2 text-ink-700">{{ $address->name }} · {{ $address->phone }}<br>{{ $address->full_address }}</p>
                    <div class="mt-4 flex gap-5 text-sm">
                        <a href="{{ route('account.addresses.edit', $address) }}" class="text-leather-700 underline underline-offset-4">Edit<span class="sr-only"> {{ $address->label }}</span></a>
                        <form method="POST" action="{{ route('account.addresses.destroy', $address) }}" x-data @submit="if (! confirm('Remove this address?')) $event.preventDefault()">
                            @csrf @method('DELETE')
                            <button class="text-danger underline underline-offset-4">Remove<span class="sr-only"> {{ $address->label }}</span></button>
                        </form>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</x-account-layout>
@endsection
