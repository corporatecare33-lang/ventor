@if ($reviews->isNotEmpty())
    <section class="section-y border-t border-line" aria-labelledby="reviews-title">
        <div class="container-site">
            <div class="flex flex-wrap items-end justify-between gap-6">
                <x-ui.section-heading id="reviews-title" :title="$section->title" :intro="$section->subtitle" />
                <a href="{{ route('reviews.index') }}" class="link-arrow">Read all reviews <x-lx-icon name="arrow-right" class="size-4" /></a>
            </div>
            <div class="mt-10 grid gap-10 md:grid-cols-3 md:gap-8">
                @foreach ($reviews as $review)
                    <figure class="border-t border-line-strong pt-6">
                        <x-ui.rating :rating="$review->rating" />
                        <blockquote class="mt-4 font-display text-xl leading-snug text-ink-950">“{{ str($review->body)->limit(180) }}”</blockquote>
                        <figcaption class="mt-5 text-sm text-ink-600">
                            <span class="font-medium text-ink-950">{{ $review->name }}</span>@if ($review->is_verified_purchase) · Verified purchase @endif
                            @if ($review->product)<br><a href="{{ route('products.show', $review->product) }}" class="text-leather-700 underline underline-offset-4">{{ $review->product->name }}</a>@endif
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        </div>
    </section>
@endif
