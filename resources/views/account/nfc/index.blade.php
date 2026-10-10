@extends('layouts.app')

@section('content')
<x-account-layout title="LeatherX Tap profile">
    @if ($canCreate)
        <a href="{{ route('account.nfc.create') }}" class="btn-dark mb-6"><x-lx-icon name="plus" class="size-4" /> Create profile</a>
    @elseif ($profiles->isEmpty())
        <div class="card p-8 text-center">
            <x-lx-icon name="signal" class="mx-auto size-10 text-gold-600" />
            <p class="mt-4 font-display text-2xl font-semibold">No Tap product yet</p>
            <p class="mx-auto mt-2 max-w-sm text-sm text-ink-600">Order any LeatherX Tap wallet or card to create your digital profile.</p>
            <a href="{{ route('tap') }}" class="btn-gold mt-6">Explore LeatherX Tap</a>
        </div>
    @endif

    <div class="space-y-4">
        @foreach ($profiles as $profile)
            <div class="card flex flex-wrap items-center gap-5 p-6">
                <span class="grid size-14 place-items-center rounded-full bg-ink-900 font-display text-2xl text-gold-400">{{ mb_strtoupper(mb_substr($profile->name, 0, 1)) }}</span>
                <div class="min-w-0 flex-1">
                    <p class="font-semibold">{{ $profile->name }} <x-status-badge :status="$profile->status" /></p>
                    <p class="text-xs text-ink-600">{{ collect([$profile->designation, $profile->company])->filter()->implode(' · ') }}</p>
                    <div class="mt-2 flex items-center gap-2" x-data="{ copied: false }">
                        <code class="truncate rounded bg-cream-100 px-2 py-1 text-xs">{{ $profile->publicUrl() }}</code>
                        <button type="button" class="text-xs text-leather-700 underline" @click="navigator.clipboard.writeText(@js($profile->publicUrl())); copied = true; setTimeout(() => copied = false, 1500)" x-text="copied ? 'Copied' : 'Copy'"></button>
                    </div>
                    <p class="mt-1 text-xs text-ink-600">{{ $profile->views_count }} taps / views</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ $profile->publicUrl() }}" target="_blank" class="btn-outline px-4 py-2 text-xs">Preview</a>
                    <a href="{{ route('account.nfc.edit', $profile) }}" class="btn-dark px-4 py-2 text-xs">Edit</a>
                </div>
            </div>
        @endforeach
    </div>
</x-account-layout>
@endsection
