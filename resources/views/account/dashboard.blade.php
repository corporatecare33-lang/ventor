@extends('layouts.app')

@section('content')
<x-account-layout title="Hello, {{ strtok(auth()->user()->name, ' ') }}">
    <dl class="grid grid-cols-2 gap-px overflow-hidden rounded-sm border border-line bg-line sm:grid-cols-4">
        @foreach ([['Orders', $stats['orders'], 'account.orders'], ['Tap profiles', $stats['nfc'], 'account.nfc.index'], ['Warranty records', $stats['warranty'], 'account.warranty'], ['Saved addresses', $stats['addresses'], 'account.addresses.index']] as [$label, $value, $route])
            <div class="bg-white p-5">
                <dt class="text-[13px] text-ink-500"><a href="{{ route($route) }}" class="underline-offset-4 hover:underline">{{ $label }}</a></dt>
                <dd class="mt-1 font-display text-[2rem] leading-none text-ink-950">{{ $value }}</dd>
            </div>
        @endforeach
    </dl>

    <section class="mt-10" aria-labelledby="recent-title">
        <div class="flex items-baseline justify-between gap-4">
            <h2 id="recent-title" class="font-display text-2xl text-ink-950">Recent orders</h2>
            <a href="{{ route('account.orders') }}" class="text-sm text-leather-700 underline underline-offset-4">All orders</a>
        </div>
        <div class="mt-4">@include('account._orders-table', ['orders' => $recentOrders])</div>
    </section>
</x-account-layout>
@endsection
