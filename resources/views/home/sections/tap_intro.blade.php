<section class="section-y bg-ink-950 text-white" aria-labelledby="tap-title">
    <div class="container-site grid items-center gap-14 lg:grid-cols-12 lg:gap-8">
        <div class="lg:col-span-6">
            <x-ui.section-heading id="tap-title" eyebrow="LeatherX Tap" :title="$section->title" :intro="$section->subtitle" inverse />
            <ol class="mt-10 border-t border-white/15">
                @foreach ($section->content['items'] ?? [] as $item)
                    <li class="flex gap-5 border-b border-white/15 py-5">
                        <span class="font-display text-2xl leading-none text-brass-300" aria-hidden="true">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <div>
                            <h3 class="font-sans text-base font-medium text-white">{{ $item['title'] }}</h3>
                            @if (! empty($item['text']))<p class="mt-1 text-[15px] leading-relaxed text-white/70">{{ $item['text'] }}</p>@endif
                        </div>
                    </li>
                @endforeach
            </ol>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ $section->content['button_link'] ?? route('tap') }}" class="btn-inverse">{{ $section->content['button_text'] ?? 'How LeatherX Tap works' }}</a>
            </div>
        </div>

        @if ($image = $section->getFirstMedia('image'))
            <figure class="lg:col-span-6 lg:col-start-7">
                <div class="aspect-[4/3] overflow-hidden rounded-sm">
                    <x-ui.picture :media="$image" :conversions="['mobile' => 800, 'desktop' => 1400]" sizes="(min-width: 1024px) 45vw, 100vw" width="1400" height="1050" />
                </div>
                <figcaption class="mt-3 text-[13px] text-white/60">No app or battery needed — the chip is read by the phone.</figcaption>
            </figure>
        @endif
    </div>
</section>
