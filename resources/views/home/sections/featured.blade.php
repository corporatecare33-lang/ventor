@if ($featured->isNotEmpty())
    <section class="section-y bg-paper" aria-labelledby="featured-title">
        <div class="container-site">
            <div class="flex flex-wrap items-end justify-between gap-6">
                <x-ui.section-heading id="featured-title" :title="$section->title" :intro="$section->subtitle" />
                <a href="{{ route('shop') }}" class="link-arrow">View all products <x-lx-icon name="arrow-right" class="size-4" /></a>
            </div>
            <div class="mt-10 grid grid-cols-2 gap-x-4 gap-y-10 sm:gap-x-6 lg:grid-cols-4">
                @foreach ($featured as $product)
                    <x-product.card :product="$product" />
                @endforeach
            </div>
        </div>
    </section>
@endif
