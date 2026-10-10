@php
    $steps = [\App\Enums\OrderStatus::Pending, \App\Enums\OrderStatus::Confirmed, \App\Enums\OrderStatus::Processing, \App\Enums\OrderStatus::Shipped, \App\Enums\OrderStatus::Delivered];
    $currentIndex = array_search($order->status, $steps, true);
@endphp
@if ($currentIndex === false)
    <x-ui.alert type="error">This order is {{ strtolower($order->status->getLabel()) }}.</x-ui.alert>
@else
    <ol class="grid grid-cols-5 gap-2" aria-label="Order progress">
        @foreach ($steps as $i => $step)
            <li class="text-center" @if ($i === $currentIndex) aria-current="step" @endif>
                <span class="block h-1 rounded-full {{ $i <= $currentIndex ? 'bg-ink-950' : 'bg-paper-300' }}" aria-hidden="true"></span>
                <span class="mt-2 block text-[11px] sm:text-[13px] {{ $i <= $currentIndex ? 'font-medium text-ink-950' : 'text-ink-500' }}">{{ $step->getLabel() }}</span>
            </li>
        @endforeach
    </ol>
@endif
@if ($order->courier_name || $order->tracking_number)
    <p class="mt-5 text-sm text-ink-700">Courier: <strong>{{ $order->courier_name ?? '—' }}</strong>@if ($order->tracking_number) · Tracking number <strong>{{ $order->tracking_number }}</strong>@endif</p>
@endif
