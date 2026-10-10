@extends('layouts.app')

@section('content')
<section class="container-site max-w-3xl py-12 lg:py-20" aria-labelledby="thanks-title">
    <span class="flex size-12 items-center justify-center rounded-full bg-success-bg text-success"><x-lx-icon name="check" class="size-6" /></span>
    <h1 id="thanks-title" class="mt-6 text-h1 text-ink-950">Thank you, your order is placed</h1>
    <p class="lead mt-4">Order <strong class="text-ink-950">{{ $order->order_number }}</strong>. We will call <strong class="text-ink-950">{{ $order->phone }}</strong> to confirm it before dispatch.</p>

    @if ($order->payment_status === \App\Enums\PaymentStatus::PendingVerification)
        <x-ui.alert type="warning" class="mt-8">We received your {{ $order->paymentMethodModel?->name ?? $order->payment_method }} Transaction ID. The order is confirmed once we have verified the payment.</x-ui.alert>
    @endif

    <div class="mt-10 rounded-sm border border-line">
        <h2 class="border-b border-line px-6 py-4 font-display text-xl text-ink-950">Order summary</h2>
        <ul class="divide-y divide-line px-6">
            @foreach ($order->items as $item)
                <li class="flex justify-between gap-4 py-4 text-[15px]">
                    <span>{{ $item->product_name }}@if ($item->variant_name)<span class="text-ink-500"> · {{ $item->variant_name }}</span>@endif <span class="text-ink-500">× {{ $item->quantity }}</span></span>
                    <span class="font-medium">{{ money($item->line_total) }}</span>
                </li>
            @endforeach
        </ul>
        <dl class="space-y-2 border-t border-line px-6 py-5 text-[15px]">
            <div class="flex justify-between"><dt class="text-ink-600">Subtotal</dt><dd>{{ money($order->subtotal + $order->engraving_total) }}</dd></div>
            @if ($order->discount > 0)<div class="flex justify-between text-success"><dt>Discount</dt><dd>−{{ money($order->discount) }}</dd></div>@endif
            <div class="flex justify-between"><dt class="text-ink-600">Delivery ({{ $order->shipping_zone_name }})</dt><dd>{{ $order->shipping_charge > 0 ? money($order->shipping_charge) : 'Free' }}</dd></div>
            <div class="flex justify-between pt-2 text-lg font-medium text-ink-950"><dt>Total</dt><dd>{{ money($order->total) }}</dd></div>
        </dl>
        <dl class="grid gap-6 border-t border-line px-6 py-5 text-[15px] sm:grid-cols-2">
            <div><dt class="label-caps text-ink-500">Delivering to</dt><dd class="mt-2">{{ $order->customer_name }}<br>{{ $order->fullAddress() }}</dd></div>
            <div><dt class="label-caps text-ink-500">Payment</dt><dd class="mt-2">{{ $order->paymentMethodModel?->name ?? $order->payment_method }}</dd></div>
        </dl>
    </div>
    @if ($order->engravingRequests->isNotEmpty())
        <p class="mt-6 text-[15px] text-ink-600">We will contact you to confirm your engraving before production. Engraved items need two to three extra working days.</p>
    @endif

    <div class="mt-10 flex flex-wrap gap-3">
        @auth<a href="{{ route('account.orders.show', $order) }}" class="btn-primary">View order</a>@else<a href="{{ route('orders.track') }}" class="btn-primary">Track this order</a>@endauth
        <a href="{{ route('shop') }}" class="btn-secondary">Continue shopping</a>
    </div>
</section>
@endsection
