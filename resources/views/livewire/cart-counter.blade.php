<a href="{{ route('cart') }}" class="relative rounded-full p-2 hover:bg-white/5" aria-label="Cart ({{ $count }} items)">
    <x-lx-icon name="bag" class="size-5" />
    @if ($count > 0)
        <span class="absolute -top-0.5 -right-0.5 grid min-w-[18px] place-items-center rounded-full bg-gold-500 px-1 text-[10px] leading-[18px] font-bold text-ink-950">{{ $count > 99 ? '99+' : $count }}</span>
    @endif
</a>
