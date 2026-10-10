<a href="{{ route('cart') }}" class="relative inline-flex size-11 items-center justify-center text-ink-950 hover:text-leather-700"
   aria-label="Cart, {{ $count }} {{ Str::plural('item', $count) }}">
    <x-lx-icon name="bag" class="size-5" />
    @if ($count > 0)
        <span class="absolute top-1.5 right-1 grid min-w-[1.125rem] place-items-center rounded-full bg-ink-950 px-1 text-[10px] leading-[1.125rem] font-semibold text-white" aria-hidden="true">{{ $count > 99 ? '99+' : $count }}</span>
    @endif
</a>
