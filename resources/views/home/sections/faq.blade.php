@if ($faqs->isNotEmpty())
    <section class="bg-cream-50 py-20 sm:py-24">
        <div class="container-lx grid gap-12 lg:grid-cols-[1fr_1.4fr]">
            <div>
                <x-section-heading eyebrow="FAQ" :title="$section->title" :subtitle="$section->subtitle" align="left" />
                <a href="{{ route('faq') }}" class="btn-outline mt-8">See all questions</a>
            </div>
            <x-faq-list :faqs="$faqs" />
        </div>
    </section>
@endif
