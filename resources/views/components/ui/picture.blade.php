{{--
    Responsive WebP image for banners and section photography (Spatie Media Library conversions).
    Focal point: set the media custom property "focus" (e.g. "70% 50%") to steer object-position.
--}}
@props(['media', 'conversions' => ['mobile' => 800, 'tablet' => 1280, 'desktop' => 1920], 'sizes' => '100vw', 'eager' => false, 'alt' => null, 'width' => 1600, 'height' => 900])
@php
    $available = collect($conversions)->filter(fn ($w, $conversion) => $media->hasGeneratedConversion($conversion));
    $srcset = $available->map(fn ($w, $conversion) => $media->getUrl($conversion).' '.$w.'w')->implode(', ');
    $src = $available->isNotEmpty() ? $media->getUrl($available->keys()->last()) : $media->getUrl();
@endphp
<img src="{{ $src }}" @if ($srcset) srcset="{{ $srcset }}" sizes="{{ $sizes }}" @endif
     alt="{{ $alt ?? $media->getCustomProperty('alt', '') }}" width="{{ $width }}" height="{{ $height }}"
     loading="{{ $eager ? 'eager' : 'lazy' }}" decoding="async" @if ($eager) fetchpriority="high" @endif
     style="object-position: {{ $media->getCustomProperty('focus', '50% 50%') }}"
     {{ $attributes->merge(['class' => 'size-full object-cover']) }}>
