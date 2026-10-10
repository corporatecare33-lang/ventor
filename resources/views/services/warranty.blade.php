@extends('layouts.app')

@section('content')
<x-page-header eyebrow="Peace of mind" title="Warranty registration & claims" subtitle="Every LeatherX piece is covered against stitching, edge and hardware defects. Register your product or file a claim using your order number." :crumbs="['Warranty' => null]" />
<section class="container-lx grid gap-10 py-14 lg:grid-cols-[1fr_1.3fr]">
    <div class="space-y-6 text-sm leading-6 text-ink-600">
        <div><h2 class="font-display text-2xl font-semibold text-ink-950">What is covered</h2><p class="mt-2">Stitching, edge finishing, zips, clasps and NFC chip defects under normal use, for the period shown on your product.</p></div>
        <div><h2 class="font-display text-2xl font-semibold text-ink-950">Not covered</h2><p class="mt-2">Natural wear, scratches, colour change, water or heat damage, and misuse.</p></div>
        <div><h2 class="font-display text-2xl font-semibold text-ink-950">How claims work</h2><p class="mt-2">Submit the form. Our team may ask for photos on WhatsApp, then repair or replace the item. You can follow the status in your account.</p></div>
    </div>
    <livewire:warranty-form />
</section>
@endsection
