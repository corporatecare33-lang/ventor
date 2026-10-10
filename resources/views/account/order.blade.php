@extends('layouts.app')

@section('content')
<x-account-layout title="Order {{ $order->order_number }}">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-[15px] text-ink-600">Placed {{ $order->created_at->format('j M Y, g:i a') }} · <x-ui.status :status="$order->payment_status" /></p>
        <a href="{{ route('orders.invoice', $order) }}" target="_blank" class="btn-secondary btn-sm"><x-lx-icon name="printer" class="size-4" /> Invoice</a>
    </div>
    <div class="mt-6 rounded-sm border border-line p-6">@include('orders._timeline')</div>

    <div class="mt-8 grid gap-8 lg:grid-cols-12">
        <section class="lg:col-span-7" aria-labelledby="items-title">
            <h2 id="items-title" class="font-display text-2xl text-ink-950">Items</h2>
            <ul class="mt-4 divide-y divide-line border-y border-line">
                @foreach ($order->items as $item)
                    <li class="flex justify-between gap-4 py-4 text-[15px]">
                        <div>
                            @if ($item->product)<a href="{{ route('products.show', $item->product) }}" class="font-medium text-ink-950 underline-offset-4 hover:underline">{{ $item->product_name }}</a>@else<span class="font-medium">{{ $item->product_name }}</span>@endif
                            <p class="text-[13px] text-ink-500">{{ $item->variant_name }}@if ($item->variant_name) · @endif Qty {{ $item->quantity }}</p>
                            @if ($eng = $order->engravingRequests->firstWhere('order_item_id', $item->id))
                                <p class="mt-1 text-[13px] text-ink-600">Engraving “{{ $eng->text ?? 'Logo' }}” — {{ $eng->status->getLabel() }}</p>
                            @endif
                        </div>
                        <span>{{ money($item->line_total) }}</span>
                    </li>
                @endforeach
            </ul>
            <dl class="mt-4 space-y-2 text-[15px]">
                <div class="flex justify-between"><dt class="text-ink-600">Subtotal</dt><dd>{{ money($order->subtotal + $order->engraving_total) }}</dd></div>
                @if ($order->discount > 0)<div class="flex justify-between"><dt class="text-ink-600">Discount</dt><dd>−{{ money($order->discount) }}</dd></div>@endif
                <div class="flex justify-between"><dt class="text-ink-600">Delivery</dt><dd>{{ money($order->shipping_charge) }}</dd></div>
                <div class="flex justify-between font-medium text-ink-950"><dt>Total</dt><dd>{{ money($order->total) }}</dd></div>
            </dl>
        </section>
        <div class="space-y-8 lg:col-span-5">
            <section aria-labelledby="address-title">
                <h2 id="address-title" class="font-display text-xl text-ink-950">Delivery address</h2>
                <p class="mt-2 text-[15px] text-ink-700">{{ $order->customer_name }}<br>{{ $order->phone }}<br>{{ $order->fullAddress() }}</p>
            </section>
            <section aria-labelledby="history-title">
                <h2 id="history-title" class="font-display text-xl text-ink-950">History</h2>
                <ol class="mt-3 space-y-3 border-l border-line pl-4">
                    @foreach ($order->statusHistories as $history)
                        <li><p class="text-[15px] font-medium text-ink-950">{{ $history->status->getLabel() }}</p><p class="text-[13px] text-ink-500">{{ $history->created_at->format('j M, g:i a') }}@if ($history->note) · {{ $history->note }}@endif</p></li>
                    @endforeach
                </ol>
            </section>
        </div>
    </div>
</x-account-layout>
@endsection
