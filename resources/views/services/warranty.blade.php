@extends('layouts.app')

@section('content')
<x-ui.page-header title="Warranty registration & claims" intro="Register a product or file a claim with your order number and the phone number used at checkout." :crumbs="['Warranty' => null]" />
<section class="container-site grid gap-12 py-12 lg:grid-cols-12 lg:py-16">
    <div class="space-y-8 lg:col-span-4">
        <div>
            <h2 class="font-display text-2xl text-ink-950">What is covered</h2>
            <p class="mt-2 text-[15px] leading-relaxed text-ink-600">Stitching, edges, zips, clasps and the NFC chip, under normal use, for the warranty period shown on each product page.</p>
        </div>
        <div>
            <h2 class="font-display text-2xl text-ink-950">Not covered</h2>
            <p class="mt-2 text-[15px] leading-relaxed text-ink-600">Natural wear, scratches, colour change, water or heat damage and misuse.</p>
        </div>
        <div>
            <h2 class="font-display text-2xl text-ink-950">How claims work</h2>
            <p class="mt-2 text-[15px] leading-relaxed text-ink-600">After you submit, our team may ask for photos and then repairs or replaces the item. Signed-in customers can follow the status under <a href="{{ route('account.warranty') }}" class="text-leather-700 underline underline-offset-4">Account → Warranty</a>.</p>
        </div>
    </div>
    <div class="lg:col-span-7 lg:col-start-6">
        <livewire:warranty-form />
    </div>
</section>
@endsection
