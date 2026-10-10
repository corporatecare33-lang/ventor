@props(['items' => [], 'dark' => false])
<nav aria-label="Breadcrumb" {{ $attributes->merge(['class' => 'text-xs']) }}>
    <ol class="flex flex-wrap items-center gap-1.5 {{ $dark ? 'text-white/50' : 'text-ink-600/70' }}">
        <li><a href="{{ route('home') }}" class="hover:text-gold-500">Home</a></li>
        @foreach ($items as $label => $url)
            <li aria-hidden="true">/</li>
            <li>
                @if ($url && ! $loop->last)
                    <a href="{{ $url }}" class="hover:text-gold-500">{{ $label }}</a>
                @else
                    <span class="{{ $dark ? 'text-white/80' : 'text-ink-900' }}" aria-current="page">{{ $label }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
