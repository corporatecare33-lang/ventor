@props(['rating' => 0, 'count' => null, 'size' => 'size-3.5'])
@php $value = round((float) $rating, 1); @endphp
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5']) }}>
    <span role="img" aria-label="Rated {{ number_format($value, 1) }} out of 5" class="inline-flex text-brass-600">
        @for ($i = 1; $i <= 5; $i++)
            <x-lx-icon :name="$i <= round($value) ? 'star-solid' : 'star'" :class="$size" />
        @endfor
    </span>
    @if ($count !== null)
        <span class="text-[13px] text-ink-500">({{ $count }})</span>
    @endif
</span>
