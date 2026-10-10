{{--
    Two collections side by side (Ventro's "Genesis vs Owen" panel). Included from
    collections.blade.php with the `comparison` HomeSection and $comparison: two categories
    and rows of string values computed from their products (see CollectionSummary::compare).
    "Yes" shows a check, "—" a muted dash; anything else is printed as is.
    The table is fixed-layout and wraps (hyphenating long words), so it fits at 320px
    without a horizontal scroll region. Renders nothing when there is nothing to compare.
--}}
@php
    [$first, $second] = array_pad(collect($comparison['collections'] ?? [])->values()->all(), 2, null);
    $rows = $comparison['rows'] ?? [];
@endphp

@if ($first && $second && $rows)
    @php
        $title = $section?->title ?: $first->name.' vs '.$second->name;
        // Outer cells keep room from the rounded edge; inner gutters stay narrow so 320px screens fit three columns.
        $pad = ['pr-2 pl-3 sm:pr-4 sm:pl-6', 'px-2 sm:px-4', 'pr-3 pl-2 sm:pr-6 sm:pl-4'];
    @endphp
    <section aria-labelledby="comparison-title" class="bg-white pt-2 sm:pt-4">
        <div class="container-site">
            <div class="reveal rounded-card bg-paper px-4 py-12 text-center sm:px-8 lg:py-16">
                <p class="label-caps text-cognac-600">Compare</p>
                <h2 id="comparison-title" class="mt-3 font-display text-[clamp(1.625rem,1.2rem+1.6vw,2.25rem)] leading-tight font-bold tracking-[-0.03em] text-balance text-ink-950">{{ $title }}</h2>
                @if ($section?->subtitle)
                    <p class="mx-auto mt-3 max-w-xl text-[15px] leading-relaxed text-pretty text-ink-600">{{ $section->subtitle }}</p>
                @endif

                <div class="relative mx-auto mt-8 max-w-3xl overflow-hidden rounded-card bg-white text-left ring-1 ring-line">
                    <table class="w-full table-fixed text-[13px] leading-snug wrap-break-word hyphens-auto sm:text-sm">
                        <caption class="sr-only">{{ $first->name }} compared with {{ $second->name }}</caption>
                        <colgroup>
                            <col class="w-[34%]">
                            <col>
                            <col>
                        </colgroup>
                        <thead class="bg-paper-200">
                            <tr>
                                <th scope="col" class="{{ $pad[0] }} py-3.5 text-left align-middle label-caps text-ink-600">Feature</th>
                                @foreach ([$first, $second] as $category)
                                    <th scope="col" class="p-0 text-left align-middle font-semibold">
                                        {{-- The link fills the cell (44px+ tall); the inset ink outline stays inside the rounded clip and contrasts with paper-200. --}}
                                        <a href="{{ route('categories.show', $category) }}" class="{{ $pad[$loop->iteration] }} block py-3.5 text-ink-950 underline-offset-4 hover:underline focus-visible:outline-ink-950 focus-visible:-outline-offset-2">{{ $category->name }}</a>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $row)
                                <tr class="border-t border-line">
                                    <th scope="row" class="{{ $pad[0] }} py-3 text-left align-top font-normal text-ink-600 sm:py-3.5">{{ $row['label'] }}</th>
                                    @foreach (array_pad(array_slice(array_values($row['values'] ?? []), 0, 2), 2, null) as $value)
                                        <td class="{{ $pad[$loop->iteration] }} py-3 align-top text-ink-950 sm:py-3.5">
                                            @if ($value === 'Yes')
                                                <x-lx-icon name="check" class="size-4.5 stroke-2 text-success" /><span class="sr-only">Yes</span>
                                            @elseif ($value === '—' || $value === '' || $value === null)
                                                <span class="text-ink-500" aria-hidden="true">—</span><span class="sr-only">Not available</span>
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

                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('categories.show', $first) }}" class="btn-primary btn-sm min-h-11">Shop {{ $first->name }}</a>
                    <a href="{{ route('categories.show', $second) }}" class="btn-secondary btn-sm min-h-11">Shop {{ $second->name }}</a>
                </div>
            </div>
        </div>
    </section>
@endif
