{{--
    Two collections side by side (Ventro's "Genesis vs Owen" panel). Included from
    collections.blade.php with the `comparison` HomeSection and $comparison from
    App\Support\Comparison::build: two columns (category, heading, button text), the rows
    that differ and the "included in both" items. Everything is set in the admin under
    Homepage sections → comparison. "Yes" shows a check, "—" a muted dash; anything else is
    printed as is, in brass when the admin highlights that column. The table is fixed-layout
    and wraps (hyphenating long words), so it fits at 320px without a horizontal scroll region.
--}}
@if ($comparison)
    @php
        [$first, $second] = $comparison['columns'];
        $title = $section?->title ?: $first['label'].' vs '.$second['label'];
        // Narrower inner padding on phones keeps short labels on one line at 320px.
        $cell = 'px-2 py-3 sm:px-4 sm:py-3.5';
        $panel = 'rounded-card bg-white shadow-xs ring-1 ring-ink-950/5';
    @endphp
    <section aria-labelledby="comparison-title" class="bg-white pt-2 pb-2 sm:pt-4 sm:pb-4">
        <div class="container-wide">
            <div class="reveal rounded-card bg-mist px-3 py-10 text-center sm:px-8 lg:py-14">
                <p class="label-caps tracking-[0.2em] text-brass-600">{{ $comparison['eyebrow'] }}</p>
                <h2 id="comparison-title" class="mt-2.5 font-display text-2xl leading-tight font-bold tracking-tight text-balance text-ink-950 sm:text-[1.75rem]">{{ $title }}</h2>
                @if ($section?->subtitle)
                    <p class="mx-auto mt-2.5 max-w-2xl text-sm text-pretty text-ink-600">{{ $section->subtitle }}</p>
                @endif

                <div class="mx-auto mt-8 max-w-[56.25rem]">
                    <div class="{{ $panel }} relative overflow-hidden">
                        <table class="w-full table-fixed text-[13px] leading-snug wrap-break-word hyphens-auto sm:text-sm">
                            <caption class="sr-only">{{ $first['label'] }} compared with {{ $second['label'] }}</caption>
                            <colgroup>
                                <col class="w-[30%] sm:w-1/3">
                                <col>
                                <col>
                            </colgroup>
                            <thead class="border-b border-mist-200 bg-mist-200/60">
                                <tr>
                                    <th scope="col" class="{{ $cell }} text-left align-middle text-[11px] font-bold tracking-[0.06em] text-ink-950 uppercase sm:py-4 sm:text-[13px]">Feature</th>
                                    @foreach ([$first, $second] as $column)
                                        <th scope="col" class="{{ $cell }} text-center align-middle text-[11px] font-bold tracking-[0.06em] text-ink-950 uppercase sm:py-4 sm:text-[13px]">{{ $column['label'] }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody data-anim="stagger">
                                @foreach ($comparison['rows'] as $row)
                                    <tr @class(['border-t border-mist-200' => ! $loop->first])>
                                        <th scope="row" class="{{ $cell }} text-left align-middle font-semibold text-ink-950">{{ $row['label'] }}</th>
                                        @foreach ($row['values'] as $i => $value)
                                            <td @class([$cell, 'text-center align-middle', $row['highlight'] === $i ? 'font-semibold text-brass-600' : 'text-ink-800'])>
                                                @if ($value === 'Yes')
                                                    {{-- Icons are display:block (preflight), so mx-auto centres the check. --}}
                                                    <x-lx-icon name="check" class="mx-auto size-4 stroke-2 text-brass-600" /><span class="sr-only">Yes</span>
                                                @elseif ($value === '—')
                                                    <span class="text-ink-500/50" aria-hidden="true">—</span><span class="sr-only">Not available</span>
                                                @else
                                                    {{ $value }}
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($comparison['included'])
                        {{--
                            Each item is an inline-block that carries its own separator, so an item and its
                            bullet wrap as one unit (a bullet never starts a line).
                        --}}
                        <div class="{{ $panel }} mt-4 px-4 py-4 sm:mt-5 sm:px-6">
                            <p id="comparison-included" class="text-[11px] font-bold tracking-[0.12em] text-ink-500 uppercase">{{ $comparison['included_label'] }}</p>
                            <ul aria-labelledby="comparison-included" class="mt-1.5 text-[13px] leading-relaxed text-ink-600">
                                @foreach ($comparison['included'] as $item)
                                    <li class="inline-block">{{ $item }}@if (! $loop->last)<span class="pr-0.5 pl-1.5 text-ink-500" aria-hidden="true">•</span>@endif</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                {{-- Stacked full-width buttons on phones, a centred row from sm up. --}}
                <div class="mt-7 grid gap-3 sm:mt-8 sm:flex sm:flex-wrap sm:justify-center sm:gap-4">
                    <a href="{{ route('categories.show', $first['category']) }}" class="btn-primary min-h-11 px-8 text-center text-[11px] font-bold tracking-[0.08em] uppercase sm:text-[13px]">{{ $first['button'] }}</a>
                    <a href="{{ route('categories.show', $second['category']) }}" class="btn-secondary min-h-11 border-2 border-ink-950 bg-white px-8 text-center text-[11px] font-bold tracking-[0.08em] uppercase hover:bg-ink-950 hover:text-white sm:text-[13px]">{{ $second['button'] }}</a>
                </div>
            </div>
        </div>
    </section>
@endif
