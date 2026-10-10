{{--
    Corporate gifting, in the slot Ventro uses for its partner-logo strip. LeatherX has no
    partner logos to show (and must not invent any), so this sells the service instead:
    swoosh title, the section photo and the admin-edited offer as a check list.
    Without a photo the list column centres on its own.
--}}
@php
    $image = $section->getFirstMedia('image');
    $items = collect($section->content['items'] ?? [])->filter(fn ($item) => filled($item['title'] ?? null));
    $buttonText = ($section->content['button_text'] ?? null) ?: 'Request a quotation';
    $buttonLink = ($section->content['button_link'] ?? null) ?: route('corporate');
@endphp
<section class="py-14 sm:py-16 lg:py-20" aria-labelledby="corporate-title">
    <div class="container-site">
        <header class="reveal text-center">
            <h2 id="corporate-title" class="font-display text-[clamp(1.875rem,1.3rem+2.2vw,3rem)] leading-[1.05] font-bold tracking-[-0.04em] text-balance text-ink-950">
                <span class="inline-block underline-swoosh">{{ $section->title }}</span>
            </h2>
            @if ($section->subtitle)
                <p class="mx-auto mt-4 max-w-xl text-[15px] leading-relaxed text-pretty text-ink-600">{{ $section->subtitle }}</p>
            @endif
        </header>

        <div @class([
            'reveal mt-10 grid items-center gap-10 lg:mt-14',
            'lg:grid-cols-2 lg:gap-14' => $image,
        ])>
            @if ($image)
                <div class="aspect-[4/3] overflow-hidden rounded-card bg-paper">
                    <x-ui.picture :media="$image" :conversions="['mobile' => 800, 'desktop' => 1400]"
                                  sizes="(min-width: 1280px) 592px, (min-width: 1024px) 46vw, 100vw" width="1400" height="1050" />
                </div>
            @endif

            <div @class(['w-full', 'mx-auto max-w-xl' => ! $image])>
                @if ($items->isNotEmpty())
                    <ul role="list" class="divide-y divide-line border-y border-line">
                        @foreach ($items as $item)
                            <li class="flex items-start gap-4 py-4">
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

                <div @class(['flex flex-wrap items-center gap-x-6 gap-y-4', 'mt-8' => $items->isNotEmpty(), 'justify-center' => ! $image])>
                    <a href="{{ $buttonLink }}" class="btn-primary">{{ $buttonText }}</a>
                    @if ($corporateProduct)
                        <a href="{{ route('categories.show', 'corporate-gifts') }}" class="link-arrow min-h-11">
                            See gift sets <x-lx-icon name="arrow-right" class="size-4" />
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
