@php
    $seo ??= \App\Support\Seo::make();
    $siteName = setting('site_name', 'LeatherX');
    $canonical = $seo->canonical ?? url()->current();
@endphp
<!DOCTYPE html>
<html lang="en-BD">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>document.documentElement.classList.add('js')</script>
    <title>{{ $seo->fullTitle() }}</title>
    <meta name="description" content="{{ $seo->metaDescription() }}">
    <meta name="robots" content="{{ $seo->robots() }}">
    <link rel="canonical" href="{{ $canonical }}">

    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:type" content="{{ $seo->type }}">
    <meta property="og:title" content="{{ $seo->shareTitle() }}">
    <meta property="og:description" content="{{ $seo->metaDescription() }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:locale" content="en_BD">
    <meta property="og:image" content="{{ $seo->imageUrl() }}">
    <meta property="og:image:alt" content="{{ $seo->shareTitle() }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seo->shareTitle() }}">
    <meta name="twitter:description" content="{{ $seo->metaDescription() }}">
    <meta name="twitter:image" content="{{ $seo->imageUrl() }}">
    @if ($verification = setting('google_site_verification'))<meta name="google-site-verification" content="{{ $verification }}">@endif

    <meta name="theme-color" content="#16130f">
    <meta name="application-name" content="{{ $siteName }}">
    <meta name="apple-mobile-web-app-title" content="{{ $siteName }}">
    @if ($favicon = setting('site_favicon'))
        <link rel="icon" href="{{ Storage::disk('public')->url($favicon) }}">
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="32x32">
        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @endif
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ route('manifest') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    @foreach ($seo->allSchemas() as $schema)
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
<body class="flex min-h-dvh flex-col">
    <a href="#main" class="sr-only z-[100] rounded-xs bg-ink-950 px-4 py-3 text-sm text-white focus:not-sr-only focus:fixed focus:top-3 focus:left-3">Skip to main content</a>

    @include('layouts.partials.header')

    <main id="main" tabindex="-1" class="flex-1 focus:outline-none">
        @if (session('success') || session('error'))
            <div class="container-site pt-6"><x-ui.flash /></div>
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
