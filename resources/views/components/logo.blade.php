{{-- Brand logo: an uploaded logo from Website settings, otherwise the LeatherX wordmark. --}}
@props(['inverse' => false, 'height' => 'h-5'])
@php
    $uploaded = setting('site_logo');
    $src = $uploaded ? Storage::disk('public')->url($uploaded) : asset($inverse ? 'images/brand/leatherx-logo-inverse.svg' : 'images/brand/leatherx-logo.png');
    [$intrinsicWidth, $intrinsicHeight] = ! $uploaded && ! $inverse ? [1835, 429] : [302, 46];
@endphp
<img src="{{ $src }}" alt="{{ setting('site_name', 'LeatherX') }}" width="{{ $intrinsicWidth }}" height="{{ $intrinsicHeight }}" {{ $attributes->merge(['class' => $height.' w-auto']) }}>
