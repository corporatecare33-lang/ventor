{{--
    Two collections side by side (Ventro's "Genesis vs Owen" panel). Included from
    collections.blade.php with the `comparison` HomeSection and $comparison: two categories
    and rows of string values computed from their products (see CollectionSummary::compare).
    Only real differences stay in the table; rows where both collections match move to an
    "Included in both" line under it. "Yes" shows a check, "—" a muted dash; anything else
    is printed as is. The table is fixed-layout and wraps (hyphenating long words), so it
    fits at 320px without a horizontal scroll region. Renders nothing when there is nothing
    to compare.
--}}
@php
    [$first, $second] = array_pad(collect($comparison['collections'] ?? [])->values()->all(), 2, null);
    $isMissing = fn ($value) => in_array($value, ['—', '', null], true);

    // Exactly two values per row; rows missing from both collections say nothing, so they are dropped.
    $rows = collect($comparison['rows'] ?? [])
        ->map(fn (array $row) => [
            'label' => $row['label'],
            'values' => array_pad(array_slice(array_values($row['values'] ?? []), 0, 2), 2, null),
        ])
        ->reject(fn (array $row) => $isMissing($row['values'][0]) && $isMissing($row['values'][1]));

    [$shared, $different] = $rows->partition(fn (array $row) => $row['values'][0] === $row['values'][1]);
@endphp

@if ($first && $second && $rows->isNotEmpty())
    @php
        $title = $section?->title ?: $first->name.' vs '.$second->name;
        $caps = 'label-caps tracking-[0.12em]';
        // Narrower inner padding on phones keeps "Engraving" and "LEATHERX" on one line at 320px.
        $cell = 'px-2 py-2.5 sm:px-3';
    @endphp
    <section aria-labelledby="comparison-title" class="bg-white pt-2 pb-2 sm:pt-4 sm:pb-4">
        <div class="container-wide">
            <div class="reveal rounded-card bg-mist px-3 py-10 text-center sm:px-8 lg:py-12">
                <p class="label-caps text-cognac-600">Compare</p>
                <h2 id="comparison-title" class="mt-2 font-display text-2xl leading-tight font-bold tracking-tight text-balance text-ink-950 sm:text-[1.75rem]">{{ $title }}</h2>
                @if ($section?->subtitle)
                    <p class="mx-auto mt-2 max-w-lg text-sm text-pretty text-ink-600">{{ $section->subtitle }}</p>
                @endif

                <div class="relative mx-auto mt-7 max-w-[44rem] overflow-hidden rounded-card bg-white ring-1 ring-line">
                    <table class="w-full table-fixed text-[13px] leading-snug wrap-break-word hyphens-auto">
                        <caption class="sr-only">{{ $first->name }} compared with {{ $second->name }}</caption>
                        <colgroup>
                            <col class="w-[30%] sm:w-[34%]">
                            <col>
                            <col>
                        </colgroup>
                        <thead class="bg-mist-200">
                            <tr>
                                <th scope="col" class="{{ $caps }} {{ $cell }} text-left align-middle text-ink-600">Feature</th>
                                @foreach ([$first, $second] as $category)
                                    <th scope="col" class="{{ $caps }} {{ $cell }} text-center align-middle text-ink-950">{{ $category->name }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        @if ($different->isNotEmpty())
                            <tbody data-anim="stagger">
                                @foreach ($different as $row)
                                    <tr class="border-t border-line">
                                        <th scope="row" class="{{ $cell }} text-left align-top text-[12px] font-normal text-ink-600 sm:text-[13px]">{{ $row['label'] }}</th>
                                        @foreach ($row['values'] as $value)
                                            <td class="{{ $cell }} text-center align-top text-ink-950">
                                                @if ($value === 'Yes')
                                                    {{-- Icons are display:block (preflight), so mx-auto centres the check. --}}
                                                    <x-lx-icon name="check" class="mx-auto size-4.5 stroke-2 text-success" /><span class="sr-only">Yes</span>
                                                @elseif ($isMissing($value))
                                                    <span class="text-ink-500" aria-hidden="true">—</span><span class="sr-only">Not available</span>
                                                @else
                                                    {{ $value }}
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        @endif
                    </table>

                    @if ($shared->isNotEmpty())
                        {{--
                            Each item is an inline-block that carries its own separator, so an item and its
                            dot wrap as one unit (a dot never starts a line). Dots are visual; commas are for
                            screen readers.
                        --}}
                        <p class="border-t border-line px-4 py-3 text-center text-[12px] leading-relaxed text-ink-600">
                            <span class="{{ $caps }} mb-1 block text-ink-500">Included in both<span class="sr-only">:</span></span>
                            @foreach ($shared as $row)
                                <span class="inline-block">{{ $row['values'][0] === 'Yes' ? $row['label'] : $row['label'].': '.$row['values'][0] }}@if (! $loop->last)<span class="sr-only">,</span><span class="pr-1 pl-1.5 text-ink-500" aria-hidden="true">·</span>@endif</span>
                            @endforeach
                        </p>
                    @endif
                </div>

                {{-- Stacked full-width buttons on phones, a centred row from sm up. --}}
                <div class="mt-7 grid gap-3 sm:flex sm:flex-wrap sm:justify-center">
                    <a href="{{ route('categories.show', $first) }}" class="btn-primary btn-sm min-h-11 text-[11px] font-semibold tracking-[0.1em] uppercase">Shop {{ $first->name }}</a>
                    <a href="{{ route('categories.show', $second) }}" class="btn-secondary btn-sm min-h-11 text-[11px] font-semibold tracking-[0.1em] uppercase">Shop {{ $second->name }}</a>
                </div>
            </div>
        </div>
    </section>
@endif
