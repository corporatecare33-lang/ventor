{{--
    Colour swatches as on Ventro's cards: small squares showing the photo linked to each colour,
    or the colour itself when no photo is linked yet.
--}}
@props(['swatches' => [], 'max' => 5, 'size' => 'size-6'])
@if ($swatches)
    <ul {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-2']) }} aria-label="{{ count($swatches) }} {{ Str::plural('colour', count($swatches)) }}: {{ collect($swatches)->pluck('name')->implode(', ') }}">
        @foreach (array_slice($swatches, 0, $max) as $swatch)
            <li class="{{ $size }} overflow-hidden rounded-sm border border-line bg-mist-200" style="background-color: {{ $swatch['hex'] ?? '#ccc' }}" aria-hidden="true">
                @if ($swatch['image'] ?? null)
                    <img src="{{ $swatch['image'] }}" alt="" class="size-full object-cover" width="96" height="96" loading="lazy" decoding="async">
                @endif
            </li>
        @endforeach
        @if (count($swatches) > $max)<li class="text-xs font-medium text-ink-500" aria-hidden="true">+{{ count($swatches) - $max }}</li>@endif
    </ul>
@endif
