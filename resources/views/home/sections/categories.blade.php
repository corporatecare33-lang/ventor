@if ($categories->isNotEmpty())
    <section class="section-y" aria-labelledby="categories-title">
        <div class="container-site grid gap-10 lg:grid-cols-12 lg:gap-8">
            <x-ui.section-heading id="categories-title" :title="$section->title" :intro="$section->subtitle" class="lg:col-span-4" />
            <ul class="grid border-t border-line sm:grid-cols-2 sm:gap-x-10 lg:col-span-8">
                @foreach ($categories as $category)
                    <li class="min-w-0 border-b border-line">
                        <a href="{{ route('categories.show', $category) }}" class="group flex items-center gap-4 py-4">
                            @if ($thumb = $category->getFirstMedia('image'))
                                <span class="block size-16 shrink-0 overflow-hidden rounded-xs bg-paper sm:size-[4.5rem]">
                                    <img src="{{ $thumb->getAvailableUrl(['thumb']) }}" alt="" width="72" height="72" loading="lazy" decoding="async" class="size-full object-cover transition-transform duration-500 group-hover:scale-105">
                                </span>
                            @endif
                            <span class="min-w-0 flex-1">
                                <span class="block font-display text-2xl leading-tight font-semibold text-ink-950 group-hover:text-leather-700">{{ $category->name }}</span>
                                @if ($category->tagline)
                                    <span class="mt-1 block truncate text-[13px] text-ink-500">{{ str_replace(' | ', ' · ', $category->tagline) }}</span>
                                @endif
                            </span>
                            <span class="hidden text-[13px] text-ink-500 sm:inline">{{ $category->active_products_count }} {{ Str::plural('item', $category->active_products_count) }}</span>
                            <x-lx-icon name="arrow-right" class="size-4 text-ink-500 transition-transform group-hover:translate-x-1 group-hover:text-ink-950" />
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
@endif
