@extends('layouts.app')

@section('content')
<x-ui.page-header title="Frequently asked questions" intro="Ordering, delivery, payment, LeatherX Tap, engraving, warranty and returns." :crumbs="['FAQ' => null]" />
<section class="container-site grid gap-12 py-12 lg:grid-cols-12 lg:py-16">
    <nav class="hidden lg:col-span-3 lg:block" aria-label="FAQ topics">
        <ul class="sticky top-28 space-y-1 border-l border-line">
            @foreach ($faqs->keys() as $category)
                <li><a href="#{{ Str::slug($category) }}" class="-ml-px block border-l border-transparent py-1.5 pl-4 text-[15px] text-ink-600 hover:border-ink-950 hover:text-ink-950">{{ $category }}</a></li>
            @endforeach
        </ul>
    </nav>
    <div class="space-y-14 lg:col-span-8 lg:col-start-5">
        @foreach ($faqs as $category => $items)
            <section id="{{ Str::slug($category) }}" class="scroll-mt-28" aria-labelledby="faq-{{ Str::slug($category) }}">
                <h2 id="faq-{{ Str::slug($category) }}" class="mb-2 font-display text-[1.875rem] text-ink-950">{{ $category }}</h2>
                <x-ui.faq :faqs="$items" />
            </section>
        @endforeach
        <div class="rounded-sm bg-paper p-8">
            <h2 class="font-display text-2xl text-ink-950">Still have a question?</h2>
            <p class="mt-2 text-[15px] text-ink-600">Send us a message and our team will get back to you.</p>
            <a href="{{ route('contact') }}" class="btn-primary mt-6">Contact us</a>
        </div>
    </div>
</section>
@endsection
