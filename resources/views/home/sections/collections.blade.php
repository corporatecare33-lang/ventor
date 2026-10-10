{{--
    The homepage's product collections, in Ventro's order: the lead collection (dark title with
    the cognac swoosh), the promise marquee, the side-by-side comparison, the remaining
    collections (cognac titles) and the "Smart additions" carousel. $section is the
    `collections` HomeSection; its title and subtitle head the additions carousel.
--}}
@php
    $lead = $collections->first();
    $others = $collections->slice(1)->values();
    $hasComparison = $comparison && $sections->has('comparison');

    // The lead's short top padding relies on the LeatherX Tap section's dark-to-white fade sitting
    // directly above it; if an admin hides or reorders that section, the lead gets full padding.
    $order = $sections->keys()->reject(fn ($key) => $key === 'comparison')->values();
    $position = $order->search('collections');
    $afterFade = $position !== false && $position > 0 && $order[$position - 1] === 'tap_intro';
@endphp

@if ($lead)
    @include('home.partials.collection', ['collection' => $lead, 'variant' => 'lead', 'afterFade' => $afterFade, 'divider' => false])
@endif

@if (! empty($promises))
    @include('home.partials.marquee', ['promises' => $promises])
@endif

@if ($hasComparison)
    @include('home.sections.comparison', ['section' => $sections['comparison'], 'comparison' => $comparison])
@endif

{{-- A hairline separates consecutive collections; the first one follows the marquee or the comparison panel instead. --}}
@foreach ($others as $collection)
    @include('home.partials.collection', ['collection' => $collection, 'variant' => 'default', 'divider' => ! $loop->first])
@endforeach

@if ($additions->isNotEmpty())
    @include('home.partials.additions', ['section' => $section, 'products' => $additions, 'divider' => $others->isNotEmpty()])
@endif
