@props(['type' => 'success'])
@php
    $styles = [
        'success' => ['bg-success-bg text-success border-success/20', 'check', 'status'],
        'error' => ['bg-danger-bg text-danger border-danger/20', 'x', 'alert'],
        'warning' => ['bg-warning-bg text-warning border-warning/20', 'clock', 'status'],
        'info' => ['bg-paper text-ink-700 border-line', 'document', 'status'],
    ][$type];
@endphp
<div role="{{ $styles[2] }}" {{ $attributes->merge(['class' => 'flex items-start gap-3 rounded-xs border px-4 py-3 text-sm '.$styles[0]]) }}>
    <x-lx-icon :name="$styles[1]" class="mt-0.5 size-4 shrink-0" />
    <div class="min-w-0">{{ $slot }}</div>
</div>
