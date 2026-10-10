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

        @if ($image = $section->getFirstMedia('image'))
            <figure class="lg:col-span-6 lg:col-start-7">
                <div class="aspect-[4/3] overflow-hidden rounded-sm">
                    <x-ui.picture :media="$image" :conversions="['mobile' => 800, 'desktop' => 1400]" sizes="(min-width: 1024px) 45vw, 100vw" width="1400" height="1050" />
                </div>
                <figcaption class="mt-3 text-[13px] text-ink-500">Engraving styles: {{ implode(', ', $fonts) }}.</figcaption>
            </figure>
        @endif
    </div>
</section>
