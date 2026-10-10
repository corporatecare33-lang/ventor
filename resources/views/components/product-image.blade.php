@props(['product', 'conversion' => 'thumb', 'eager' => false, 'class' => ''])
@php
    $media = $product->relationLoaded('media') ? $product->media->where('collection_name', 'images')->sortBy('order_column')->first() : $product->getFirstMedia('images');
@endphp
@if ($media)
    <img src="{{ $media->getAvailableUrl([$conversion]) }}" alt="{{ $media->getCustomProperty('alt', $product->name) }}"
         class="{{ $class }} size-full object-cover" width="600" height="600"
         loading="{{ $eager ? 'eager' : 'lazy' }}" decoding="async" @if ($eager) fetchpriority="high" @endif>
@else
    {{-- Placeholder until product photos are uploaded from the admin panel --}}
    <div class="{{ $class }} placeholder-leather relative grid size-full place-items-center overflow-hidden" role="img" aria-label="{{ $product->name }}">
        <div class="absolute inset-4 rounded-xl border border-dashed border-tan-300/20"></div>
        <div class="text-center">
            <x-lx-icon name="leather" class="mx-auto size-10 text-tan-300/60" />
            <p class="mt-2 px-4 font-display text-sm tracking-[0.2em] text-tan-300/60 uppercase">LeatherX</p>
        </div>
    </div>
@endif
