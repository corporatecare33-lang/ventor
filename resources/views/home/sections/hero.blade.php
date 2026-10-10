<section class="bg-paper" aria-labelledby="hero-title">
    <div class="container-site grid items-center gap-12 py-12 sm:py-16 lg:grid-cols-12 lg:gap-8 lg:py-20">
        <div class="lg:col-span-7 xl:col-span-6">
            <p class="eyebrow">{{ $hero->eyebrow ?? 'Wallets · Card holders · Bags · NFC smart wallets' }}</p>
            <h1 id="hero-title" class="mt-4 text-[clamp(2.25rem,1.7rem+2.2vw,3.75rem)] leading-[1.06] text-ink-950">
                {{ $hero->title ?? 'Leather goods for everyday carry, made smarter with LeatherX Tap.' }}
            </h1>
            @if ($hero?->subtitle)
                <p class="lead mt-6 max-w-xl">{{ $hero->subtitle }}</p>
            @endif
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ $hero->button_link ?? route('shop') }}" class="btn-primary">{{ $hero->button_text ?? 'Shop the collection' }}</a>
                @if ($hero?->secondary_button_text)
                    <a href="{{ $hero->secondary_button_link }}" class="btn-secondary">{{ $hero->secondary_button_text }}</a>
                @endif
            </div>

            @if ($facts)
                <dl class="mt-12 grid grid-cols-2 gap-x-6 gap-y-6 border-t border-line-strong pt-6 sm:grid-cols-4">
                    @foreach ($facts as $fact)
                        <div>
                            <dt class="label-caps text-ink-500">{{ $fact['label'] }}</dt>
                            <dd class="mt-2 text-[15px] font-medium text-ink-950">{{ $fact['value'] }}</dd>
                            <dd class="mt-0.5 text-[13px] leading-snug text-ink-500">{{ $fact['note'] }}</dd>
                        </div>
                    @endforeach
                </dl>
            @endif
        </div>

        <div class="lg:col-span-5 lg:col-start-8">
            @if ($hero && $hero->hasMedia('image'))
                <picture class="block aspect-[4/5] overflow-hidden rounded-sm bg-paper-200">
                    @if ($hero->hasMedia('mobile_image'))
                        <source media="(max-width: 767px)" srcset="{{ $hero->getFirstMediaUrl('mobile_image') }}">
                    @endif
                    <img src="{{ $hero->getFirstMediaUrl('image') }}" alt="{{ $hero->getFirstMedia('image')->getCustomProperty('alt', $hero->title) }}"
                         class="size-full object-cover" width="900" height="1125" fetchpriority="high" decoding="async">
                </picture>
            @elseif ($flagship)
                @php $heroColor = $flagship->primaryColor(); @endphp
                <figure>
                    <a href="{{ route('products.show', $flagship) }}" class="group relative block aspect-[4/5] overflow-hidden rounded-sm max-lg:aspect-[5/4]" tabindex="-1" aria-hidden="true">
                        <x-product.media :product="$flagship" eager sizes="(min-width: 1024px) 40vw, 90vw" class="transition-transform duration-700 group-hover:scale-[1.02]" />
                        @if ($heroColor && ! $flagship->getFirstMedia('images'))
                            <span class="absolute top-5 left-5 rounded-xs bg-paper/95 px-3 py-2 text-xs text-ink-700">
                                Shown in <span class="font-medium text-ink-950">{{ $heroColor->name }}</span>
                                @if ($flagship->leather_type)<span class="block text-ink-500">{{ $flagship->leather_type }}</span>@endif
                            </span>
                        @endif
                    </a>
                    <figcaption class="mt-4 flex items-start justify-between gap-4 border-t border-line-strong pt-4">
                        <div>
                            <p class="text-[13px] text-ink-500">{{ $flagship->category?->name }}</p>
                            <a href="{{ route('products.show', $flagship) }}" class="mt-0.5 block font-medium text-ink-950 underline-offset-4 hover:underline">{{ $flagship->name }}</a>
                            <x-product.swatches :swatches="$flagship->colorSwatches()" class="mt-2" />
                        </div>
                        <x-product.price :current="$flagship->currentPrice()" :regular="$flagship->hasDiscount() ? $flagship->regular_price : null" class="shrink-0 flex-col items-end" />
                    </figcaption>
                </figure>
            @endif
        </div>
    </div>
</section>
