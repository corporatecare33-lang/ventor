{{--
    Homepage FAQ: centred plain title (the swoosh is kept for the corporate and reviews titles
    above, so it is not overused), the accordion and a pointer to contact and the full FAQ.
--}}
@if ($faqs->isNotEmpty())
    <section class="py-10 sm:py-12 lg:py-14" aria-labelledby="faq-title">
        <div class="container-wide">
            <header class="reveal text-center">
                <h2 id="faq-title" class="font-display text-[clamp(1.75rem,1.3rem+1.8vw,2.625rem)] leading-[1.15] font-bold tracking-[-0.04em] text-balance text-ink-950">{{ $section->title }}</h2>
                @if ($section->subtitle)
                    <p class="mx-auto mt-3 max-w-xl text-[15px] leading-relaxed text-pretty text-ink-600">{{ $section->subtitle }}</p>
                @endif
            </header>

            <x-ui.faq :faqs="$faqs" class="mx-auto mt-6 max-w-3xl sm:mt-8" />

            <p class="mx-auto mt-6 max-w-3xl text-center text-[15px] leading-relaxed text-ink-600">
                Can't find your answer?
                <a href="{{ route('contact') }}" class="text-leather-700 underline underline-offset-4 transition-colors hover:text-ink-950">Contact our team</a>
                or read the <a href="{{ route('faq') }}" class="text-leather-700 underline underline-offset-4 transition-colors hover:text-ink-950">full FAQ</a>.
            </p>
        </div>
    </section>
@endif
