{{--
    One homepage collection: centred title, the feature line derived from its products and a
    2-up / 4-up product grid. From md, a dark tile fills the last row's empty columns and links
    to the full category (over the category's "tile" photo when one is uploaded); a text link
    appears only when the category holds more than is shown.
    Variant "lead" sets the title in ink with the cognac swoosh; with $afterFade it also
    tightens the top padding, as it then sits right under the dark LeatherX Tap section's fade.
--}}
@php
    $category = $collection['category'];
    $products = $collection['products'];
    $features = $collection['features'] ?? [];
    $isLead = ($variant ?? 'default') === 'lead';
    $tightTop = $isLead && ($afterFade ?? false);
    $titleId = 'collection-'.$category->slug.'-title';
    $total = (int) $category->active_products_count;
    $count = $products->count();

    // Columns left empty in the 4-up row; the category tile spans them (md and up only).
    $fill = $count % 4 ? 4 - $count % 4 : 0;
    // Tile tagline (escaped first): "·" stays with the item before it and hyphenated words such
    // as "RFID-blocking" never split, so no line starts with "·" or "blocking".
    $tagline = $category->tagline
        ? str_replace(' · ', '&nbsp;· ', preg_replace('/\S+-\S+/u', '<span class="whitespace-nowrap">$0</span>', e(str_replace(' | ', ' · ', $category->tagline))))
        : null;
@endphp
<section aria-labelledby="{{ $titleId }}" @class([
    'bg-white',
    'pt-4 pb-12 sm:pt-6 sm:pb-14 lg:pt-8 lg:pb-16' => $tightTop,
    'pt-10 pb-12 sm:pt-12 sm:pb-14 lg:pt-14 lg:pb-16' => ! $tightTop,
    'border-t border-line' => $divider ?? false,
])>
    <div class="container-wide">
        <header class="reveal text-center">
            <h2 id="{{ $titleId }}" @class(['title-collection', 'text-ink-950 leading-[1.15]' => $isLead, 'text-cognac-500' => ! $isLead])>
                @if ($isLead)
                    {{-- Inline (not inline-block) so a wrapped title gets a swoosh under each line; 1.15 leading keeps the first swoosh clear of the next line's caps. --}}
                    <span class="underline-swoosh">{{ $category->name }}</span>
                @else
                    {{ $category->name }}
                @endif
            </h2>

            @if ($features)
                {{--
                    Below lg: wrapped chips. From lg: one line of text ("Item | Item | Item"); the
                    separator touches the label so a line never starts with "|". The list keeps the
                    items distinct for screen readers. Short items never break; long ones (two
                    leather names) may wrap below sm so they cannot overflow a 320px screen.
                    Link chips are 36px tall; an invisible strip above and below lifts their hit
                    area to 44px (gap-y-2 keeps neighbouring rows' strips from overlapping).
                --}}
                @php($separator = '<span class="mr-0.5 ml-1.5 text-ink-500/50 max-lg:hidden" aria-hidden="true">|</span>')
                <ul role="list" data-anim="stagger" class="mx-auto mt-5 flex max-w-3xl flex-wrap justify-center gap-x-1.5 gap-y-2 text-[13px] text-ink-700 sm:mt-6 lg:block lg:text-[15px] lg:leading-relaxed lg:text-balance">
                    @foreach ($features as $feature)
                        @php($length = mb_strlen($feature['label']))
                        @php($wrap = ['whitespace-nowrap' => $length <= 28, 'sm:whitespace-nowrap' => $length > 28 && $length <= 48])
                        @php($chip = 'max-lg:inline-flex max-lg:min-h-9 max-lg:items-center max-lg:rounded-full max-lg:bg-mist max-lg:px-3')
                        <li class="flex lg:inline">
                            @if ($feature['url'] ?? null)
                                <a href="{{ $feature['url'] }}" @class([$chip, 'max-lg:relative max-lg:ring-1 max-lg:ring-line-strong max-lg:after:absolute max-lg:after:-inset-y-1 max-lg:after:inset-x-0', 'decoration-line-strong underline-offset-4 transition-colors hover:text-ink-950 hover:decoration-ink-950 lg:underline', ...$wrap])>{{ $feature['label'] }}</a>{!! $loop->last ? '' : $separator !!}
                            @else
                                <span @class([$chip, ...$wrap])>{{ $feature['label'] }}</span>{!! $loop->last ? '' : $separator !!}
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </header>

        <ul role="list" data-anim="cards" class="mt-8 grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-4 lg:mt-12 lg:gap-5">
            @foreach ($products as $product)
                {{-- An odd last card sits centred on its own row in the 2-up grid (half width minus half the gap). --}}
                <li @class([
                    'flex',
                    'max-md:col-span-2 max-md:w-[calc(50%-0.375rem)] max-md:justify-self-center sm:max-md:w-[calc(50%-0.5rem)]' => $loop->last && $count % 2,
                ])>
                    <x-product.card :product="$product" :show-category="false" class="w-full"
                                    sizes="(min-width: 1440px) 330px, (min-width: 768px) 23vw, 46vw" />
                </li>
            @endforeach

            @if ($fill)
                <li @class([
                    'hidden md:flex',
                    'md:col-span-1' => $fill === 1,
                    'md:col-span-2' => $fill === 2,
                    'md:col-span-3' => $fill === 3,
                ])>
                    {{--
                        The pill is the link ("Shop all {category}"); its ::after covers the tile so the whole
                        tile is clickable, while the tagline and count stay plain text. Wrapping all of it in
                        the link would give it a name that does not match its visible label.
                    --}}
                    @php($tile = $category->getFirstMedia('tile'))
                    <div @class([
                        'group/tile relative isolate flex w-full flex-col justify-end overflow-hidden rounded-card bg-ink-950 p-5 text-white ring-1 ring-white/10 transition-shadow duration-500 hover:shadow-pop lg:p-8',
                    ])>
                        @if ($tile)
                            {{--
                                Decorative (the tile already names the collection). Wide tiles use the complete
                                photograph as an editorial backdrop, retaining its negative space for the copy.
                                Layered gradients keep the text readable without introducing a hard split between
                                content and photography. A one-column tile keeps the product above the compact copy.
                            --}}
                            <div aria-hidden="true" class="absolute inset-0 -z-10 overflow-hidden">
                                <img src="{{ $tile->getAvailableUrl(['tile']) }}" alt="" loading="lazy" decoding="async"
                                     style="object-position: {{ $tile->getCustomProperty('focus', '50% 0%') }}"
                                     class="size-full object-cover transition-transform duration-700 ease-out-quart group-hover/tile:scale-[1.025]">
                                @if ($fill === 1)
                                    <div class="absolute inset-0 bg-linear-to-t from-ink-950 via-ink-950/35 to-transparent"></div>
                                @else
                                    <div class="absolute inset-0 bg-linear-to-r from-ink-950 from-0% via-ink-950/95 via-40% to-transparent to-78%"></div>
                                    <div class="absolute inset-0 bg-linear-to-t from-ink-950/45 via-transparent to-ink-950/10"></div>
                                @endif
                            </div>
                        @endif
                        <div @class(['relative z-10', 'max-w-lg' => $fill > 1])>
                            <p class="eyebrow-inverse">{{ $category->name }}</p>
                            @if ($tagline)
                                <p @class([
                                    'mt-2 leading-snug font-bold tracking-tight text-balance',
                                    'text-base' => $fill === 1,
                                    'text-lg lg:text-2xl' => $fill === 2,
                                    'text-xl lg:text-3xl' => $fill === 3,
                                ])>{!! $tagline !!}</p>
                            @endif
                            {{-- Wide tiles (short collections) carry the category description so they read as an editorial banner, not an empty slab. --}}
                            @if ($fill > 1 && $category->description)
                                <p class="mt-3 line-clamp-3 max-w-md text-[15px] leading-relaxed text-white/70">{{ $category->description }}</p>
                            @endif
                            @if ($total)
                                <p class="mt-2 text-sm font-medium text-white/65">{{ $total }} {{ str('piece')->plural($total) }}</p>
                            @endif
                            {{-- A one-column tile has ~124px for the pill at md: it goes full width with less padding below xl. --}}
                            <a href="{{ route('categories.show', $category) }}"
                               @class(['btn-brass btn-sm mt-5 self-start after:absolute after:inset-0 after:rounded-card', 'max-xl:flex max-xl:w-full max-xl:px-2' => $fill === 1])>
                                Shop all<span class="sr-only"> {{ $category->name }}</span>
                                <x-lx-icon name="arrow-right" class="size-4 transition-transform group-hover/tile:translate-x-1" />
                            </a>
                        </div>
                    </div>
                </li>
            @endif
        </ul>

        {{--
            min-h-11 gives the lone link a 44px touch target; the underline stays on the text.
            When every piece is already shown, phones still get a way into the category
            (the dark fill tile that links there is hidden below md).
        --}}
        @if ($total > $count)
            <p class="mt-5 text-center lg:mt-6">
                <a href="{{ route('categories.show', $category) }}" class="link-arrow min-h-11">
                    View all {{ $total }} {{ $category->name }}
                    <x-lx-icon name="arrow-right" class="size-4" />
                </a>
            </p>
        @else
            <p class="mt-5 text-center md:hidden">
                <a href="{{ route('categories.show', $category) }}" class="link-arrow min-h-11">
                    Shop {{ $category->name }}
                    <x-lx-icon name="arrow-right" class="size-4" />
                </a>
            </p>
        @endif
    </div>
</section>
