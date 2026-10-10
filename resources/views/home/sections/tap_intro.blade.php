<section class="section-y bg-ink-950 text-white" aria-labelledby="tap-title">
    <div class="container-site grid items-center gap-14 lg:grid-cols-12 lg:gap-8">
        <div class="lg:col-span-6">
            <x-ui.section-heading id="tap-title" eyebrow="LeatherX Tap" :title="$section->title" :intro="$section->subtitle" inverse />
            <ol class="mt-10 border-t border-white/15">
                @foreach ($section->content['items'] ?? [] as $item)
                    <li class="flex gap-5 border-b border-white/15 py-5">
                        <span class="font-display text-2xl leading-none text-brass-300" aria-hidden="true">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <div>
                            <h3 class="font-sans text-base font-medium text-white">{{ $item['title'] }}</h3>
                            @if (! empty($item['text']))<p class="mt-1 text-[15px] leading-relaxed text-white/70">{{ $item['text'] }}</p>@endif
                        </div>
                    </li>
                @endforeach
            </ol>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ $section->content['button_link'] ?? route('tap') }}" class="btn-inverse">{{ $section->content['button_text'] ?? 'How LeatherX Tap works' }}</a>
            </div>
        </div>

        {{-- Interface preview of a real Tap profile page (uses the store's own name, not a fictional person) --}}
        <figure class="lg:col-span-5 lg:col-start-8">
            <div class="mx-auto w-full max-w-[20rem] rounded-[2rem] border border-white/15 bg-ink-900 p-3" aria-hidden="true">
                <div class="overflow-hidden rounded-[1.5rem] bg-ink-950 px-5 pt-10 pb-6">
                    <div class="text-center">
                        <span class="mx-auto flex size-16 items-center justify-center rounded-full border border-brass-300/50 font-display text-2xl text-brass-300">{{ mb_substr(setting('site_name', 'LeatherX'), 0, 1) }}</span>
                        <p class="mt-4 font-display text-2xl font-semibold text-white">{{ setting('site_name', 'LeatherX') }}</p>
                        <p class="mt-1 text-xs text-white/60">Leather goods & NFC wallets</p>
                        <span class="mt-5 flex min-h-11 items-center justify-center gap-2 rounded-xs bg-paper text-sm font-medium text-ink-950"><x-lx-icon name="download" class="size-4" /> Save contact</span>
                    </div>
                    <ul class="mt-4 space-y-2 text-sm">
                        @foreach ([['phone', 'Call'], ['chat', 'WhatsApp'], ['mail', 'Email'], ['globe', 'Website']] as [$icon, $label])
                            <li class="flex items-center gap-3 rounded-xs border border-white/10 px-4 py-3 text-white/85"><x-lx-icon :name="$icon" class="size-4 text-brass-300" /> {{ $label }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <figcaption class="mt-4 text-center text-[13px] text-white/60">What people see when they tap your wallet</figcaption>
        </figure>
    </div>
</section>
