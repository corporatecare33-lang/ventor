@if ($categories->isNotEmpty())
    <section class="section-y" aria-labelledby="categories-title">
        <div class="container-site grid gap-10 lg:grid-cols-12 lg:gap-8">
            <x-ui.section-heading id="categories-title" :title="$section->title" :intro="$section->subtitle" class="lg:col-span-4" />
            <ul class="grid border-t border-line sm:grid-cols-2 sm:gap-x-10 lg:col-span-8">
                @foreach ($categories as $category)
                    <li class="border-b border-line">
                        <a href="{{ route('categories.show', $category) }}" class="group flex items-center gap-4 py-5">
                            <span class="min-w-0 flex-1">
                                <span class="block font-display text-2xl leading-tight font-semibold text-ink-950 group-hover:text-leather-700">{{ $category->name }}</span>
                                @if ($category->tagline)
                                    <span class="mt-1 block truncate text-[13px] text-ink-500">{{ str_replace(' | ', ' · ', $category->tagline) }}</span>
                                @endif
                            </span>
                            <span class="text-[13px] text-ink-500">{{ $category->active_products_count }} {{ Str::plural('item', $category->active_products_count) }}</span>
                            <x-lx-icon name="arrow-right" class="size-4 text-ink-500 transition-transform group-hover:translate-x-1 group-hover:text-ink-950" />
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
@endif
