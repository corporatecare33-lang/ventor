@php
    // Skip malformed entries (no title / non-string points) so a bad admin edit cannot break the homepage.
    $items = collect($section->content['items'] ?? [])->filter(fn ($item) => is_array($item) && filled($item['title'] ?? null))->values();
    $points = collect($section->content['points'] ?? [])->filter(fn ($point) => is_string($point) && filled($point))->values();
    $buttonLink = ($section->content['button_link'] ?? null) ?: route('tap');
    $buttonText = ($section->content['button_text'] ?? null) ?: 'Learn more about LeatherX Tap';
@endphp
<section class="bg-ink-950 text-white" aria-labelledby="tap-title">
    {{-- Compact Ventro-style band; the bottom padding stays short because the fade band below adds the gap. --}}
    <div class="container-wide pt-14 pb-8 text-center lg:pt-16 lg:pb-10">
        <div class="reveal mx-auto max-w-2xl">
            {{-- A short label rather than the product name, which the hero directly above already shows. --}}
            <p class="pill-label border-brass-300/40 text-brass-300"><span class="size-1.5 rounded-full bg-brass-300" aria-hidden="true"></span>Tap to share</p>
            <h2 id="tap-title" class="mt-4 font-sans text-[clamp(1.625rem,1.3rem+1.2vw,2.25rem)] leading-tight font-bold tracking-[-0.025em] text-balance text-white">{{ $section->title }}</h2>
            @if ($section->subtitle)
                <p class="mx-auto mt-3 max-w-lg text-[15px] leading-relaxed text-pretty text-white/60">{{ $section->subtitle }}</p>
            @endif
        </div>

        @if ($items->isNotEmpty())
            {{-- role="list" keeps list semantics in Safari/VoiceOver, which drops them when list-style is none --}}
            <ol role="list" data-anim="stagger" class="mx-auto mt-8 grid max-w-[54rem] gap-3 text-left md:grid-cols-3">
                @foreach ($items as $item)
                    {{-- Icon beside the text on phones, stacked on top from md up. The faint border keeps the
                         card outlined in forced-colours mode, where the translucent background is dropped. --}}
                    <li class="flex gap-4 rounded-card border border-white/[0.06] bg-white/[0.05] p-4 md:flex-col md:gap-0 lg:p-5">
                        <span class="inline-flex size-9 shrink-0 items-center justify-center rounded-full bg-brass-300/10 text-brass-300">
                            <x-lx-icon :name="($item['icon'] ?? null) ?: 'signal'" class="size-4" />
                        </span>
                        <div class="min-w-0 md:mt-3">
                            <h3 class="text-sm leading-snug font-semibold text-white">{{ $item['title'] }}</h3>
                            @if (filled($item['text'] ?? null))
                                <p class="mt-1 text-[13px] leading-relaxed text-pretty text-white/60">{{ $item['text'] }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        @endif

        @if ($points->isNotEmpty())
            <ul role="list" data-anim="stagger" class="mx-auto mt-6 flex max-w-3xl flex-wrap items-center justify-center gap-x-6 gap-y-2.5 text-xs text-white/65">
                @foreach ($points as $point)
                    <li class="inline-flex items-center gap-1.5">
                        <x-lx-icon name="check" class="size-3.5 shrink-0 text-brass-300" />{{ $point }}
                    </li>
                @endforeach
            </ul>
        @endif

        {{-- min-h-11 gives a 44px target; its own vertical slack already separates it from the points above. --}}
        <a href="{{ $buttonLink }}" class="group {{ $points->isNotEmpty() ? 'mt-3' : 'mt-6' }} inline-flex min-h-11 items-center gap-1.5 text-[13px] font-semibold text-brass-300 underline-offset-4 transition-colors hover:text-white hover:underline">
            {{ $buttonText }}
            <x-lx-icon name="arrow-right" class="size-3.5 transition-transform motion-safe:group-hover:translate-x-0.5" />
        </a>
    </div>

    {{-- Ventro-style fade from the dark band into the white page; the first collection title follows directly. --}}
    <div class="h-16 bg-linear-to-b from-ink-950 to-white sm:h-20" aria-hidden="true"></div>
</section>
