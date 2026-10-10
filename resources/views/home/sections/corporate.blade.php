<section class="relative overflow-hidden bg-ink-950 py-20 text-white sm:py-24">
    <div class="absolute inset-0 opacity-40 placeholder-leather"></div>
    <div class="container-lx relative grid items-center gap-12 lg:grid-cols-2">
        <div>
            <x-section-heading eyebrow="For teams & clients" :title="$section->title" :subtitle="$section->subtitle" align="left" dark />
            @if (! empty($section->content['button_text']))
                <a href="{{ $section->content['button_link'] ?? route('corporate') }}" class="btn-gold mt-9">{{ $section->content['button_text'] }} <x-lx-icon name="arrow-right" class="size-4" /></a>
            @endif
        </div>
        <div class="grid grid-cols-2 gap-4">
            @foreach ($section->content['items'] ?? [] as $item)
                <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-6 backdrop-blur">
                    <x-lx-icon :name="$item['icon'] ?? 'gift'" class="size-6 text-gold-400" />
                    <p class="mt-5 font-display text-3xl font-semibold text-white">{{ $item['meta'] ?? '' }}</p>
                    <p class="mt-1 text-xs leading-5 text-white/60">{{ $item['title'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
