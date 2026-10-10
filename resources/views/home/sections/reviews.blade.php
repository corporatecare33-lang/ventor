{{--
    Ventro's "Verified Reviews" block: swoosh title, the store-wide rating, then one review at a
    time in a card with prev / next buttons and a counter underneath (no autoplay). Renders
    nothing until approved reviews exist — the numbers come from $reviewStats, never from copy.
    The slides share one grid cell and hide with `invisible`, so the card keeps the height of
    the longest review (shorter ones centre in it) and the buttons below never jump under the pointer.
--}}
@if ($reviews->isNotEmpty())
    @php
        $total = $reviews->count();
        $average = (float) ($reviewStats['average'] ?? 0);
        $reviewCount = (int) ($reviewStats['count'] ?? 0);
    @endphp
    <section class="py-14 sm:py-16 lg:py-20" aria-labelledby="reviews-title">
        <div class="container-site">
            <header class="reveal text-center">
                <h2 id="reviews-title" class="font-display text-[clamp(1.875rem,1.3rem+2.2vw,3rem)] leading-[1.05] font-bold tracking-[-0.04em] text-balance text-ink-950">
                    <span class="inline-block underline-swoosh">{{ $section->title }}</span>
                </h2>
                @if ($reviewCount > 0)
                    <p class="mt-4 flex flex-wrap items-center justify-center gap-x-2 gap-y-1 text-sm text-ink-600">
                        <x-ui.rating :rating="$average" />
                        <span>{{ number_format($average, 1) }} · {{ $reviewCount }} {{ str('review')->plural($reviewCount) }}</span>
                    </p>
                @endif
                @if ($section->subtitle)
                    <p class="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-pretty text-ink-600">{{ $section->subtitle }}</p>
                @endif
            </header>

            <div class="reveal mx-auto mt-10 max-w-2xl"
                 x-data="{ active: 0, total: {{ $total }}, go(step) { this.active = (this.active + step + this.total) % this.total } }"
                 role="region" aria-roledescription="carousel" aria-label="Customer reviews">
                {{-- The quote mark sits inside each slide so a short review centres as one block in the card. --}}
                <div id="home-reviews-slides" class="grid grid-cols-1 items-center rounded-card bg-paper px-6 py-10 text-center ring-1 ring-line sm:px-12" aria-live="polite">
                    @foreach ($reviews as $review)
                        {{-- The slide role goes on a wrapper: a <figure> with a <figcaption> may not take role="group". --}}
                        <div role="group" aria-roledescription="slide" aria-label="{{ $loop->iteration }} of {{ $total }}"
                             @class(['col-start-1 row-start-1 min-w-0 motion-safe:transition-[opacity,visibility] motion-safe:duration-300', 'invisible opacity-0' => ! $loop->first])
                             :class="{ 'invisible opacity-0': active !== {{ $loop->index }} }">
                            <figure>
                                <svg viewBox="0 0 40 30" fill="currentColor" class="mx-auto h-9 w-12 text-cognac-500" aria-hidden="true" focusable="false">
                                    <path d="M0 30V17.5C0 7.6 5.4 1.7 16 0l1.6 4.4C11.6 6 8.6 9.3 8.4 14H16v16H0Zm22 0V17.5C22 7.6 27.4 1.7 38 0l1.6 4.4C33.6 6 30.6 9.3 30.4 14H38v16H22Z" />
                                </svg>
                                <blockquote class="mt-6 text-lg leading-relaxed text-pretty wrap-break-word text-ink-950">
                                    <p>{{ str($review->body)->squish()->limit(260, '…', preserveWords: true) }}</p>
                                </blockquote>
                                <figcaption class="mt-6 flex flex-col items-center gap-2 text-sm">
                                    <x-ui.rating :rating="$review->rating" size="size-4" />
                                    <span class="flex max-w-full flex-wrap items-center justify-center gap-x-2 gap-y-1">
                                        <span class="min-w-0 font-semibold wrap-break-word text-ink-950">{{ $review->name }}</span>
                                        @if ($review->is_verified_purchase)
                                            <span class="inline-flex items-center gap-1 text-ink-600">
                                                <x-lx-icon name="check" class="size-3.5 text-cognac-600" /> Verified purchase
                                            </span>
                                        @endif
                                    </span>
                                    @if ($review->product)
                                        <a href="{{ route('products.show', $review->product) }}"
                                           class="inline-flex min-h-11 max-w-full items-center text-[13px] wrap-break-word text-ink-600 underline decoration-line-strong underline-offset-4 transition-colors hover:text-ink-950 hover:decoration-ink-950">{{ $review->product->name }}</a>
                                    @endif
                                </figcaption>
                            </figure>
                        </div>
                    @endforeach
                </div>

                @if ($total > 1)
                    <div class="mt-6 flex items-center justify-center gap-4">
                        <button type="button" @click="go(-1)" aria-label="Previous review" aria-controls="home-reviews-slides"
                                class="grid size-11 place-items-center rounded-full border border-line-strong text-ink-950 transition-colors hover:border-ink-950">
                            <x-lx-icon name="chevron-left" class="size-4" />
                        </button>
                        <p class="min-w-12 text-center text-sm text-ink-600 tabular-nums"><span x-text="active + 1">1</span> / {{ $total }}</p>
                        <button type="button" @click="go(1)" aria-label="Next review" aria-controls="home-reviews-slides"
                                class="grid size-11 place-items-center rounded-full border border-line-strong text-ink-950 transition-colors hover:border-ink-950">
                            <x-lx-icon name="chevron-right" class="size-4" />
                        </button>
                    </div>
                @endif

                <p class="mt-6 text-center">
                    <a href="{{ route('reviews.index') }}" class="link-arrow min-h-11">Read all reviews <x-lx-icon name="arrow-right" class="size-4" /></a>
                </p>
            </div>
        </div>
    </section>
@endif
