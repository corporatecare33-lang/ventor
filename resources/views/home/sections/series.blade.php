<section class="bg-cream-50 py-20 sm:py-24">
    <div class="container-lx">
        <x-section-heading eyebrow="The collection" :title="$section->title" :subtitle="$section->subtitle" />
    </div>

    @foreach ($series as $category)
        <div class="container-lx mt-16 first-of-type:mt-14">
            <div class="mb-6 flex flex-wrap items-end justify-between gap-4 border-b border-ink-900/10 pb-5">
                <div>
                    <h3 class="font-display text-3xl font-semibold text-ink-950 sm:text-[34px]">{{ $category->name }}</h3>
                    @if ($category->tagline)
                        <p class="mt-1.5 text-xs tracking-wide text-ink-600">{{ str_replace('|', ' · ', $category->tagline) }}</p>
                    @endif
                </div>
                <a href="{{ route('categories.show', $category) }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-leather-700 hover:text-gold-600">
                    View all <x-lx-icon name="arrow-right" class="size-4" />
                </a>
            </div>
            <div class="scrollbar-none -mx-4 flex snap-x gap-4 overflow-x-auto px-4 pb-2 sm:mx-0 sm:grid sm:grid-cols-2 sm:overflow-visible sm:px-0 lg:grid-cols-4">
                @foreach ($category->featuredProducts as $product)
                    <div class="w-[72%] shrink-0 snap-start sm:w-auto">
                        <x-product-card :product="$product" />
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</section>
