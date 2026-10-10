{{-- Brand logo: an uploaded logo from Website settings, otherwise the supplied LeatherX SVG. --}}
@props(['inverse' => false, 'height' => 'h-9'])
@php
    $uploaded = setting('site_logo');
    $src = $uploaded ? Storage::disk('public')->url($uploaded) : asset($inverse ? 'images/brand/leatherx-logo-inverse.svg' : 'images/brand/leatherx-logo.svg');
@endphp
<img src="{{ $src }}" alt="{{ setting('site_name', 'LeatherX') }}" width="299" height="100" {{ $attributes->merge(['class' => $height.' w-auto']) }}>
