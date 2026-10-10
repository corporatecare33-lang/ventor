@if ($orders->isEmpty())
    <p class="py-6 text-center text-sm text-ink-600">No orders yet. <a href="{{ route('shop') }}" class="text-leather-700 underline">Start shopping</a></p>
@else
    <div class="overflow-x-auto">
        <table class="w-full min-w-[560px] text-left text-sm">
            <thead class="text-xs text-ink-600"><tr><th class="py-2">Order</th><th class="py-2">Date</th><th class="py-2">Items</th><th class="py-2">Total</th><th class="py-2">Status</th><th></th></tr></thead>
            <tbody>
                @foreach ($orders as $order)
                    <tr class="border-t border-ink-900/8">
                        <td class="py-3 font-semibold">{{ $order->order_number }}</td>
                        <td class="py-3 text-ink-600">{{ $order->created_at->format('d M Y') }}</td>
                        <td class="py-3">{{ $order->items_count }}</td>
                        <td class="py-3">{{ money($order->total) }}</td>
                        <td class="py-3"><x-status-badge :status="$order->status" /></td>
                        <td class="py-3 text-right"><a href="{{ route('account.orders.show', $order) }}" class="text-leather-700 underline">Details</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
