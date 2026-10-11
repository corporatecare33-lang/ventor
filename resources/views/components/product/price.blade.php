{{-- quiet: card style (as on Ventro), where a regular price is set in normal weight and only a sale price is heavier. --}}
@props(['current', 'regular' => null, 'size' => 'text-[15px]', 'stacked' => false, 'quiet' => false])
@php $onSale = $regular && (float) $regular > (float) $current; @endphp
<p {{ $attributes->merge(['class' => $stacked ? 'flex flex-col items-end leading-tight' : 'flex flex-wrap items-baseline gap-x-2']) }}>
    @if ($onSale)
        <span class="sr-only">Sale price</span>
        <span class="{{ $size }} {{ $quiet ? 'font-medium' : 'font-semibold' }} text-sale">{{ money($current) }}</span>
        <span class="sr-only">Regular price</span>
        <s class="text-[13px] text-ink-500">{{ money($regular) }}</s>
    @else
        <span class="{{ $size }} {{ $quiet ? 'font-normal' : 'font-semibold' }} text-ink-950">{{ money($current) }}</span>
    @endif
</p>
