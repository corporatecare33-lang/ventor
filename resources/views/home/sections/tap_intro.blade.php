<section class="bg-ink-900 py-20 text-white sm:py-24">
    <div class="container-lx grid items-center gap-12 lg:grid-cols-2">
        <div class="relative order-2 mx-auto w-full max-w-md lg:order-1" aria-hidden="true">
            <div class="relative aspect-[4/5] overflow-hidden rounded-[32px] border border-white/10 placeholder-leather">
                <div class="absolute inset-x-10 top-10 bottom-28 rounded-3xl border border-dashed border-tan-300/20"></div>
                <div class="absolute right-8 bottom-8 left-8 rounded-2xl bg-white p-4 text-ink-900 shadow-2xl">
                    <div class="flex items-center gap-3">
                        <span class="grid size-11 place-items-center rounded-full bg-ink-900 font-display text-lg text-gold-400">A</span>
                        <div>
                            <p class="text-sm font-semibold">Your Name</p>
                            <p class="text-xs text-ink-600">Founder · Your Company</p>
                        </div>
                        <span class="ml-auto rounded-full bg-gold-500/15 px-2.5 py-1 text-[10px] font-semibold text-gold-600">TAPPED</span>
                    </div>
                    <div class="mt-3 grid grid-cols-4 gap-2 text-ink-600">
                        @foreach (['phone', 'mail', 'globe', 'chat'] as $icon)
                            <span class="grid h-9 place-items-center rounded-lg bg-cream-100"><x-lx-icon :name="$icon" class="size-4" /></span>
                        @endforeach
                    </div>
                </div>
                <div class="absolute top-8 right-8 grid size-14 place-items-center rounded-full bg-gold-500 text-ink-950 shadow-lg shadow-gold-500/30">
                    <x-lx-icon name="signal" class="size-7" />
                </div>
            </div>
        </div>
        <div class="order-1 lg:order-2">
            <x-section-heading eyebrow="LeatherX Tap" :title="$section->title" :subtitle="$section->subtitle" align="left" dark />
            <div class="mt-10 space-y-6">
                @foreach ($section->content['items'] ?? [] as $item)
                    <div class="flex gap-4">
                        <span class="grid size-11 shrink-0 place-items-center rounded-full border border-gold-500/30 text-gold-400"><x-lx-icon :name="$item['icon'] ?? 'check'" class="size-5" /></span>
                        <div>
                            <h3 class="font-sans text-[15px] font-semibold">{{ $item['title'] }}</h3>
                            <p class="mt-1 text-sm leading-6 text-white/60">{{ $item['text'] ?? '' }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
            @if (! empty($section->content['button_text']))
                <a href="{{ $section->content['button_link'] ?? route('tap') }}" class="btn-gold mt-10">{{ $section->content['button_text'] }} <x-lx-icon name="arrow-right" class="size-4" /></a>
            @endif
        </div>
    </div>
</section>
