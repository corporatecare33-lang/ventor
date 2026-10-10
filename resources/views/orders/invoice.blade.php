<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Invoice {{ $order->order_number }}</title>
    @vite(['resources/css/app.css'])
    <style>@media print { .no-print { display: none !important; } body { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }</style>
</head>
<body class="bg-paper py-8 print:bg-white print:py-0">
    <div class="mx-auto max-w-3xl bg-white p-8 shadow-sm sm:p-12 print:shadow-none">
        <div class="flex items-start justify-between gap-6 border-b border-ink-900/10 pb-8">
            <div>
                <x-logo height="h-12" />
                <p class="mt-2 text-xs leading-5 text-ink-600">{{ setting('contact_address') }}<br>{{ setting('contact_phone') }} · {{ setting('contact_email') }}</p>
            </div>
            <div class="text-right">
                <h1 class="font-display text-2xl font-semibold">Invoice</h1>
                <p class="mt-1 text-sm">{{ $order->order_number }}</p>
                <p class="text-xs text-ink-600">{{ $order->created_at->format('d M Y') }}</p>
            </div>
        </div>
        <div class="grid gap-6 py-8 text-sm sm:grid-cols-2">
            <div>
                <p class="label">Bill to</p>
                <p class="font-semibold">{{ $order->customer_name }}</p>
                <p class="text-ink-700">{{ $order->phone }}@if ($order->email)<br>{{ $order->email }}@endif<br>{{ $order->fullAddress() }}</p>
            </div>
            <div class="sm:text-right">
                <p class="label">Payment</p>
                <p>{{ $order->paymentMethodModel?->name ?? $order->payment_method }}</p>
                <p class="text-ink-700">Status: {{ $order->payment_status->getLabel() }}</p>
                <p class="text-ink-700">Order status: {{ $order->status->getLabel() }}</p>
            </div>
        </div>
        <table class="w-full text-sm">
            <thead><tr class="border-y border-ink-900/10 text-left text-xs text-ink-600 uppercase"><th class="py-3">Item</th><th class="py-3 text-right">Price</th><th class="py-3 text-right">Qty</th><th class="py-3 text-right">Total</th></tr></thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr class="border-b border-ink-900/8">
                        <td class="py-3">{{ $item->product_name }}<span class="block text-xs text-ink-600">{{ $item->variant_name }} {{ $item->sku }}@if ($item->engraving_fee > 0) · engraving {{ money($item->engraving_fee) }}@endif</span></td>
                        <td class="py-3 text-right">{{ money($item->unit_price) }}</td>
                        <td class="py-3 text-right">{{ $item->quantity }}</td>
                        <td class="py-3 text-right">{{ money($item->line_total) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <dl class="mt-6 ml-auto w-full max-w-xs space-y-2 text-sm">
            <div class="flex justify-between"><dt class="text-ink-600">Subtotal</dt><dd>{{ money($order->subtotal) }}</dd></div>
            @if ($order->engraving_total > 0)<div class="flex justify-between"><dt class="text-ink-600">Engraving</dt><dd>{{ money($order->engraving_total) }}</dd></div>@endif
            @if ($order->discount > 0)<div class="flex justify-between"><dt class="text-ink-600">Discount {{ $order->coupon_code ? '('.$order->coupon_code.')' : '' }}</dt><dd>−{{ money($order->discount) }}</dd></div>@endif
            <div class="flex justify-between"><dt class="text-ink-600">Delivery</dt><dd>{{ money($order->shipping_charge) }}</dd></div>
            <div class="flex justify-between border-t border-ink-900/10 pt-3 text-base font-semibold"><dt>Total</dt><dd>{{ money($order->total) }}</dd></div>
        </dl>
        <p class="mt-12 text-center text-xs text-ink-600">Thank you for choosing LeatherX. Keep this invoice for warranty claims.</p>
        <div class="no-print mt-8 text-center"><button type="button" onclick="window.print()" class="btn-primary">Print or save as PDF</button></div>
    </div>
</body>
</html>
