@php
    // Skip malformed entries (no title / non-string points) so a bad admin edit cannot break the homepage.
    $items = collect($section->content['items'] ?? [])->filter(fn ($item) => is_array($item) && filled($item['title'] ?? null))->values();
    $points = collect($section->content['points'] ?? [])->filter(fn ($point) => is_string($point) && filled($point))->values();
    $buttonLink = ($section->content['button_link'] ?? null) ?: route('tap');
    $buttonText = ($section->content['button_text'] ?? null) ?: 'Learn more about LeatherX Tap';
@endphp
<section class="bg-ink-950 text-white" aria-labelledby="tap-title">
    {{-- Bottom padding is kept short: the fade band below provides the gap to the next section. --}}
    <div class="container-site section-y pb-10 text-center sm:pb-12">
        <div class="reveal mx-auto max-w-2xl">
            <p class="pill-label"><span class="size-1.5 rounded-full bg-brass-300" aria-hidden="true"></span>LeatherX Tap™</p>
            <h2 id="tap-title" class="mt-5 font-sans text-[clamp(1.875rem,1.3rem+2vw,2.75rem)] leading-[1.08] font-bold tracking-[-0.03em] text-balance text-white">{{ $section->title }}</h2>
            @if ($section->subtitle)
                <p class="mx-auto mt-4 max-w-xl text-[15px] leading-relaxed text-pretty text-white/65 sm:text-base">{{ $section->subtitle }}</p>
            @endif
        </div>

        @if ($items->isNotEmpty())
            {{-- role="list" keeps list semantics in Safari/VoiceOver, which drops them when list-style is none --}}
            <ol role="list" class="mx-auto mt-10 grid max-w-4xl gap-3 text-left sm:mt-12 md:grid-cols-3 md:gap-4">
                @foreach ($items as $item)
                    {{-- Icon beside the text on phones, stacked on top from md up --}}
                    <li class="relative flex gap-4 rounded-card border border-white/10 bg-white/[0.04] p-5 md:flex-col md:gap-0 lg:p-6">
                        <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-full bg-brass-300/10 text-brass-300 ring-1 ring-brass-300/30">
                            <x-lx-icon :name="($item['icon'] ?? null) ?: 'signal'" class="size-[18px]" />
                        </span>
                        <span class="absolute top-5 right-5 hidden text-[11px] font-semibold tracking-[0.14em] text-white/50 tabular-nums md:block lg:top-6 lg:right-6" aria-hidden="true">{{ sprintf('%02d', $loop->iteration) }}</span>
                        <div class="min-w-0 md:mt-5">
                            <h3 class="text-[15px] leading-snug font-semibold text-white">{{ $item['title'] }}</h3>
                            @if (! empty($item['text']))
                                <p class="mt-1.5 text-sm leading-relaxed text-white/60">{{ $item['text'] }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        @endif

        @if ($points->isNotEmpty())
            <ul role="list" class="mx-auto mt-8 flex max-w-3xl flex-wrap items-center justify-center gap-x-6 gap-y-2.5 text-xs text-white/65">
                @foreach ($points as $point)
                    <li class="inline-flex items-center gap-1.5">
                        <x-lx-icon name="check" class="size-3.5 shrink-0 text-brass-300" />{{ $point }}
                    </li>
                @endforeach
            </ul>
        @endif

        <a href="{{ $buttonLink }}" class="group {{ $points->isNotEmpty() ? 'mt-5' : 'mt-8' }} inline-flex min-h-11 items-center gap-1.5 text-[13px] font-semibold text-brass-300 underline-offset-4 transition-colors hover:text-white hover:underline">
            {{ $buttonText }}
            <x-lx-icon name="arrow-right" class="size-3.5 transition-transform motion-safe:group-hover:translate-x-0.5" />
        </a>
    </div>

    {{-- Ventro-style fade from the dark band into the white page; the first collection title follows directly. --}}
    <div class="h-20 bg-linear-to-b from-ink-950 to-white sm:h-28" aria-hidden="true"></div>
</section>
