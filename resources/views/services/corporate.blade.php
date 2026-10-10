@extends('layouts.app')

@section('content')
<x-page-header eyebrow="Corporate gifting" :title="$section->title ?? 'Corporate gifting'" :subtitle="$section->subtitle ?? null" :crumbs="['Corporate' => null]" />
<section class="container-lx py-14">
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach ($section->content['items'] ?? [] as $item)
            <div class="card p-6"><x-lx-icon :name="$item['icon'] ?? 'gift'" class="size-6 text-gold-600" /><p class="mt-4 font-display text-3xl font-semibold">{{ $item['meta'] ?? '' }}</p><p class="mt-1 text-xs text-ink-600">{{ $item['title'] }}</p></div>
        @endforeach
    </div>

    <div class="mt-14 grid gap-10 lg:grid-cols-[1fr_1.3fr]">
        <div>
            <h2 class="section-title">Request a quotation</h2>
            <p class="mt-4 text-sm leading-6 text-ink-600">Tell us about your team, quantity and timeline. We'll suggest products, share samples and send a detailed quote within one working day.</p>
            @if ($products->isNotEmpty())
                <div class="mt-8 grid grid-cols-2 gap-3">
                    @foreach ($products->take(2) as $product)<x-product-card :product="$product" />@endforeach
                </div>
            @endif
        </div>
        <form method="POST" action="{{ route('corporate.store') }}" class="card grid gap-4 p-6 sm:grid-cols-2 sm:p-8">
            @csrf
            <x-input name="company_name" label="Company" required />
            <x-input name="contact_person" label="Contact person" required />
            <x-input name="designation" label="Designation" />
            <x-input name="phone" label="Phone" type="tel" required />
            <x-input name="email" label="Work email" type="email" required class="sm:col-span-2" />
            <x-input name="quantity" label="Quantity" type="number" min="10" required />
            <x-input name="budget" label="Budget per piece" placeholder="৳1,500 – ৳3,000" />
            <x-input name="product_interest" label="Products of interest" class="sm:col-span-2" placeholder="Wallets, card holders, gift boxes…" />
            <x-input name="deadline" label="Needed by" type="date" />
            <label class="flex items-center gap-2 self-end pb-3 text-sm text-ink-700"><input type="hidden" name="branding_required" value="0"><input type="checkbox" name="branding_required" value="1" class="size-4 accent-ink-900" @checked(old('branding_required'))> Logo engraving needed</label>
            <div class="sm:col-span-2">
                <label for="message" class="label">Details</label>
                <textarea id="message" name="message" rows="4" class="field">{{ old('message') }}</textarea>
            </div>
            <button class="btn-gold sm:col-span-2">Request quotation</button>
        </form>
    </div>
</section>
@endsection
