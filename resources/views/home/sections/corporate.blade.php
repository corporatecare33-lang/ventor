{{--
    Corporate gifting, in the slot Ventro uses for its partner-logo strip. LeatherX has no
    partner logos to show (and must not invent any), so this sells the service instead:
    swoosh title, the admin-edited offer as a check list with the quotation button, and the
    gift sets themselves beside it. Without gift sets the section photo takes their place;
    with neither, the list column centres on its own.
--}}
@php
    $gifts = $corporateProducts ?? collect();
    $image = $gifts->isEmpty() ? $section->getFirstMedia('image') : null;
    $hasAside = $gifts->isNotEmpty() || $image;
    $items = collect($section->content['items'] ?? [])->filter(fn ($item) => filled($item['title'] ?? null));
    $buttonText = ($section->content['button_text'] ?? null) ?: 'Request a quotation';
    $buttonLink = ($section->content['button_link'] ?? null) ?: route('corporate');
@endphp
<section class="py-10 sm:py-12 lg:py-14" aria-labelledby="corporate-title">
    <div class="container-wide">
        <header class="reveal text-center">
            {{-- Inline (not inline-block) so a wrapped title gets a swoosh under each line. --}}
            <h2 id="corporate-title" class="font-display text-[clamp(1.75rem,1.3rem+1.8vw,2.625rem)] leading-[1.15] font-bold tracking-[-0.04em] text-balance text-ink-950">
                <span class="underline-swoosh">{{ $section->title }}</span>
            </h2>
            @if ($section->subtitle)
                <p class="mx-auto mt-3 max-w-xl text-[15px] leading-relaxed text-pretty text-ink-600">{{ $section->subtitle }}</p>
            @endif
        </header>

        <div @class([
            'reveal mt-8 grid items-center gap-8 lg:mt-10',
            'md:grid-cols-2 md:gap-10 lg:gap-14' => $hasAside,
        ])>
            <div @class(['w-full', 'mx-auto max-w-xl' => ! $hasAside])>
                @if ($items->isNotEmpty())
                    <ul role="list" class="divide-y divide-line border-y border-line">
                        @foreach ($items as $item)
                            <li class="flex items-start gap-3.5 py-3.5">
                                <span class="grid size-7 shrink-0 place-items-center rounded-full bg-paper text-cognac-600">
                                    <x-lx-icon name="check" class="size-3.5" />
                                </span>
                                {{-- The non-breaking space keeps the dash with the title when the line wraps. --}}
                                <p class="min-w-0 pt-0.5 text-[15px] leading-relaxed wrap-break-word text-ink-600">
                                    <span class="font-semibold text-ink-950">{{ $item['title'] }}</span>@if (filled($item['text'] ?? null))&nbsp;— {{ $item['text'] }}@endif
                                </p>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <div @class(['flex flex-wrap items-center gap-x-6 gap-y-3', 'mt-6 sm:mt-8' => $items->isNotEmpty(), 'justify-center' => ! $hasAside])>
                    <a href="{{ $buttonLink }}" class="btn-primary">{{ $buttonText }}</a>
                    @if ($gifts->isNotEmpty())
                        <a href="{{ route('categories.show', 'corporate-gifts') }}" class="link-arrow min-h-11">
                            See all gift sets <x-lx-icon name="arrow-right" class="size-4" />
                        </a>
                    @endif
                </div>
            </div>

            @if ($gifts->isNotEmpty())
                {{--
                    Two sets fill the column 2-up; a lone set keeps a single card's width instead of
                    stretching. The category label is dropped: the section title already says it.
                --}}
                <ul role="list" @class([
                    'grid gap-3 sm:gap-4',
                    'grid-cols-2' => $gifts->count() > 1,
                    'mx-auto w-full max-w-80' => $gifts->count() === 1,
                ])>
                    @foreach ($gifts as $gift)
                        <li class="flex">
                            <x-product.card :product="$gift" :show-category="false" class="w-full"
                                            :sizes="$gifts->count() > 1 ? '(min-width: 768px) 22vw, 46vw' : '320px'" />
                        </li>
                    @endforeach
                </ul>
            @elseif ($image)
                <div class="aspect-[4/3] overflow-hidden rounded-card bg-paper">
                    <x-ui.picture :media="$image" :conversions="['mobile' => 800, 'desktop' => 1400]"
                                  sizes="(min-width: 1440px) 660px, (min-width: 768px) 46vw, 100vw" width="1400" height="1050" />
                </div>
            @endif
        </div>
    </div>
</section>
