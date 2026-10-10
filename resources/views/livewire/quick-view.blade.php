<div x-data="{ open: false }" x-on:quick-view.window="open = true" x-on:keydown.escape.window="if (open) { open = false; $wire.close() }">
    <div x-cloak x-show="open" class="fixed inset-0 z-50 flex items-end justify-center sm:items-center sm:p-6" role="dialog" aria-modal="true" aria-labelledby="quick-view-title">
        <div class="absolute inset-0 bg-ink-950/60" @click="open = false; $wire.close()" x-show="open" x-transition.opacity></div>
        <div class="relative grid max-h-[92dvh] w-full max-w-4xl overflow-y-auto rounded-t-md bg-white sm:grid-cols-2 sm:rounded-sm" x-show="open" x-trap.noscroll="open"
             x-transition:enter="transition duration-200 ease-out-quart" x-transition:enter-start="translate-y-4 opacity-0">
            <button type="button" @click="open = false; $wire.close()" class="absolute top-2 right-2 z-10 inline-flex size-11 items-center justify-center rounded-full bg-white/90" aria-label="Close quick view">
                <x-lx-icon name="x" class="size-5" />
            </button>

            @if ($product)
                <div class="aspect-[4/5] bg-paper max-sm:aspect-[4/3]">
                    <x-product.media :product="$product" sizes="(min-width: 640px) 28rem, 100vw" />
                </div>
                <div class="p-6 sm:p-8">
                    @if ($product->category)<p class="text-[13px] text-ink-500">{{ $product->category->name }}</p>@endif
                    <h2 id="quick-view-title" class="mt-1 font-display text-[1.875rem] leading-tight text-ink-950">{{ $product->name }}</h2>
                    <p class="mt-3 text-[15px] leading-relaxed text-ink-600">{{ $product->short_description }}</p>
                    <div class="mt-6 border-t border-line pt-6">
                        <livewire:add-to-cart :product="$product" :compact="true" :key="'qv-'.$product->id" />
                    </div>
                    <a href="{{ route('products.show', $product) }}" class="link-arrow mt-6">View full details{{ $product->engraving_available ? ' and engraving' : '' }} <x-lx-icon name="arrow-right" class="size-4" /></a>
                </div>
            @else
                {{-- Loading skeleton while the product is fetched --}}
                <div class="skeleton aspect-[4/5] rounded-none max-sm:aspect-[4/3]"></div>
                <div class="space-y-4 p-6 sm:p-8" aria-hidden="true">
                    <div class="skeleton h-3 w-1/3"></div>
                    <h2 id="quick-view-title" class="sr-only">Loading product</h2>
                    <div class="skeleton h-8 w-3/4"></div>
                    <div class="skeleton h-16 w-full"></div>
                    <div class="skeleton h-12 w-full"></div>
                </div>
            @endif
        </div>
    </div>
</div>
