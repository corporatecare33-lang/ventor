@props(['faqs'])
<div class="divide-y divide-ink-900/10 border-y border-ink-900/10" x-data="{ open: null }">
    @foreach ($faqs as $faq)
        <div>
            <h3>
                <button type="button" class="flex w-full items-center justify-between gap-6 py-5 text-left font-sans text-[15px] font-medium text-ink-950"
                        @click="open = open === {{ $faq->id }} ? null : {{ $faq->id }}" :aria-expanded="open === {{ $faq->id }}">
                    {{ $faq->question }}
                    <x-lx-icon name="plus" class="size-5 shrink-0 text-gold-600 transition" ::class="open === {{ $faq->id }} && 'rotate-45'" />
                </button>
            </h3>
            <div x-cloak x-show="open === {{ $faq->id }}" x-collapse.duration.200ms>
                <p class="pb-5 text-sm leading-6 text-ink-600">{{ $faq->answer }}</p>
            </div>
        </div>
    @endforeach
</div>
