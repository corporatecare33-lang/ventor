<section class="section-y" aria-labelledby="corporate-title">
    <div class="container-site grid items-center gap-14 lg:grid-cols-12 lg:gap-8">
        <div class="lg:col-span-6">
            <x-ui.section-heading id="corporate-title" eyebrow="For teams and clients" :title="$section->title" :intro="$section->subtitle" />
            @if (! empty($section->content['items']))
                <ul class="mt-8 space-y-3">
                    @foreach ($section->content['items'] as $item)
                        <li class="flex gap-3 text-[15px] text-ink-700"><x-lx-icon name="check" class="mt-1 size-4 shrink-0 text-leather-600" /> <span>{{ $item['title'] }}@if (! empty($item['text']))<span class="text-ink-500"> — {{ $item['text'] }}</span>@endif</span></li>
                    @endforeach
                </ul>
            @endif
            <div class="mt-10 flex flex-wrap items-center gap-x-6 gap-y-4">
                <a href="{{ $section->content['button_link'] ?? route('corporate') }}" class="btn-primary">{{ $section->content['button_text'] ?? 'Request a quotation' }}</a>
                <a href="{{ route('categories.show', 'corporate-gifts') }}" class="link-arrow">See gift sets <x-lx-icon name="arrow-right" class="size-4" /></a>
            </div>
        </div>
        @if ($image = $section->getFirstMedia('image'))
            <figure class="lg:col-span-6 lg:col-start-7">
                <div class="aspect-[4/3] overflow-hidden rounded-sm">
                    <x-ui.picture :media="$image" :conversions="['mobile' => 800, 'desktop' => 1400]" sizes="(min-width: 1024px) 45vw, 100vw" width="1400" height="1050" />
                </div>
                @if ($corporateProduct)
                    <figcaption class="mt-3 text-[13px] text-ink-500">
                        Pictured: <a href="{{ route('products.show', $corporateProduct) }}" class="text-leather-700 underline underline-offset-4">{{ $corporateProduct->category?->name }}</a>
                    </figcaption>
                @endif
            </figure>
        @endif
    </div>
</section>
