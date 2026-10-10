@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex items-center justify-center gap-1.5">
        @if ($paginator->onFirstPage())
            <span class="grid size-10 place-items-center rounded-full text-ink-600/30"><x-lx-icon name="chevron-left" class="size-4" /></span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="grid size-10 place-items-center rounded-full border border-ink-900/10 hover:border-ink-900" aria-label="Previous page"><x-lx-icon name="chevron-left" class="size-4" /></a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-2 text-ink-600">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page" class="grid size-10 place-items-center rounded-full bg-ink-900 text-sm text-white">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="grid size-10 place-items-center rounded-full text-sm hover:bg-cream-100">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="grid size-10 place-items-center rounded-full border border-ink-900/10 hover:border-ink-900" aria-label="Next page"><x-lx-icon name="chevron-right" class="size-4" /></a>
        @else
            <span class="grid size-10 place-items-center rounded-full text-ink-600/30"><x-lx-icon name="chevron-right" class="size-4" /></span>
        @endif
    </nav>
@endif
