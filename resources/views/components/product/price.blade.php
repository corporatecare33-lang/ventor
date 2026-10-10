@props(['current', 'regular' => null, 'size' => 'text-[15px]', 'stacked' => false])
@php $onSale = $regular && (float) $regular > (float) $current; @endphp
<p {{ $attributes->merge(['class' => $stacked ? 'flex flex-col items-end leading-tight' : 'flex flex-wrap items-baseline gap-x-2']) }}>
    @if ($onSale)
        <span class="sr-only">Sale price</span>
        <span class="{{ $size }} font-semibold text-sale">{{ money($current) }}</span>
        <span class="sr-only">Regular price</span>
        <s class="text-[13px] text-ink-500">{{ money($regular) }}</s>
    @else
        <span class="{{ $size }} font-semibold text-ink-950">{{ money($current) }}</span>
    @endif
</p>
