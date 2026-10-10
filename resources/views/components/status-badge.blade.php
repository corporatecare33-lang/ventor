@props(['status'])
@php
    $classes = [
        'success' => 'bg-emerald-50 text-emerald-800', 'danger' => 'bg-red-50 text-red-800', 'warning' => 'bg-amber-50 text-amber-800',
        'info' => 'bg-sky-50 text-sky-800', 'primary' => 'bg-gold-500/15 text-gold-600', 'gray' => 'bg-ink-900/5 text-ink-700',
    ][$status->getColor()] ?? 'bg-ink-900/5 text-ink-700';
@endphp
<span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $classes }}">{{ $status->getLabel() }}</span>
