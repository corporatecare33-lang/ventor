@extends('layouts.app')

@section('content')
<x-page-header title="Frequently asked questions" subtitle="Can't find what you're looking for? Our team is one message away." :crumbs="['FAQ' => null]" />
<section class="container-lx grid gap-12 py-14 lg:grid-cols-[220px_1fr]">
    <nav class="hidden lg:block" aria-label="FAQ categories">
        <ul class="sticky top-24 space-y-2 text-sm">
            @foreach ($faqs->keys() as $category)
                <li><a href="#{{ Str::slug($category) }}" class="text-ink-600 hover:text-gold-600">{{ $category }}</a></li>
            @endforeach
        </ul>
    </nav>
    <div class="space-y-12">
        @foreach ($faqs as $category => $items)
            <div id="{{ Str::slug($category) }}" class="scroll-mt-24">
                <h2 class="mb-2 font-display text-2xl font-semibold">{{ $category }}</h2>
                <x-faq-list :faqs="$items" />
            </div>
        @endforeach
        <div class="rounded-3xl bg-ink-900 p-8 text-white">
            <h2 class="font-display text-2xl font-semibold">Still have questions?</h2>
            <p class="mt-2 text-sm text-white/65">Call, WhatsApp or send us a message — we usually reply within a few hours.</p>
            <a href="{{ route('contact') }}" class="btn-gold mt-6">Contact us</a>
        </div>
    </div>
</section>
@endsection
