@extends('layouts.app', ['seo' => \App\Support\Seo::make('Page not found')->noindex()])

@section('content')
<section class="container-lx py-24 text-center">
    <p class="eyebrow">404</p>
    <h1 class="mt-4 font-display text-5xl font-semibold">This page wandered off</h1>
    <p class="mx-auto mt-4 max-w-md text-sm text-ink-600">The page you're looking for doesn't exist or has moved.</p>
    <div class="mt-8 flex justify-center gap-3"><a href="{{ route('home') }}" class="btn-dark">Go home</a><a href="{{ route('shop') }}" class="btn-outline">Shop all</a></div>
</section>
@endsection
