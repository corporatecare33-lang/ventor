@extends('layouts.app')

@section('content')
<x-ui.page-header title="Track your order" intro="Enter your order number and the phone number used at checkout." :crumbs="['Track an order' => null]" />
<section class="container-site max-w-3xl py-12 lg:py-16">
    <form method="POST" action="{{ route('orders.track.lookup') }}" class="grid gap-5 sm:grid-cols-[1fr_1fr_auto] sm:items-start" x-data="{ busy: false }" @submit="busy = true" novalidate>
        @csrf
        <x-form.input name="order_number" label="Order number" required :value="request('order_number')" autocomplete="off" />
        <x-form.input name="phone" label="Phone" type="tel" required :value="request('phone')" inputmode="numeric" autocomplete="tel" />
        <x-form.submit label="Track" loading="Searching…" class="sm:mt-7" />
    </form>

    @if (! empty($notFound))
        <x-ui.alert type="error" class="mt-8">No order matches that number and phone. Check both and try again, or <a href="{{ route('contact') }}" class="underline">contact us</a>.</x-ui.alert>
    @endif

    @if ($order)
        <div class="mt-10 rounded-sm border border-line p-6 sm:p-8" role="status">
            <div class="mb-6 flex flex-wrap items-baseline justify-between gap-2">
                <h2 class="font-display text-2xl text-ink-950">Order {{ $order->order_number }}</h2>
                <span class="text-[13px] text-ink-500">Placed {{ $order->created_at->format('j M Y') }}</span>
            </div>
            @include('orders._timeline')
            <ul class="mt-8 divide-y divide-line border-t border-line text-[15px]">
                @foreach ($order->items as $item)
                    <li class="flex justify-between py-3"><span>{{ $item->product_name }} <span class="text-ink-500">× {{ $item->quantity }}</span></span><span>{{ money($item->line_total) }}</span></li>
                @endforeach
            </ul>
            <p class="mt-2 flex justify-between border-t border-line pt-4 font-medium text-ink-950"><span>Total</span><span>{{ money($order->total) }}</span></p>
        </div>
    @endif
</section>
@endsection
