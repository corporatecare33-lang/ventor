@if ($reviews->isNotEmpty())
    <section class="py-20 sm:py-24">
        <div class="container-lx">
            <x-section-heading eyebrow="Reviews" :title="$section->title" :subtitle="$section->subtitle" />
            <div class="mt-12 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($reviews as $review)
                    <figure class="flex flex-col rounded-2xl border border-ink-900/8 bg-cream-50 p-6">
                        <x-stars :rating="$review->rating" />
                        <blockquote class="mt-4 flex-1 text-sm leading-6 text-ink-700">“{{ str($review->body)->limit(220) }}”</blockquote>
                        <figcaption class="mt-5 flex items-center justify-between gap-3 border-t border-ink-900/8 pt-4 text-xs">
                            <span class="font-semibold text-ink-950">{{ $review->name }}
                                @if ($review->is_verified_purchase)<span class="ml-1 font-normal text-emerald-700">· Verified</span>@endif
                            </span>
                            @if ($review->product)
                                <a href="{{ route('products.show', $review->product) }}" class="truncate text-ink-600 hover:text-gold-600">{{ $review->product->name }}</a>
                            @endif
                        </figcaption>
                    </figure>
                @endforeach
            </div>
            <div class="mt-10 text-center"><a href="{{ route('reviews.index') }}" class="btn-outline">Read all reviews</a></div>
        </div>
    </section>
@endif
