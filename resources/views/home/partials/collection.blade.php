{{--
    One homepage collection: centred title, the feature line derived from its products, a
    2-up / 4-up product grid (left-aligned when it holds fewer than four) and a link to the
    full category. Variant "lead" sets the title in ink with the cognac swoosh; with
    $afterFade it also tightens the top padding, as it then sits right under the dark
    LeatherX Tap section's fade.
--}}
@php
    $category = $collection['category'];
    $products = $collection['products'];
    $features = $collection['features'] ?? [];
    $isLead = ($variant ?? 'default') === 'lead';
    $tightTop = $isLead && ($afterFade ?? false);
    $titleId = 'collection-'.$category->slug.'-title';
    $total = (int) $category->active_products_count;
@endphp
<section aria-labelledby="{{ $titleId }}" @class([
    'bg-white',
    'pt-4 pb-14 sm:pt-6 sm:pb-16 lg:pt-8 lg:pb-20' => $tightTop,
    'py-14 sm:py-16 lg:py-20' => ! $tightTop,
    'border-t border-line' => $divider ?? false,
])>
    <div class="container-site">
        <header class="reveal text-center">
            <h2 id="{{ $titleId }}" @class(['title-collection', 'text-ink-950' => $isLead, 'text-cognac-500' => ! $isLead])>
                @if ($isLead)
                    <span class="inline-block underline-swoosh">{{ $category->name }}</span>
                @else
                    {{ $category->name }}
                @endif
            </h2>

            @if ($features)
                {{--
                    Reads like one line of text ("Item | Item | Item"); the list keeps the items distinct
                    for screen readers. The separator touches the label so a line never starts with "|".
                    Short items never break (no "2- / year warranty" on phones); long ones, such as two
                    leather names, may wrap below sm so they cannot overflow a 320px screen.
                --}}
                @php($separator = '<span class="mr-0.5 ml-1.5 text-ink-500/50" aria-hidden="true">|</span>')
                <ul role="list" class="mx-auto mt-5 max-w-3xl text-[15px] leading-relaxed text-balance text-ink-700 sm:mt-6">
                    @foreach ($features as $feature)
                        @php($length = mb_strlen($feature['label']))
                        <li class="inline">
                            @if ($feature['url'] ?? null)
                                <a href="{{ $feature['url'] }}" @class(['underline decoration-line-strong underline-offset-4 transition-colors hover:text-ink-950 hover:decoration-ink-950', 'whitespace-nowrap' => $length <= 28, 'sm:whitespace-nowrap' => $length > 28 && $length <= 48])>{{ $feature['label'] }}</a>{!! $loop->last ? '' : $separator !!}
                            @else
                                <span @class(['whitespace-nowrap' => $length <= 28, 'sm:whitespace-nowrap' => $length > 28 && $length <= 48])>{{ $feature['label'] }}</span>{!! $loop->last ? '' : $separator !!}
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </header>

        <ul role="list" class="mt-10 grid grid-cols-2 gap-3 sm:gap-4 lg:mt-12 lg:grid-cols-4 lg:gap-5">
            @foreach ($products as $product)
                <li class="flex">
                    <x-product.card :product="$product" class="w-full" sizes="(min-width: 1280px) 284px, (min-width: 1024px) 22vw, 46vw" />
                </li>
            @endforeach
        </ul>

        {{-- min-h-11 gives the lone link a 44px touch target; the underline stays on the text. --}}
        <p class="mt-6 text-center lg:mt-8">
            <a href="{{ route('categories.show', $category) }}" class="link-arrow min-h-11">
                {{ $total > $products->count() ? 'View all '.$total.' '.$category->name : 'Shop '.$category->name }}
                <x-lx-icon name="arrow-right" class="size-4" />
            </a>
        </p>
    </div>
</section>
