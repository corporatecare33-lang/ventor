@props(['icon' => 'document', 'title', 'text' => null])
<div {{ $attributes->merge(['class' => 'rounded-sm border border-dashed border-line-strong px-6 py-14 text-center']) }}>
    <x-lx-icon :name="$icon" class="mx-auto size-7 text-ink-500" />
    <p class="mt-4 font-display text-2xl font-semibold text-ink-950">{{ $title }}</p>
    @if ($text)<p class="mx-auto mt-2 max-w-sm text-sm text-ink-600">{{ $text }}</p>@endif
    @if ($slot->isNotEmpty())<div class="mt-6 flex flex-wrap justify-center gap-3">{{ $slot }}</div>@endif
</div>
