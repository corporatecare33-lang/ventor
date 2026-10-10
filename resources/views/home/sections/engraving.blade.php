<section class="section-y bg-paper" aria-labelledby="engraving-title">
    <div class="container-site grid items-start gap-14 lg:grid-cols-12 lg:gap-8">
        <div class="lg:col-span-6">
            <x-ui.section-heading id="engraving-title" eyebrow="Personalisation" :title="$section->title" :intro="$section->subtitle" />
            <ol class="mt-10 space-y-6">
                @foreach ($section->content['items'] ?? [] as $item)
                    <li class="grid grid-cols-[2.5rem_1fr] gap-x-4">
                        <span class="font-display text-2xl leading-none text-leather-600" aria-hidden="true">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <div>
                            <h3 class="font-sans text-base font-medium text-ink-950">{{ $item['title'] }}</h3>
                            @if (! empty($item['text']))<p class="mt-1 text-[15px] leading-relaxed text-ink-600">{{ $item['text'] }}</p>@endif
                        </div>
                    </li>
                @endforeach
            </ol>
            <a href="{{ $section->content['button_link'] ?? route('engraving') }}" class="btn-primary mt-10">{{ $section->content['button_text'] ?? 'Personalise a product' }}</a>
        </div>

        <figure class="lg:col-span-5 lg:col-start-8">
            <div class="material-tile aspect-[5/4] w-full rounded-sm" style="--tile: #5b3a24">
                <ul class="absolute inset-0 flex flex-col items-center justify-center gap-4 text-center text-[clamp(1.25rem,1rem+1vw,1.75rem)]" aria-hidden="true">
                    @foreach ($fonts as $font)
                        <li class="tracking-[0.08em] text-transparent [text-shadow:-1px_-1px_0_rgb(0_0_0/0.4),1px_1px_0_rgb(255_255_255/0.18)]
                            {{ ['Classic Serif' => 'font-display font-semibold', 'Modern Sans' => 'font-sans font-medium', 'Script' => 'font-display italic', 'Block Capitals' => 'font-sans font-bold uppercase tracking-[0.18em] text-[0.8em]'][$font] ?? '' }}">{{ $font }}</li>
                    @endforeach
                </ul>
            </div>
            <figcaption class="mt-3 text-[13px] text-ink-500">The four engraving styles available at checkout, shown debossed on leather.</figcaption>
        </figure>
    </div>
</section>
