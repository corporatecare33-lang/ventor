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
            <a href="{{ $section->content['button_link'] ?? route('corporate') }}" class="btn-primary mt-10">{{ $section->content['button_text'] ?? 'Request a quotation' }}</a>
        </div>
        @if ($corporateProduct)
            <div class="lg:col-span-4 lg:col-start-9">
                <x-product.card :product="$corporateProduct" />
            </div>
        @endif
    </div>
</section>
