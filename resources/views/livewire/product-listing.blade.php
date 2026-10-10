<div class="container-lx py-10" x-data="{ filters: false }">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-ink-600">
            <span class="font-semibold text-ink-950">{{ $products->total() }}</span> products
            @if ($q !== '') for “<span class="text-ink-950">{{ $q }}</span>”@endif
        </p>
        <div class="flex items-center gap-2">
            <button type="button" @click="filters = true" class="btn-outline px-4 py-2 lg:hidden">
                <x-lx-icon name="filters" class="size-4" /> Filters @if ($activeFilterCount)<span class="rounded-full bg-ink-900 px-1.5 text-[10px] text-white">{{ $activeFilterCount }}</span>@endif
            </button>
            <label for="sort" class="sr-only">Sort by</label>
            <select id="sort" wire:model.live="sort" class="field w-auto py-2 pr-9">
                @foreach (\App\Livewire\ProductListing::SORTS as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid gap-8 lg:grid-cols-[250px_1fr]">
        {{-- Filters: sidebar on desktop, drawer on mobile --}}
        <div :class="filters ? 'fixed inset-0 z-50 flex' : 'hidden lg:block'">
            <div class="absolute inset-0 bg-black/50 lg:hidden" @click="filters = false"></div>
            <aside class="relative ml-auto h-full w-[86%] max-w-sm overflow-y-auto bg-white p-6 lg:static lg:w-auto lg:max-w-none lg:overflow-visible lg:p-0" aria-label="Product filters">
                <div class="mb-6 flex items-center justify-between lg:hidden">
                    <h2 class="font-display text-2xl font-semibold">Filters</h2>
                    <button type="button" @click="filters = false" aria-label="Close filters"><x-lx-icon name="x" class="size-6" /></button>
                </div>

                <div class="space-y-8 lg:sticky lg:top-24">
                    <div>
                        <label for="q" class="eyebrow mb-3 block text-ink-900">Search</label>
                        <input id="q" type="search" wire:model.live.debounce.400ms="q" class="field" placeholder="Wallet, bag…">
                    </div>

                    @unless ($categoryId)
                        <fieldset>
                            <legend class="eyebrow mb-3 text-ink-900">Category</legend>
                            <div class="space-y-2.5">
                                @foreach ($filterCategories as $cat)
                                    <label class="flex cursor-pointer items-center gap-3 text-sm text-ink-700">
                                        <input type="checkbox" wire:model.live="categories" value="{{ $cat->slug }}" class="size-4 rounded accent-ink-900">
                                        <span class="flex-1">{{ $cat->name }}</span>
                                        <span class="text-xs text-ink-600/60">{{ $cat->active_products_count }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    @endunless

                    @if ($filterColors->isNotEmpty())
                        <fieldset>
                            <legend class="eyebrow mb-3 text-ink-900">Color</legend>
                            <div class="flex flex-wrap gap-2.5">
                                @foreach ($filterColors as $color)
                                    <label class="cursor-pointer" title="{{ $color->name }}">
                                        <input type="checkbox" wire:model.live="colors" value="{{ $color->id }}" class="peer sr-only">
                                        <span class="block size-8 rounded-full ring-1 ring-ink-900/15 ring-offset-2 transition peer-checked:ring-2 peer-checked:ring-ink-900 peer-focus-visible:ring-gold-500" style="background-color: {{ $color->value }}"></span>
                                        <span class="sr-only">{{ $color->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    @endif

                    <fieldset>
                        <legend class="eyebrow mb-3 text-ink-900">Features</legend>
                        <div class="space-y-2.5">
                            @foreach (\App\Livewire\ProductListing::FEATURES as $value => $label)
                                <label class="flex cursor-pointer items-center gap-3 text-sm text-ink-700">
                                    <input type="checkbox" wire:model.live="features" value="{{ $value }}" class="size-4 rounded accent-ink-900"> {{ $label }}
                                </label>
                            @endforeach
                            <label class="flex cursor-pointer items-center gap-3 text-sm text-ink-700">
                                <input type="checkbox" wire:model.live="inStock" class="size-4 rounded accent-ink-900"> In stock only
                            </label>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend class="eyebrow mb-3 text-ink-900">Price (৳)</legend>
                        <div class="flex items-center gap-2">
                            <input type="number" min="0" wire:model.live.debounce.600ms="minPrice" class="field py-2" placeholder="Min" aria-label="Minimum price">
                            <span class="text-ink-600">–</span>
                            <input type="number" min="0" wire:model.live.debounce.600ms="maxPrice" class="field py-2" placeholder="Max" aria-label="Maximum price">
                        </div>
                    </fieldset>

                    @if ($activeFilterCount || $q !== '')
                        <button type="button" wire:click="clearFilters" class="text-sm font-medium text-leather-700 underline underline-offset-4">Clear all filters</button>
                    @endif

                    <button type="button" @click="filters = false" class="btn-dark w-full lg:hidden">Show {{ $products->total() }} products</button>
                </div>
            </aside>
        </div>

        <div>
            <div wire:loading.class="opacity-50" class="transition-opacity">
                @if ($products->isEmpty())
                    <div class="rounded-2xl border border-dashed border-ink-900/15 px-6 py-20 text-center">
                        <x-lx-icon name="search" class="mx-auto size-8 text-ink-600/40" />
                        <p class="mt-4 font-display text-2xl text-ink-950">No products found</p>
                        <p class="mt-2 text-sm text-ink-600">Try removing a filter or searching for something else.</p>
                        <button type="button" wire:click="clearFilters" class="btn-outline mt-6">Reset filters</button>
                    </div>
                @else
                    <div class="grid grid-cols-2 gap-3 sm:gap-5 md:grid-cols-3">
                        @foreach ($products as $product)
                            <x-product-card :product="$product" :eager="$loop->index < 3" wire:key="p-{{ $product->id }}" />
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="mt-12">{{ $products->onEachSide(1)->links('livewire.pagination') }}</div>
        </div>
    </div>
</div>
