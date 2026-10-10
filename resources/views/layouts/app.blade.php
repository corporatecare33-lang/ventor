@php
    $seo ??= \App\Support\Seo::make();
    $siteName = setting('site_name', 'LeatherX');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $seo->fullTitle() }}</title>
    <meta name="description" content="{{ $seo->metaDescription() }}">
    <meta name="robots" content="{{ $seo->robots() }}">
    @if ($keywords = setting('seo_keywords'))<meta name="keywords" content="{{ $keywords }}">@endif
    <link rel="canonical" href="{{ $seo->canonical ?? url()->current() }}">
    <meta name="theme-color" content="#0d0b0a">

    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:type" content="{{ $seo->type }}">
    <meta property="og:title" content="{{ $seo->title ?? $seo->fullTitle() }}">
    <meta property="og:description" content="{{ $seo->metaDescription() }}">
    <meta property="og:url" content="{{ $seo->canonical ?? url()->current() }}">
    <meta property="og:locale" content="en_BD">
    @if ($image = $seo->imageUrl())
        <meta property="og:image" content="{{ $image }}">
        <meta name="twitter:image" content="{{ $image }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">
    @if ($verification = setting('google_site_verification'))<meta name="google-site-verification" content="{{ $verification }}">@endif

    @if ($favicon = setting('site_favicon'))
        <link rel="icon" href="{{ Storage::disk('public')->url($favicon) }}">
    @else
        <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%230d0b0a'/%3E%3Ctext x='16' y='22' font-family='Georgia' font-size='17' text-anchor='middle' fill='%23c08a3e'%3EX%3C/text%3E%3C/svg%3E">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    @foreach ($seo->schemas as $schema)
        <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    @endforeach

    @if ($ga = setting('google_analytics_id'))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga }}"></script>
        <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config',@js($ga));</script>
    @endif
    @if ($pixel = setting('facebook_pixel_id'))
        <script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init',@js($pixel));fbq('track','PageView');</script>
    @endif
    @stack('head')
</head>
<body class="flex min-h-screen flex-col">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-[100] focus:rounded-full focus:bg-white focus:px-4 focus:py-2">Skip to content</a>

    @include('layouts.partials.header')

    <main id="main" class="flex-1">
        @if (session('success') || session('error'))
            <div class="container-lx pt-6">
                <x-flash />
            </div>
        @endif
        @yield('content')
    </main>

    @include('layouts.partials.footer')

    <livewire:quick-view />
    <x-toast />

    @livewireScripts
    @stack('scripts')
</body>
</html>
