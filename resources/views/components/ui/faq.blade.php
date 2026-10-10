@props(['faqs'])
<div {{ $attributes->merge(['class' => 'border-t border-line']) }} x-data="{ open: null }">
    @foreach ($faqs as $faq)
        <div class="border-b border-line">
            <h3 class="font-sans text-base font-medium">
                <button type="button" id="faq-q-{{ $faq->id }}" aria-controls="faq-a-{{ $faq->id }}"
                        class="flex w-full items-start justify-between gap-6 py-5 text-left text-ink-950 hover:text-leather-700"
                        @click="open = open === {{ $faq->id }} ? null : {{ $faq->id }}" :aria-expanded="(open === {{ $faq->id }}).toString()">
                    <span>{{ $faq->question }}</span>
                    <x-lx-icon name="plus" class="mt-0.5 size-5 shrink-0 text-ink-500 transition-transform" ::class="open === {{ $faq->id }} && 'rotate-45'" />
                </button>
            </h3>
            <div id="faq-a-{{ $faq->id }}" role="region" aria-labelledby="faq-q-{{ $faq->id }}" x-cloak x-show="open === {{ $faq->id }}" x-collapse.duration.250ms>
                <p class="max-w-2xl pb-6 text-[15px] leading-relaxed text-ink-600">{{ $faq->answer }}</p>
            </div>
        </div>
    @endforeach
</div>
