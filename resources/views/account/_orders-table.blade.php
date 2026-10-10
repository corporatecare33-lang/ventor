@if ($orders->isEmpty())
    <x-ui.empty-state icon="bag" title="No orders yet" text="When you place an order it will appear here.">
        <a href="{{ route('shop') }}" class="btn-secondary">Start shopping</a>
    </x-ui.empty-state>
@else
    <div class="relative overflow-x-auto border-t border-line" role="region" aria-label="Orders table" tabindex="0">
        <table class="w-full min-w-[36rem] text-left text-[15px]">
            <thead class="text-[13px] text-ink-500"><tr class="border-b border-line"><th scope="col" class="py-3 font-normal">Order</th><th scope="col" class="py-3 font-normal">Date</th><th scope="col" class="py-3 font-normal">Items</th><th scope="col" class="py-3 font-normal">Total</th><th scope="col" class="py-3 font-normal">Status</th><th scope="col" class="py-3"><span class="sr-only">Actions</span></th></tr></thead>
            <tbody>
                @foreach ($orders as $order)
                    <tr class="border-b border-line">
                        <td class="py-3 font-medium text-ink-950">{{ $order->order_number }}</td>
                        <td class="py-3 text-ink-600">{{ $order->created_at->format('j M Y') }}</td>
                        <td class="py-3 text-ink-600">{{ $order->items_count }}</td>
                        <td class="py-3">{{ money($order->total) }}</td>
                        <td class="py-3"><x-ui.status :status="$order->status" /></td>
                        <td class="py-3 text-right"><a href="{{ route('account.orders.show', $order) }}" class="text-leather-700 underline underline-offset-4">View<span class="sr-only"> order {{ $order->order_number }}</span></a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
