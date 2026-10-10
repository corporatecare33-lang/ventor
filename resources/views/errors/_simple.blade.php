{{-- Shared body for 403 / 419 / 429 pages (uses the full storefront layout). --}}
@extends('layouts.app', ['seo' => \App\Support\Seo::make($title)->noindex()])

@section('content')
<section class="container-site max-w-2xl py-20 lg:py-28" aria-labelledby="error-title">
    <p class="eyebrow">Error {{ $code }}</p>
    <h1 id="error-title" class="mt-4 text-h1 text-ink-950">{{ $title }}</h1>
    <p class="lead mt-4">{{ $message }}</p>
    <div class="mt-8 flex flex-wrap gap-3">
        <a href="{{ $action ?? route('home') }}" class="btn-primary">{{ $actionLabel ?? 'Back to the homepage' }}</a>
        <a href="{{ route('contact') }}" class="btn-secondary">Contact us</a>
    </div>
</section>
@endsection
