{{--
    Product image with responsive srcset. Until photography is uploaded it renders the
    material tile in the product's real leather colour (see .material-tile in app.css).
    loading="eager" loads without the high fetch priority that `eager` (the LCP image) adds.
--}}
@props(['product', 'eager' => false, 'loading' => null, 'sizes' => '(min-width: 1024px) 25vw, 50vw', 'color' => null, 'index' => 0])
@php
    $media = $product->relationLoaded('media')
        ? $product->media->where('collection_name', 'images')->sortBy('order_column')->values()->get($index)
        : $product->getMedia('images')->get($index);
    $color ??= $product->relationLoaded('activeVariants') ? $product->primaryColor() : null;
@endphp
@if ($media)
    @php
        $srcset = collect(['thumb' => 480, 'medium' => 800, 'large' => 1200])
            ->filter(fn ($w, $conversion) => $media->hasGeneratedConversion($conversion))
            ->map(fn ($w, $conversion) => $media->getUrl($conversion).' '.$w.'w')
            ->implode(', ');
    @endphp
    <img src="{{ $media->getAvailableUrl(['medium', 'thumb']) }}" @if ($srcset) srcset="{{ $srcset }}" sizes="{{ $sizes }}" @endif
         alt="{{ $media->getCustomProperty('alt', $product->name) }}"
         style="object-position: {{ $media->getCustomProperty('focus', '50% 50%') }}"
         {{ $attributes->merge(['class' => 'size-full object-cover']) }} width="800" height="1000"
         loading="{{ $loading ?? ($eager ? 'eager' : 'lazy') }}" decoding="async" @if ($eager) fetchpriority="high" @endif>
@else
    <div role="img" aria-label="{{ $product->name }}{{ $color ? ' in '.$color->name : '' }}"
         {{ $attributes->merge(['class' => 'material-tile size-full']) }} style="--tile: {{ $color?->value ?? '#5b3a24' }}">
        <span class="material-tile__mark text-[clamp(0.9rem,2.2vw,1.35rem)]" aria-hidden="true">LX</span>
    </div>
@endif
