<a href="{{ route('cart') }}" class="relative inline-flex size-11 items-center justify-center text-ink-950 hover:text-leather-700"
   aria-label="Cart, {{ $count }} {{ Str::plural('item', $count) }}">
    <x-lx-icon name="cart" class="size-6" />
    @if ($count > 0)
        {{-- A bare number at the icon's corner, as on Ventro --}}
        <span class="absolute top-0 right-0 text-xs leading-[1.125rem] font-medium tabular-nums" aria-hidden="true">{{ $count > 99 ? '99+' : $count }}</span>
    @endif
</a>
