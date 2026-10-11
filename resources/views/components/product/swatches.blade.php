@props(['swatches' => [], 'max' => 5, 'size' => 'size-3'])
@if ($swatches)
    <ul {{ $attributes->merge(['class' => 'flex items-center gap-1.5']) }} aria-label="{{ count($swatches) }} {{ Str::plural('colour', count($swatches)) }}: {{ collect($swatches)->pluck('name')->implode(', ') }}">
        @foreach (array_slice($swatches, 0, $max) as $swatch)
            <li class="{{ $size }} rounded-full ring-1 ring-line-strong ring-offset-1" style="background-color: {{ $swatch['hex'] ?? '#ccc' }}" aria-hidden="true"></li>
        @endforeach
        @if (count($swatches) > $max)<li class="text-xs text-ink-500" aria-hidden="true">+{{ count($swatches) - $max }}</li>@endif
    </ul>
@endif
