@extends('layouts.app')

@section('content')
<x-ui.page-header eyebrow="Corporate gifting" :title="$section->title ?? 'Corporate gifting'" :intro="$section->subtitle ?? null" :crumbs="['Corporate gifting' => null]" />

<section class="section-y" aria-labelledby="offer-title">
    <div class="container-site grid gap-12 lg:grid-cols-12">
        <div class="lg:col-span-5">
            <x-ui.section-heading id="offer-title" title="What we can put together" />
            @if (! empty($section->content['items']))
                <ul class="mt-8 space-y-3">
                    @foreach ($section->content['items'] as $item)
                        <li class="flex gap-3 text-[15px] text-ink-700"><x-lx-icon name="check" class="mt-1 size-4 shrink-0 text-leather-600" />
                            <span>{{ $item['title'] }}@if (! empty($item['text']))<span class="text-ink-500"> — {{ $item['text'] }}</span>@endif</span></li>
                    @endforeach
                </ul>
            @endif
            <a href="#quote" class="btn-primary mt-10">Request a quotation</a>
        </div>
        @if ($image = $section?->getFirstMedia('image'))
            <figure class="lg:col-span-6 lg:col-start-7">
                <div class="aspect-[4/3] overflow-hidden rounded-sm">
                    <x-ui.picture :media="$image" :conversions="['mobile' => 800, 'desktop' => 1400]" sizes="(min-width: 1024px) 45vw, 100vw" width="1400" height="1050" eager />
                </div>
            </figure>
        @endif
    </div>
</section>

@if ($products->isNotEmpty())
    <section class="border-t border-line" aria-labelledby="sets-title">
        <div class="container-site py-14 lg:py-20">
            <x-ui.section-heading id="sets-title" title="Gift sets" intro="Ready-made sets you can order as they are, or use as a starting point for your quotation." />
            <div class="mt-10 grid grid-cols-2 gap-x-4 gap-y-10 sm:gap-x-6 lg:grid-cols-4">
                @foreach ($products as $product)<x-product.card :product="$product" />@endforeach
            </div>
        </div>
    </section>
@endif

<section id="quote" class="scroll-mt-24 border-t border-line bg-paper" aria-labelledby="quote-title">
    <div class="container-site grid gap-12 py-14 lg:grid-cols-12 lg:py-20">
        <div class="lg:col-span-4">
            <h2 id="quote-title" class="text-h2 text-ink-950">Request a quotation</h2>
            <p class="mt-4 text-[15px] leading-relaxed text-ink-600">Tell us the quantity, the products you have in mind and when you need them. We reply with suggestions, a written quotation and samples where needed.</p>
        </div>
        <form method="POST" action="{{ route('corporate.store') }}" class="grid gap-5 sm:grid-cols-2 lg:col-span-7 lg:col-start-6" x-data="{ busy: false }" @submit="busy = true" novalidate>
            @csrf
            @if ($errors->any())<x-ui.alert type="error" class="sm:col-span-2">Please check the highlighted fields.</x-ui.alert>@endif
            <x-form.input name="company_name" label="Company" required autocomplete="organization" />
            <x-form.input name="contact_person" label="Your name" required autocomplete="name" />
            <x-form.input name="designation" label="Designation" autocomplete="organization-title" />
            <x-form.input name="phone" label="Phone" type="tel" required autocomplete="tel" />
            <x-form.input name="email" label="Work email" type="email" required autocomplete="email" class="sm:col-span-2" />
            <x-form.input name="quantity" label="Quantity" type="number" min="10" required inputmode="numeric" hint="Minimum 10 pieces." />
            <x-form.input name="budget" label="Budget per piece (optional)" />
            <x-form.input name="product_interest" label="Products you are considering" class="sm:col-span-2" />
            <x-form.input name="deadline" label="Needed by" type="date" :min="now()->addDay()->toDateString()" />
            <x-form.checkbox name="branding_required" label="We need our logo engraved" class="self-end pb-3" />
            <x-form.textarea name="message" label="Anything else we should know?" rows="4" class="sm:col-span-2" />
            <x-form.submit label="Send request" loading="Sending…" class="sm:col-span-2 sm:justify-self-start" />
        </form>
    </div>
</section>
@endsection
