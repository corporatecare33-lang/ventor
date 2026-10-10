<section class="bg-cream-100 py-20 sm:py-24">
    <div class="container-lx">
        <x-section-heading eyebrow="Personalisation" :title="$section->title" :subtitle="$section->subtitle" />
        <ol class="mt-14 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($section->content['items'] ?? [] as $item)
                <li class="relative rounded-2xl border border-ink-900/8 bg-white p-6">
                    <span class="font-display text-5xl font-semibold text-tan-300">{{ $item['meta'] ?? str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    <x-lx-icon :name="$item['icon'] ?? 'sparkles'" class="absolute top-6 right-6 size-6 text-gold-500" />
                    <h3 class="mt-4 font-sans text-[15px] font-semibold text-ink-950">{{ $item['title'] }}</h3>
                    <p class="mt-2 text-sm leading-6 text-ink-600">{{ $item['text'] ?? '' }}</p>
                </li>
            @endforeach
        </ol>
        @if (! empty($section->content['button_text']))
            <div class="mt-10 text-center"><a href="{{ $section->content['button_link'] ?? route('engraving') }}" class="btn-dark">{{ $section->content['button_text'] }}</a></div>
        @endif
    </div>
</section>
