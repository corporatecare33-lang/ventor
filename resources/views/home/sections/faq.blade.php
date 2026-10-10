@if ($faqs->isNotEmpty())
    <section class="section-y border-t border-line" aria-labelledby="faq-title">
        <div class="container-site grid gap-10 lg:grid-cols-12 lg:gap-8">
            <div class="lg:col-span-4">
                <x-ui.section-heading id="faq-title" :title="$section->title" :intro="$section->subtitle" />
                <p class="mt-6 text-[15px] text-ink-600">Can't find an answer? <a href="{{ route('contact') }}" class="text-leather-700 underline underline-offset-4">Contact our team</a> or browse the <a href="{{ route('faq') }}" class="text-leather-700 underline underline-offset-4">full FAQ</a>.</p>
            </div>
            <x-ui.faq :faqs="$faqs" class="lg:col-span-7 lg:col-start-6" />
        </div>
    </section>
@endif
