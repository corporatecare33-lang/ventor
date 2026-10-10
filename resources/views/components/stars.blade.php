@props(['rating' => 0, 'size' => 'size-3.5'])
<span class="inline-flex items-center gap-0.5 text-gold-500" aria-label="Rated {{ number_format((float) $rating, 1) }} out of 5">
    @for ($i = 1; $i <= 5; $i++)
        <x-lx-icon name="{{ $i <= round((float) $rating) ? 'star-solid' : 'star' }}" class="{{ $size }}" />
    @endfor
</span>
