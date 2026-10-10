@extends('layouts.app')

@section('content')
<section class="container-lx max-w-3xl py-14 sm:py-20">
    <div class="text-center">
        <span class="mx-auto grid size-16 place-items-center rounded-full bg-emerald-50 text-emerald-700"><x-lx-icon name="check" class="size-8" /></span>
        <p class="eyebrow mt-6">Thank you, {{ strtok($order->customer_name, ' ') }}</p>
        <h1 class="mt-3 font-display text-4xl font-semibold sm:text-5xl">Your order is placed</h1>
        <p class="mt-4 text-sm text-ink-600">Order number <strong class="tracking-wider text-ink-950">{{ $order->order_number }}</strong>. Our team will call <strong>{{ $order->phone }}</strong> to confirm before dispatch.</p>
    </div>

    @if ($order->payment_status === \App\Enums\PaymentStatus::PendingVerification)
        <div class="mt-8 rounded-2xl border border-amber-600/20 bg-amber-50 p-5 text-sm text-amber-900">
            We received your {{ $order->paymentMethodModel?->name ?? $order->payment_method }} transaction ID. Your order will be confirmed once the payment is verified (usually within a few hours).
        </div>
    @endif

    <div class="mt-10 rounded-3xl bg-cream-50 p-6 sm:p-8">
        <ul class="divide-y divide-ink-900/10">
            @foreach ($order->items as $item)
                <li class="flex justify-between gap-4 py-3 text-sm">
                    <span>{{ $item->product_name }} @if ($item->variant_name)<span class="text-ink-600">· {{ $item->variant_name }}</span>@endif × {{ $item->quantity }}</span>
                    <span class="font-medium">{{ money($item->line_total) }}</span>
                </li>
            @endforeach
        </ul>
        <dl class="mt-4 space-y-2 border-t border-ink-900/10 pt-4 text-sm">
            <div class="flex justify-between"><dt class="text-ink-600">Subtotal</dt><dd>{{ money($order->subtotal + $order->engraving_total) }}</dd></div>
            @if ($order->discount > 0)<div class="flex justify-between text-emerald-700"><dt>Discount</dt><dd>−{{ money($order->discount) }}</dd></div>@endif
            <div class="flex justify-between"><dt class="text-ink-600">Delivery ({{ $order->shipping_zone_name }})</dt><dd>{{ $order->shipping_charge > 0 ? money($order->shipping_charge) : 'Free' }}</dd></div>
            <div class="flex justify-between pt-2 text-lg font-semibold"><dt>Total</dt><dd>{{ money($order->total) }}</dd></div>
        </dl>
        <div class="mt-6 grid gap-4 border-t border-ink-900/10 pt-6 text-sm sm:grid-cols-2">
            <div><p class="label">Delivery address</p><p>{{ $order->customer_name }}<br>{{ $order->fullAddress() }}</p></div>
            <div><p class="label">Payment</p><p>{{ $order->paymentMethodModel?->name ?? $order->payment_method }}</p></div>
        </div>
        @if ($order->engravingRequests->isNotEmpty())
            <p class="mt-6 rounded-xl bg-white px-4 py-3 text-xs text-ink-700"><x-lx-icon name="sparkles" class="mr-1 inline size-4 text-gold-600" /> Engraving requests are reviewed by our team before production. Engraved items need 2–3 extra working days.</p>
        @endif
    </div>

    <div class="mt-8 flex flex-wrap justify-center gap-3">
        @auth
            <a href="{{ route('account.orders.show', $order) }}" class="btn-dark">View order</a>
        @else
            <a href="{{ route('orders.track') }}" class="btn-dark">Track this order</a>
        @endauth
        <a href="{{ route('shop') }}" class="btn-outline">Continue shopping</a>
    </div>
</section>
@endsection
