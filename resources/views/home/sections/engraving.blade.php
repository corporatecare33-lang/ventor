@php
    // Skip malformed entries (no title / non-string points) so a bad admin edit cannot break the homepage.
    $items = collect($section->content['items'] ?? [])->filter(fn ($item) => is_array($item) && filled($item['title'] ?? null))->values();
    $points = collect($section->content['points'] ?? [])->filter(fn ($point) => is_string($point) && filled($point))->values();
    $buttonLink = ($section->content['button_link'] ?? null) ?: route('engraving');
    $buttonText = ($section->content['button_text'] ?? null) ?: 'Start your engraving';

    // Columns follow the number of steps so the row never ends with an empty slot.
    // Cards switch from phone rows to centred tiles at sm, so multi-step grids gain their columns at sm too.
    $stepColumns = match ($items->count()) {
        1 => 'mx-auto max-w-sm',
        2 => 'sm:grid-cols-2',
        3, 6 => 'sm:grid-cols-3',
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

    // On tablets the header stacks above the stats, so a full set of four sits in one row (md) instead of 2x2,
    // then returns to 2x2 beside the header at lg. Other counts keep 2x2, where an odd last box spans the row.
    $statColumns = count($stats) === 4 ? 'md:grid-cols-4 lg:grid-cols-2' : '';
@endphp
<section class="bg-white py-10 sm:py-12 lg:py-14" aria-labelledby="engraving-title">
    <div class="container-wide">
        <div class="rounded-card bg-ink-950 px-5 py-10 text-white sm:px-10 lg:px-14 lg:py-12">
            {{-- Like Ventro, the content sits in a narrower centred column inside the wide dark panel. --}}
            <div class="mx-auto max-w-[60rem]">
                <div class="grid items-center gap-8 lg:grid-cols-[1.15fr_1fr] lg:gap-12">
                    <div class="reveal">
                        <p class="pill-label border-brass-300/40 text-brass-300"><span class="size-1.5 rounded-full bg-brass-300" aria-hidden="true"></span>Personalisation</p>
                        <h2 id="engraving-title" class="mt-4 font-sans text-[clamp(1.625rem,1.3rem+1.2vw,2.25rem)] leading-tight font-bold tracking-[-0.025em] text-balance text-white">{{ $section->title }}</h2>
                        @if ($section->subtitle)
                            <p class="mt-3 max-w-xl text-[15px] leading-relaxed text-pretty text-white/60">{{ $section->subtitle }}</p>
                        @endif
                    </div>

                    <dl class="grid grid-cols-2 gap-3 {{ $statColumns }}">
                        @foreach ($stats as $stat)
                            {{-- Label (dt) comes first in the markup for screen readers but sits under the figure visually.
                                 An odd last box spans both columns so the grid never shows a hole.
                                 Tighter label tracking on phones keeps "Engravable" (one long uppercase word) inside a 320px-wide box. --}}
                            <div class="flex min-w-0 flex-col items-center justify-center rounded-card border border-white/[0.06] bg-white/[0.04] p-3.5 text-center odd:last:col-span-2 sm:p-4">
                                <dt class="order-last mt-1 text-[11px] leading-snug font-semibold tracking-[0.08em] text-white/60 uppercase sm:tracking-[0.12em]">{{ $stat['label'] }}</dt>
                                <dd class="text-xl leading-tight font-bold tracking-tight text-brass-300 tabular-nums sm:text-2xl">
                                    {{-- Words around a figure are set small so each box fits two-up on a 320px screen. --}}
                                    @isset($stat['prefix'])
                                        <span class="text-[13px] font-semibold tracking-normal text-white/65">{{ $stat['prefix'] }}</span>
                                    @endisset
                                    <span class="whitespace-nowrap">{{ $stat['value'] }}</span>
                                    @isset($stat['suffix'])
                                        <span class="text-[13px] font-semibold tracking-normal text-white/65">{{ $stat['suffix'] }}</span>
                                    @endisset
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </div>

                @if ($items->isNotEmpty())
                    {{-- role="list" keeps list semantics in Safari/VoiceOver, which drops them when list-style is none --}}
                    <ol role="list" class="mt-8 grid gap-2.5 {{ $stepColumns }}">
                        @foreach ($items as $item)
                            {{-- Same compact row as the Tap steps on phones (icon left), centred tiles from sm up. --}}
                            <li class="flex min-w-0 gap-4 rounded-card border border-white/[0.06] bg-white/[0.04] p-4 text-left sm:block sm:p-5 sm:text-center">
                                <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-brass-300/10 text-brass-300 sm:mx-auto">
                                    <x-lx-icon :name="($item['icon'] ?? null) ?: 'pen'" class="size-4" />
                                </span>
                                <div class="min-w-0">
                                    <h3 class="text-sm leading-snug font-semibold text-white sm:mt-3">{{ $item['title'] }}</h3>
                                    @if (filled($item['text'] ?? null))
                                        <p class="mt-1 text-[13px] leading-relaxed text-pretty text-white/60">{{ $item['text'] }}</p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @endif

                @if ($points->isNotEmpty())
                    <ul role="list" class="mx-auto mt-6 flex max-w-3xl flex-wrap items-center justify-center gap-x-6 gap-y-2.5 text-xs text-white/65">
                        @foreach ($points as $point)
                            <li class="inline-flex items-center gap-1.5">
                                <x-lx-icon name="check" class="size-3.5 shrink-0 text-brass-300" />{{ $point }}
                            </li>
                        @endforeach
                    </ul>
                @endif

                {{-- px-4 on phones keeps the default label on one line at 320px (the pill is 48px tall either way). --}}
                <div class="mt-6 text-center">
                    <a href="{{ $buttonLink }}" class="btn-brass px-4 sm:px-7">{{ $buttonText }} <x-lx-icon name="arrow-right" class="size-4 shrink-0" /></a>
                </div>
            </div>
        </div>
    </div>
</section>
