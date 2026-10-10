@php
    // Skip malformed entries (no title / non-string points) so a bad admin edit cannot break the homepage.
    $items = collect($section->content['items'] ?? [])->filter(fn ($item) => is_array($item) && filled($item['title'] ?? null))->values();
    $points = collect($section->content['points'] ?? [])->filter(fn ($point) => is_string($point) && filled($point))->values();
    $buttonLink = ($section->content['button_link'] ?? null) ?: route('engraving');
    $buttonText = ($section->content['button_text'] ?? null) ?: 'Start your engraving';

    // Columns follow the number of steps so the row never ends with an empty slot.
    $stepColumns = match ($items->count()) {
        1 => 'mx-auto max-w-sm',
        2 => 'sm:grid-cols-2',
        3, 6 => 'md:grid-cols-3',
        default => 'sm:grid-cols-2 lg:grid-cols-4',
    };

    // Every figure is computed from the catalogue; a box is shown only when its value exists.
    // The extra making time is the delivery policy: engraved orders need two to three extra working days.
    $stats = array_values(array_filter([
        ! empty($engraving['from']) ? ['prefix' => 'From', 'value' => $engraving['from'], 'label' => 'Per piece'] : null,
        ! empty($engraving['styles']) ? ['value' => $engraving['styles'], 'label' => 'Lettering styles'] : null,
        ! empty($engraving['pieces']) ? ['value' => $engraving['pieces'], 'label' => 'Engravable pieces'] : null,
        ['value' => '2–3', 'suffix' => 'days', 'label' => 'Extra making time'],
    ]));
@endphp
<section class="py-14 sm:py-16 lg:py-20 bg-white" aria-labelledby="engraving-title">
    <div class="container-site">
        <div class="rounded-[20px] bg-ink-950 px-5 py-10 text-white sm:px-10 lg:px-14 lg:py-14">
            {{-- Like Ventro, the content sits in a narrower centred column inside the wide dark panel. --}}
            <div class="mx-auto max-w-4xl">
                <div class="grid items-center gap-10 lg:grid-cols-2">
                    <div class="reveal">
                        <p class="pill-label"><span class="size-1.5 rounded-full bg-brass-300" aria-hidden="true"></span>Personalisation</p>
                        <h2 id="engraving-title" class="mt-5 font-sans text-[clamp(1.75rem,1.3rem+1.6vw,2.75rem)] leading-[1.08] font-bold tracking-[-0.03em] text-balance text-white">{{ $section->title }}</h2>
                        @if ($section->subtitle)
                            <p class="mt-4 max-w-xl text-[15px] leading-relaxed text-pretty text-white/65 sm:text-base">{{ $section->subtitle }}</p>
                        @endif
                    </div>

                    <dl class="grid grid-cols-2 gap-3">
                        @foreach ($stats as $stat)
                            {{-- Label (dt) comes first in the markup for screen readers but sits under the figure visually.
                                 An odd last box spans both columns so the grid never shows a hole.
                                 px-3 on phones keeps "Engravable" (one long uppercase word) inside a 320px-wide box. --}}
                            <div class="flex min-w-0 flex-col items-center justify-center rounded-card border border-white/10 bg-white/[0.04] px-3 py-4 text-center odd:last:col-span-2 sm:p-5">
                                <dt class="order-last mt-1.5 text-[11px] leading-snug font-semibold tracking-[0.12em] text-white/55 uppercase">{{ $stat['label'] }}</dt>
                                <dd class="text-2xl leading-tight font-bold tracking-tight text-brass-300 tabular-nums sm:text-3xl">
                                    {{-- Words around a figure are set small so each box fits two-up on a 320px screen. --}}
                                    @isset($stat['prefix'])
                                        <span class="text-[13px] font-semibold tracking-normal text-white/65 sm:text-sm">{{ $stat['prefix'] }}</span>
                                    @endisset
                                    <span class="whitespace-nowrap">{{ $stat['value'] }}</span>
                                    @isset($stat['suffix'])
                                        <span class="text-[13px] font-semibold tracking-normal text-white/65 sm:text-sm">{{ $stat['suffix'] }}</span>
                                    @endisset
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </div>

                @if ($items->isNotEmpty())
                    {{-- role="list" keeps list semantics in Safari/VoiceOver, which drops them when list-style is none --}}
                    <ol role="list" class="mt-10 grid gap-3 {{ $stepColumns }}">
                        @foreach ($items as $item)
                            <li class="min-w-0 rounded-card border border-white/10 bg-white/[0.03] p-5 text-center">
                                <span class="mx-auto flex size-10 items-center justify-center rounded-full bg-brass-300/10 text-brass-300 ring-1 ring-brass-300/30">
                                    <x-lx-icon :name="($item['icon'] ?? null) ?: 'pen'" class="size-[18px]" />
                                </span>
                                <h3 class="mt-4 text-sm leading-snug font-semibold text-white">{{ $item['title'] }}</h3>
                                @if (filled($item['text'] ?? null))
                                    <p class="mt-1.5 text-[13px] leading-relaxed text-pretty text-white/55">{{ $item['text'] }}</p>
                                @endif
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

                {{-- px-4 on phones keeps the default label on one line at 320px (the pill is 48px tall either way). --}}
                <div class="mt-8 text-center">
                    <a href="{{ $buttonLink }}" class="btn-brass px-4 sm:px-7">{{ $buttonText }} <x-lx-icon name="arrow-right" class="size-4 shrink-0" /></a>
                </div>
            </div>
        </div>
    </div>
</section>
