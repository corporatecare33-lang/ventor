@extends('layouts.app')

@section('content')
<x-page-header title="Track your order" subtitle="Enter your order number and the phone number used at checkout." :crumbs="['Track order' => null]" />
<section class="container-lx max-w-2xl py-12">
    <form method="POST" action="{{ route('orders.track.lookup') }}" class="card grid gap-4 p-6 sm:grid-cols-[1fr_1fr_auto] sm:items-end">
        @csrf
        <x-input name="order_number" label="Order number" required placeholder="LX2410…" :value="request('order_number')" />
        <x-input name="phone" label="Phone" type="tel" required placeholder="01XXXXXXXXX" :value="request('phone')" />
        <button class="btn-dark">Track</button>
    </form>

    @if (! empty($notFound))
        <p class="mt-6 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-800">No order matches that number and phone. Please check and try again.</p>
    @endif

    @if ($order)
        <div class="card mt-8 p-6 sm:p-8">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-2">
                <h2 class="font-display text-2xl font-semibold">Order {{ $order->order_number }}</h2>
                <span class="text-xs text-ink-600">Placed {{ $order->created_at->format('d M Y') }}</span>
            </div>
            @include('orders._timeline')
            <ul class="mt-6 divide-y divide-ink-900/8 border-t border-ink-900/10 text-sm">
                @foreach ($order->items as $item)
                    <li class="flex justify-between py-3"><span>{{ $item->product_name }} × {{ $item->quantity }}</span><span>{{ money($item->line_total) }}</span></li>
                @endforeach
            </ul>
            <p class="mt-4 flex justify-between border-t border-ink-900/10 pt-4 font-semibold"><span>Total</span><span>{{ money($order->total) }}</span></p>
        </div>
    @endif
</section>
@endsection
