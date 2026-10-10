@php
    $steps = [\App\Enums\OrderStatus::Pending, \App\Enums\OrderStatus::Confirmed, \App\Enums\OrderStatus::Processing, \App\Enums\OrderStatus::Shipped, \App\Enums\OrderStatus::Delivered];
    $currentIndex = array_search($order->status, $steps, true);
    $stopped = $currentIndex === false;
@endphp
@if ($stopped)
    <p class="rounded-xl bg-red-50 px-4 py-3 text-sm text-red-800">This order is <strong>{{ strtolower($order->status->getLabel()) }}</strong>.</p>
@else
    <ol class="grid grid-cols-5 gap-1">
        @foreach ($steps as $i => $step)
            <li class="text-center">
                <span class="mx-auto grid size-8 place-items-center rounded-full text-xs font-semibold {{ $i <= $currentIndex ? 'bg-ink-900 text-gold-300' : 'bg-ink-900/8 text-ink-600' }}">
                    @if ($i < $currentIndex)<x-lx-icon name="check" class="size-4" />@else{{ $i + 1 }}@endif
                </span>
                <span class="mt-2 block text-[10px] font-medium sm:text-xs {{ $i <= $currentIndex ? 'text-ink-950' : 'text-ink-600/60' }}">{{ $step->getLabel() }}</span>
            </li>
        @endforeach
    </ol>
@endif
@if ($order->courier_name || $order->tracking_number)
    <p class="mt-5 text-sm text-ink-700">Courier: <strong>{{ $order->courier_name ?? '—' }}</strong> @if ($order->tracking_number)· Tracking no. <strong>{{ $order->tracking_number }}</strong>@endif</p>
@endif
