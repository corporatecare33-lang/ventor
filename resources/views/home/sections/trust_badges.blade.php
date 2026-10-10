<section class="relative z-10 -mt-px border-b border-ink-900/5 bg-white">
    <div class="container-lx grid grid-cols-2 gap-px py-2 lg:grid-cols-4">
        @foreach ($section->content['items'] ?? [] as $item)
            <div class="flex items-start gap-3 px-2 py-5 sm:px-4">
                <span class="grid size-10 shrink-0 place-items-center rounded-full bg-cream-100 text-leather-700">
                    <x-lx-icon :name="$item['icon'] ?? 'check'" class="size-5" />
                </span>
                <div>
                    <h3 class="font-sans text-sm font-semibold text-ink-950">{{ $item['title'] }}</h3>
                    @if (! empty($item['text']))<p class="mt-0.5 hidden text-xs leading-5 text-ink-600 sm:block">{{ $item['text'] }}</p>@endif
                </div>
            </div>
        @endforeach
    </div>
</section>
