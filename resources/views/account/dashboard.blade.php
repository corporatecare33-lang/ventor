@extends('layouts.app')

@section('content')
<x-account-layout title="Hello, {{ strtok(auth()->user()->name, ' ') }}">
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach ([['Orders', $stats['orders'], 'account.orders'], ['NFC profiles', $stats['nfc'], 'account.nfc.index'], ['Warranty records', $stats['warranty'], 'account.warranty'], ['Saved addresses', $stats['addresses'], 'account.addresses.index']] as [$label, $value, $route])
            <a href="{{ route($route) }}" class="card p-5 hover:border-ink-900/30"><p class="font-display text-4xl font-semibold">{{ $value }}</p><p class="mt-1 text-xs text-ink-600">{{ $label }}</p></a>
        @endforeach
    </div>

    <div class="card mt-6 p-6">
        <div class="mb-4 flex items-center justify-between"><h2 class="font-display text-2xl font-semibold">Recent orders</h2><a href="{{ route('account.orders') }}" class="text-sm text-leather-700 underline">View all</a></div>
        @include('account._orders-table', ['orders' => $recentOrders])
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        <a href="{{ route('account.nfc.index') }}" class="rounded-2xl bg-ink-900 p-6 text-white"><x-lx-icon name="signal" class="size-6 text-gold-400" /><p class="mt-4 font-semibold">LeatherX Tap profile</p><p class="mt-1 text-xs text-white/60">Edit what people see when they tap.</p></a>
        <a href="{{ route('warranty') }}" class="card p-6"><x-lx-icon name="badge" class="size-6 text-gold-600" /><p class="mt-4 font-semibold">Register warranty</p><p class="mt-1 text-xs text-ink-600">For delivered orders.</p></a>
        <a href="{{ route('orders.track') }}" class="card p-6"><x-lx-icon name="truck" class="size-6 text-gold-600" /><p class="mt-4 font-semibold">Track an order</p><p class="mt-1 text-xs text-ink-600">Live delivery status.</p></a>
    </div>
</x-account-layout>
@endsection
