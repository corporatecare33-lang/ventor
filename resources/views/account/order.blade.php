@extends('layouts.app')

@section('content')
<x-account-layout title="Order {{ $order->order_number }}">
    <div class="card p-6">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-ink-600">Placed {{ $order->created_at->format('d M Y, h:i A') }}</p>
            <div class="flex gap-2">
                <x-status-badge :status="$order->payment_status" />
                <a href="{{ route('orders.invoice', $order) }}" target="_blank" class="btn-outline px-4 py-2 text-xs"><x-lx-icon name="printer" class="size-4" /> Invoice</a>
            </div>
        </div>
        @include('orders._timeline')
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_300px]">
        <div class="card p-6">
            <h2 class="font-display text-2xl font-semibold">Items</h2>
            <ul class="mt-4 divide-y divide-ink-900/8">
                @foreach ($order->items as $item)
                    <li class="flex justify-between gap-4 py-3 text-sm">
                        <div>
                            @if ($item->product)<a href="{{ route('products.show', $item->product) }}" class="font-medium hover:text-leather-700">{{ $item->product_name }}</a>@else<span class="font-medium">{{ $item->product_name }}</span>@endif
                            <p class="text-xs text-ink-600">{{ $item->variant_name }} · Qty {{ $item->quantity }}</p>
                            @if ($eng = $order->engravingRequests->firstWhere('order_item_id', $item->id))
                                <p class="mt-1 text-xs text-leather-700">Engraving “{{ $eng->text ?? 'Logo' }}” — {{ $eng->status->getLabel() }}</p>
                            @endif
                        </div>
                        <span>{{ money($item->line_total) }}</span>
                    </li>
                @endforeach
            </ul>
            <dl class="mt-4 space-y-2 border-t border-ink-900/10 pt-4 text-sm">
                <div class="flex justify-between"><dt class="text-ink-600">Subtotal</dt><dd>{{ money($order->subtotal + $order->engraving_total) }}</dd></div>
                @if ($order->discount > 0)<div class="flex justify-between"><dt class="text-ink-600">Discount</dt><dd>−{{ money($order->discount) }}</dd></div>@endif
                <div class="flex justify-between"><dt class="text-ink-600">Delivery</dt><dd>{{ money($order->shipping_charge) }}</dd></div>
                <div class="flex justify-between font-semibold"><dt>Total</dt><dd>{{ money($order->total) }}</dd></div>
            </dl>
        </div>
        <div class="space-y-6">
            <div class="card p-6 text-sm"><p class="label">Delivery address</p><p>{{ $order->customer_name }}<br>{{ $order->phone }}<br>{{ $order->fullAddress() }}</p></div>
            <div class="card p-6 text-sm">
                <p class="label">Timeline</p>
                <ul class="space-y-3">
                    @foreach ($order->statusHistories as $history)
                        <li><p class="font-medium">{{ $history->status->getLabel() }}</p><p class="text-xs text-ink-600">{{ $history->created_at->format('d M, h:i A') }}@if ($history->note) · {{ $history->note }}@endif</p></li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</x-account-layout>
@endsection
