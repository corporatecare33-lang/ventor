@props(['status'])
@php
    $tone = [
        'success' => 'bg-success-bg text-success', 'danger' => 'bg-danger-bg text-danger', 'warning' => 'bg-warning-bg text-warning',
        'info' => 'bg-paper text-ink-700', 'primary' => 'bg-paper-200 text-leather-700', 'gray' => 'bg-paper text-ink-600',
    ][$status->getColor()] ?? 'bg-paper text-ink-600';
@endphp
<span class="inline-flex items-center rounded-xs px-2 py-0.5 text-xs font-medium {{ $tone }}">{{ $status->getLabel() }}</span>
