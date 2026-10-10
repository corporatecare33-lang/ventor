@extends('layouts.app')

@section('content')
<x-account-layout title="Warranty">
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('warranty') }}" class="btn-primary">Register a product</a>
        <a href="{{ route('warranty', ['type' => 'claim']) }}" class="btn-secondary">File a claim</a>
    </div>
    @if ($records->isEmpty())
        <x-ui.empty-state icon="badge" title="No warranty records yet" text="Register a delivered product to keep its warranty on file." class="mt-8" />
    @else
        <ul class="mt-8 divide-y divide-line border-y border-line">
            @foreach ($records as $record)
                <li class="flex flex-wrap items-start justify-between gap-4 py-5">
                    <div>
                        <p class="font-medium text-ink-950">{{ $record->product_name }}</p>
                        <p class="text-[13px] text-ink-500">{{ $record->type->getLabel() }} · Ref {{ $record->reference }} · Order {{ $record->order_number }}</p>
                        @if ($record->expires_at)<p class="mt-1 text-[13px] {{ $record->isActive() ? 'text-success' : 'text-danger' }}">{{ $record->isActive() ? 'Valid until' : 'Expired on' }} {{ $record->expires_at->format('j M Y') }}</p>@endif
                        @if ($record->admin_note)<p class="mt-2 border-l-2 border-brass-500 pl-3 text-sm text-ink-600">{{ $record->admin_note }}</p>@endif
                    </div>
                    <x-ui.status :status="$record->status" />
                </li>
            @endforeach
        </ul>
    @endif
</x-account-layout>
@endsection
