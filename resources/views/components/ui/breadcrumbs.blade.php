{{-- $items: [label => url|null]; the last item is the current page. --}}
@props(['items' => [], 'inverse' => false])
<nav aria-label="Breadcrumb" {{ $attributes->merge(['class' => 'text-[13px]']) }}>
    <ol class="flex flex-wrap items-center gap-x-2 gap-y-1 {{ $inverse ? 'text-white/70' : 'text-ink-500' }}">
        <li><a href="{{ route('home') }}" class="underline-offset-4 hover:underline {{ $inverse ? 'hover:text-white' : 'hover:text-ink-950' }}">Home</a></li>
        @foreach ($items as $label => $url)
            <li aria-hidden="true" class="opacity-60">/</li>
            <li class="max-w-[16rem] truncate">
                @if ($url && ! $loop->last)
                    <a href="{{ $url }}" class="underline-offset-4 hover:underline {{ $inverse ? 'hover:text-white' : 'hover:text-ink-950' }}">{{ $label }}</a>
                @else
                    <span aria-current="page" class="{{ $inverse ? 'text-white' : 'text-ink-950' }}">{{ $label }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
