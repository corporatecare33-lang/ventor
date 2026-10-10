<div>
    @if ($product)
        <div class="fixed inset-0 z-50 flex items-end justify-center sm:items-center sm:p-6" role="dialog" aria-modal="true" aria-label="{{ $product->name }}"
             x-data x-init="document.body.classList.add('overflow-hidden')" x-on:keydown.escape.window="$wire.close()" x-destroy="document.body.classList.remove('overflow-hidden')">
            <div class="absolute inset-0 bg-ink-950/60" wire:click="close"></div>
            <div class="relative grid max-h-[92vh] w-full max-w-4xl overflow-y-auto rounded-t-3xl bg-white sm:grid-cols-2 sm:rounded-3xl">
                <button type="button" wire:click="close" class="absolute top-3 right-3 z-10 grid size-10 place-items-center rounded-full bg-white/90 shadow" aria-label="Close"><x-lx-icon name="x" class="size-5" /></button>
                <div class="aspect-square bg-cream-100 sm:aspect-auto">
                    <x-product-image :product="$product" conversion="large" />
                </div>
                <div class="p-6 sm:p-8">
                    @if ($product->category)<p class="eyebrow">{{ $product->category->name }}</p>@endif
                    <h2 class="mt-2 font-display text-3xl font-semibold text-ink-950">{{ $product->name }}</h2>
                    @if ($product->reviews_count)
                        <p class="mt-2 flex items-center gap-2 text-xs text-ink-600"><x-stars :rating="$product->rating_avg" /> {{ $product->reviews_count }} reviews</p>
                    @endif
                    <p class="mt-4 text-sm leading-6 text-ink-600">{{ $product->short_description }}</p>
                    <div class="mt-6">
                        <livewire:add-to-cart :product="$product" :compact="true" :key="'qv-'.$product->id" />
                    </div>
                    <a href="{{ route('products.show', $product) }}" class="mt-6 inline-flex items-center gap-1.5 text-sm font-medium text-leather-700 hover:text-gold-600">
                        Full details{{ $product->engraving_available ? ' & engraving' : '' }} <x-lx-icon name="arrow-right" class="size-4" />
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
