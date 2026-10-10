<x-mail::message>
# {{ $forAdmin ? 'New order received' : 'Thank you for your order!' }}

Order **{{ $order->order_number }}** · {{ $order->created_at->format('d M Y, h:i A') }}

<x-mail::table>
| Item | Qty | Total |
|:-----|:---:|------:|
@foreach ($order->items as $item)
| {{ $item->product_name }}{{ $item->variant_name ? ' ('.$item->variant_name.')' : '' }} | {{ $item->quantity }} | {{ money($item->line_total) }} |
@endforeach
| **Delivery** | | {{ money($order->shipping_charge) }} |
@if ($order->discount > 0)
| **Discount** | | −{{ money($order->discount) }} |
@endif
| **Total** | | **{{ money($order->total) }}** |
</x-mail::table>

**Deliver to:** {{ $order->customer_name }}, {{ $order->phone }}<br>
{{ $order->fullAddress() }}

**Payment:** {{ $order->payment_method }} ({{ $order->payment_status->getLabel() }})

@unless ($forAdmin)
We'll call you to confirm before dispatch.

<x-mail::button :url="route('orders.track')">
Track your order
</x-mail::button>
@endunless

Thanks,<br>
{{ setting('site_name', 'LeatherX') }}
</x-mail::message>
