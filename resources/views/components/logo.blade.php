{{-- Brand logo: an uploaded logo from Website settings, otherwise the LeatherX monoline wordmark (SVG). --}}
@props(['inverse' => false, 'height' => 'h-5'])
@php
    $uploaded = setting('site_logo');
    $src = $uploaded ? Storage::disk('public')->url($uploaded) : asset($inverse ? 'images/brand/leatherx-logo-inverse.svg' : 'images/brand/leatherx-logo.svg');
@endphp
<img src="{{ $src }}" alt="{{ setting('site_name', 'LeatherX') }}" width="302" height="46" {{ $attributes->merge(['class' => $height.' w-auto']) }}>
