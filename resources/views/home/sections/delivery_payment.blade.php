<section class="border-y border-ink-900/8 bg-white py-16">
    <div class="container-lx">
        <h2 class="text-center font-display text-3xl font-semibold text-ink-950">{{ $section->title }}</h2>
        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($section->content['items'] ?? [] as $item)
                <div class="text-center">
                    <span class="mx-auto grid size-14 place-items-center rounded-full bg-ink-900 text-gold-400"><x-lx-icon :name="$item['icon'] ?? 'check'" class="size-6" /></span>
                    <h3 class="mt-4 font-sans text-sm font-semibold text-ink-950">{{ $item['title'] }}</h3>
                    <p class="mx-auto mt-1.5 max-w-60 text-xs leading-5 text-ink-600">{{ $item['text'] ?? '' }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
