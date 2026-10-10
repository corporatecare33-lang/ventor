{{-- Inner-page header: breadcrumbs, the page's single H1 and an intro line. --}}
@props(['title', 'intro' => null, 'eyebrow' => null, 'crumbs' => []])
<header class="border-b border-line bg-paper">
    <div class="container-site pt-6 pb-10 sm:pb-12 lg:pt-8 lg:pb-14">
        <x-ui.breadcrumbs :items="$crumbs" />
        <div class="mt-8 max-w-3xl lg:mt-10">
            @if ($eyebrow)<p class="eyebrow mb-3">{{ $eyebrow }}</p>@endif
            <h1 class="text-h1 text-ink-950">{{ $title }}</h1>
            @if ($intro)<p class="lead mt-4 max-w-2xl">{{ $intro }}</p>@endif
        </div>
        {{ $slot }}
    </div>
</header>
