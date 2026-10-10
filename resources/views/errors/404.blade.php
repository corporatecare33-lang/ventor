@extends('layouts.app', ['seo' => \App\Support\Seo::make('Page not found')->noindex()])

@section('content')
<section class="container-site grid gap-12 py-16 lg:grid-cols-12 lg:py-24" aria-labelledby="error-title">
    <div class="lg:col-span-6">
        <p class="eyebrow">Error 404</p>
        <h1 id="error-title" class="mt-4 text-h1 text-ink-950">We could not find that page</h1>
        <p class="lead mt-4 max-w-md">The link may be out of date, or the product may have been renamed. Try searching, or start from one of the pages below.</p>
        <form action="{{ route('shop') }}" method="GET" role="search" class="mt-8 flex max-w-md gap-2">
            <label for="error-q" class="sr-only">Search products</label>
            <input id="error-q" type="search" name="q" class="field" placeholder="Search wallets, bags…">
            <button class="btn-primary shrink-0">Search</button>
        </form>
        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('home') }}" class="btn-secondary">Back to the homepage</a>
        </div>
    </div>
    <nav class="lg:col-span-5 lg:col-start-8" aria-label="Popular pages">
        <h2 class="label-caps font-sans text-ink-500">Popular pages</h2>
        <ul class="mt-4 border-t border-line">
            @foreach ($layout['menuCategories']->take(6) as $category)
                <li class="border-b border-line"><a href="{{ route('categories.show', $category) }}" class="flex min-h-12 items-center justify-between text-[15px] hover:text-leather-700">{{ $category->name }} <x-lx-icon name="arrow-right" class="size-4 text-ink-500" /></a></li>
            @endforeach
            <li class="border-b border-line"><a href="{{ route('orders.track') }}" class="flex min-h-12 items-center justify-between text-[15px] hover:text-leather-700">Track an order <x-lx-icon name="arrow-right" class="size-4 text-ink-500" /></a></li>
            <li class="border-b border-line"><a href="{{ route('contact') }}" class="flex min-h-12 items-center justify-between text-[15px] hover:text-leather-700">Contact us <x-lx-icon name="arrow-right" class="size-4 text-ink-500" /></a></li>
        </ul>
    </nav>
</section>
@endsection
