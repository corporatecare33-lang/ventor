@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex items-center justify-center gap-1">
        @if ($paginator->onFirstPage())
            <span class="inline-flex size-11 items-center justify-center text-ink-500/50" aria-hidden="true"><x-lx-icon name="chevron-left" class="size-4" /></span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex size-11 items-center justify-center rounded-xs border border-line-strong hover:border-ink-950" aria-label="Previous page"><x-lx-icon name="chevron-left" class="size-4" /></a>
        @endif
        @foreach ($elements as $element)
            @if (is_string($element))<span class="px-2 text-ink-500">{{ $element }}</span>@endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page" class="inline-flex size-11 items-center justify-center rounded-xs bg-ink-950 text-sm text-white">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="inline-flex size-11 items-center justify-center rounded-xs text-sm hover:bg-paper" aria-label="Page {{ $page }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex size-11 items-center justify-center rounded-xs border border-line-strong hover:border-ink-950" aria-label="Next page"><x-lx-icon name="chevron-right" class="size-4" /></a>
        @else
            <span class="inline-flex size-11 items-center justify-center text-ink-500/50" aria-hidden="true"><x-lx-icon name="chevron-right" class="size-4" /></span>
        @endif
    </nav>
@endif
