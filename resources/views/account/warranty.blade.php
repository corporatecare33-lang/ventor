@extends('layouts.app')

@section('content')
<x-account-layout title="Warranty">
    <div class="mb-6 flex flex-wrap gap-3">
        <a href="{{ route('warranty') }}" class="btn-dark">Register a product</a>
        <a href="{{ route('warranty', ['type' => 'claim']) }}" class="btn-outline">File a claim</a>
    </div>
    <div class="card p-6">
        @forelse ($records as $record)
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-ink-900/8 py-4 first:pt-0 last:border-0 last:pb-0">
                <div>
                    <p class="font-semibold">{{ $record->product_name }}</p>
                    <p class="text-xs text-ink-600">{{ $record->type->getLabel() }} · Ref {{ $record->reference }} · Order {{ $record->order_number }}</p>
                    @if ($record->expires_at)<p class="mt-1 text-xs {{ $record->isActive() ? 'text-emerald-700' : 'text-red-700' }}">Warranty {{ $record->isActive() ? 'valid until' : 'expired on' }} {{ $record->expires_at->format('d M Y') }}</p>@endif
                    @if ($record->admin_note)<p class="mt-2 rounded-lg bg-cream-50 px-3 py-2 text-xs">{{ $record->admin_note }}</p>@endif
                </div>
                <x-status-badge :status="$record->status" />
            </div>
        @empty
            <p class="py-6 text-center text-sm text-ink-600">No warranty records yet.</p>
        @endforelse
    </div>
</x-account-layout>
@endsection
