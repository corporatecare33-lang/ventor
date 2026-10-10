@php
    $categoryNames = $filterCategories->pluck('name', 'slug');
    $colorNames = $filterColors->pluck('name', 'id');
    $chips = collect()
        ->merge(collect($categories)->map(fn ($slug) => ['label' => $categoryNames[$slug] ?? $slug, 'filter' => 'categories', 'value' => $slug]))
        ->merge(collect($colors)->map(fn ($id) => ['label' => $colorNames[(int) $id] ?? 'Colour', 'filter' => 'colors', 'value' => (string) $id]))
        ->merge(collect($features)->map(fn ($f) => ['label' => \App\Livewire\ProductListing::FEATURES[$f] ?? $f, 'filter' => 'features', 'value' => $f]))
        ->when($minPrice, fn ($c) => $c->push(['label' => 'From '.money($minPrice), 'filter' => 'minPrice', 'value' => '']))
        ->when($maxPrice, fn ($c) => $c->push(['label' => 'Up to '.money($maxPrice), 'filter' => 'maxPrice', 'value' => '']))
        ->when($inStock, fn ($c) => $c->push(['label' => 'In stock', 'filter' => 'inStock', 'value' => '']))
        ->when($q !== '', fn ($c) => $c->push(['label' => '“'.$q.'”', 'filter' => 'q', 'value' => '']));
@endphp
<section class="container-site py-8 lg:py-12" x-data="{ filters: false }" aria-labelledby="products-heading">
    <h2 id="products-heading" class="sr-only">Products</h2>

    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-line pb-4">
        <p class="text-sm text-ink-600" role="status" aria-live="polite">
            <span class="font-medium text-ink-950">{{ $products->total() }}</span> {{ Str::plural('product', $products->total()) }}
        </p>
        <div class="flex items-center gap-2">
            <button type="button" @click="filters = true" class="btn-secondary btn-sm lg:hidden" aria-controls="product-filters" :aria-expanded="filters.toString()">
                <x-lx-icon name="filters" class="size-4" /> Filter
                @if ($activeFilterCount)<span class="rounded-full bg-ink-950 px-1.5 text-[11px] text-white">{{ $activeFilterCount }}</span>@endif
            </button>
            <label for="sort" class="sr-only">Sort products</label>
            <select id="sort" wire:model.live="sort" class="field min-h-10 w-auto py-1.5 text-sm">
                @foreach (\App\Livewire\ProductListing::SORTS as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @if ($chips->isNotEmpty())
        <ul class="mt-4 flex flex-wrap items-center gap-2" aria-label="Active filters">
            @foreach ($chips as $chip)
                <li>
                    <button type="button" wire:click="removeFilter(@js($chip['filter']), @js($chip['value']))" class="inline-flex min-h-8 items-center gap-1.5 rounded-xs border border-line-strong px-2.5 text-[13px] text-ink-700 hover:border-ink-950">
                        {{ $chip['label'] }} <x-lx-icon name="x" class="size-3.5" /><span class="sr-only">Remove filter</span>
                    </button>
                </li>
            @endforeach
            <li><button type="button" wire:click="clearFilters" class="ml-1 text-[13px] text-leather-700 underline underline-offset-4">Clear all</button></li>
        </ul>
    @endif

    <div class="mt-8 grid gap-10 lg:grid-cols-[15rem_1fr]">
        {{-- Filters: sidebar on desktop, focus-trapped drawer on smaller screens --}}
        <div id="product-filters" :class="filters ? 'fixed inset-0 z-50 flex' : 'hidden lg:block'" @keydown.escape.window="filters = false">
            <div class="absolute inset-0 bg-ink-950/50 lg:hidden" @click="filters = false"></div>
            <aside class="relative ml-auto flex h-full w-[min(22rem,88vw)] flex-col bg-white lg:h-auto lg:w-auto" aria-label="Product filters"
                   x-trap.noscroll.inert="filters && window.innerWidth < 1024">
                <div class="flex h-16 items-center justify-between border-b border-line px-5 lg:hidden">
                    <p class="font-display text-2xl font-semibold">Filter</p>
                    <button type="button" @click="filters = false" class="-mr-2 inline-flex size-11 items-center justify-center" aria-label="Close filters"><x-lx-icon name="x" class="size-6" /></button>
                </div>

                <div class="flex-1 space-y-8 overflow-y-auto px-5 py-6 lg:sticky lg:top-28 lg:overflow-visible lg:p-0">
                    <div>
                        <label for="filter-q" class="label-caps mb-2.5 block text-ink-950">Search</label>
                        <input id="filter-q" type="search" wire:model.live.debounce.400ms="q" class="field min-h-10 py-2" placeholder="Wallet, tote, card holder…">
                    </div>

                    @unless ($categoryId)
                        <fieldset>
                            <legend class="label-caps mb-3 text-ink-950">Category</legend>
                            <div class="space-y-2.5">
                                @foreach ($filterCategories as $cat)
                                    <label class="flex cursor-pointer items-center gap-3 text-[15px] text-ink-700">
                                        <input type="checkbox" wire:model.live="categories" value="{{ $cat->slug }}" class="check">
                                        <span class="flex-1">{{ $cat->name }}</span>
                                        <span class="text-[13px] text-ink-500">{{ $cat->active_products_count }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    @endunless

                    @if ($filterColors->isNotEmpty())
                        <fieldset>
                            <legend class="label-caps mb-3 text-ink-950">Colour</legend>
                            <div class="grid grid-cols-2 gap-x-3 gap-y-3">
                                @foreach ($filterColors as $color)
                                    <label class="flex cursor-pointer items-start gap-2.5 text-sm leading-tight text-ink-700">
                                        <input type="checkbox" wire:model.live="colors" value="{{ $color->id }}" class="peer sr-only">
                                        <span class="size-5 shrink-0 rounded-full ring-1 ring-line-strong ring-offset-2 peer-checked:ring-2 peer-checked:ring-ink-950 peer-focus-visible:outline-2 peer-focus-visible:outline-brass-500" style="background-color: {{ $color->value }}" aria-hidden="true"></span>
                                        <span class="pt-0.5 peer-checked:font-medium peer-checked:text-ink-950">{{ $color->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    @endif

                    <fieldset>
                        <legend class="label-caps mb-3 text-ink-950">Features</legend>
                        <div class="space-y-2.5">
                            @foreach (\App\Livewire\ProductListing::FEATURES as $value => $label)
                                <label class="flex cursor-pointer items-center gap-3 text-[15px] text-ink-700">
                                    <input type="checkbox" wire:model.live="features" value="{{ $value }}" class="check"> {{ $label }}
                                </label>
                            @endforeach
                            <label class="flex cursor-pointer items-center gap-3 text-[15px] text-ink-700">
                                <input type="checkbox" wire:model.live="inStock" class="check"> In stock only
                            </label>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend class="label-caps mb-3 text-ink-950">Price (৳)</legend>
                        <div class="flex items-center gap-2">
                            <label class="sr-only" for="min-price">Minimum price</label>
                            <input id="min-price" type="number" min="0" inputmode="numeric" wire:model.live.debounce.600ms="minPrice" class="field min-h-10 py-2" placeholder="Min">
                            <span class="text-ink-500" aria-hidden="true">–</span>
                            <label class="sr-only" for="max-price">Maximum price</label>
                            <input id="max-price" type="number" min="0" inputmode="numeric" wire:model.live.debounce.600ms="maxPrice" class="field min-h-10 py-2" placeholder="Max">
                        </div>
                    </fieldset>
                </div>

                <div class="border-t border-line p-5 lg:hidden">
                    <button type="button" @click="filters = false" class="btn-primary w-full">Show {{ $products->total() }} {{ Str::plural('product', $products->total()) }}</button>
                </div>
            </aside>
        </div>

        <div class="min-w-0">
            {{-- Skeleton while filters are applied --}}
            <div wire:loading.delay.grid class="hidden grid-cols-2 gap-x-4 gap-y-10 sm:gap-x-6 lg:grid-cols-3" aria-hidden="true">
                @for ($i = 0; $i < 6; $i++)<x-product.skeleton-card />@endfor
            </div>

            <div wire:loading.delay.remove>
                @if ($products->isEmpty())
                    <x-ui.empty-state icon="search" title="No products match these filters" text="Try removing a filter or searching for something else.">
                        <button type="button" wire:click="clearFilters" class="btn-secondary">Clear filters</button>
                    </x-ui.empty-state>
                @else
                    <div class="grid grid-cols-2 gap-x-4 gap-y-10 sm:gap-x-6 lg:grid-cols-3">
                        @foreach ($products as $product)
                            <x-product.card :product="$product" :eager="$loop->index < 2" wire:key="p-{{ $product->id }}"
                                sizes="(min-width: 1280px) 28vw, (min-width: 1024px) 30vw, 46vw" />
                        @endforeach
                    </div>
                @endif
                <div class="mt-14">{{ $products->onEachSide(1)->links('livewire.pagination') }}</div>
            </div>
        </div>
    </div>
</section>
