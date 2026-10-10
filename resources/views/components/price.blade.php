@props(['current', 'regular' => null, 'size' => 'text-base'])
<span {{ $attributes->merge(['class' => 'inline-flex items-baseline gap-2']) }}>
    <span class="{{ $size }} font-semibold text-ink-950">{{ money($current) }}</span>
    @if ($regular && (float) $regular > (float) $current)
        <span class="text-xs text-ink-600/60 line-through">{{ money($regular) }}</span>
    @endif
</span>
