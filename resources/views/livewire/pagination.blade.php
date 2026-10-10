@php $pageName = $paginator->getPageName(); @endphp
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex items-center justify-center gap-1.5">
        @if ($paginator->onFirstPage())
            <span class="grid size-10 place-items-center rounded-full text-ink-600/30"><x-lx-icon name="chevron-left" class="size-4" /></span>
        @else
            <button type="button" wire:click="previousPage('{{ $pageName }}')" x-on:click="window.scrollTo({ top: 0, behavior: 'smooth' })" class="grid size-10 place-items-center rounded-full border border-ink-900/10 hover:border-ink-900" aria-label="Previous page"><x-lx-icon name="chevron-left" class="size-4" /></button>
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
                        <button type="button" wire:click="gotoPage({{ $page }}, '{{ $pageName }}')" x-on:click="window.scrollTo({ top: 0, behavior: 'smooth' })" class="grid size-10 place-items-center rounded-full text-sm hover:bg-cream-100">{{ $page }}</button>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <button type="button" wire:click="nextPage('{{ $pageName }}')" x-on:click="window.scrollTo({ top: 0, behavior: 'smooth' })" class="grid size-10 place-items-center rounded-full border border-ink-900/10 hover:border-ink-900" aria-label="Next page"><x-lx-icon name="chevron-right" class="size-4" /></button>
        @else
            <span class="grid size-10 place-items-center rounded-full text-ink-600/30"><x-lx-icon name="chevron-right" class="size-4" /></span>
        @endif
    </nav>
@endif
