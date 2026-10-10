@props(['current', 'regular' => null, 'size' => 'text-[15px]'])
<p {{ $attributes->merge(['class' => 'flex flex-wrap items-baseline gap-x-2']) }}>
    @if ($regular && (float) $regular > (float) $current)
        <span class="sr-only">Sale price</span>
        <span class="{{ $size }} font-medium text-ink-950">{{ money($current) }}</span>
        <span class="sr-only">Regular price</span>
        <s class="text-[13px] text-ink-500">{{ money($regular) }}</s>
    @else
        <span class="{{ $size }} font-medium text-ink-950">{{ money($current) }}</span>
    @endif
</p>
