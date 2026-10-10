@extends('layouts.app')

@section('content')
<x-account-layout title="LeatherX Tap profile">
    @if ($canCreate)
        <a href="{{ route('account.nfc.create') }}" class="btn-primary"><x-lx-icon name="plus" class="size-4" /> Create a profile</a>
    @elseif ($profiles->isEmpty())
        <x-ui.empty-state icon="signal" title="No LeatherX Tap product yet" text="Order any LeatherX Tap wallet or card holder to create your contact profile.">
            <a href="{{ route('tap') }}" class="btn-secondary">How LeatherX Tap works</a>
        </x-ui.empty-state>
    @endif

    @if ($profiles->isNotEmpty())
        <ul class="mt-8 space-y-4">
            @foreach ($profiles as $profile)
                <li class="flex flex-wrap items-center gap-6 rounded-sm border border-line p-5">
                    @if ($profile->status === \App\Enums\NfcProfileStatus::Active)
                        <a href="{{ route('nfc.qr', $profile) }}" download="{{ $profile->slug }}-qr.svg" class="block size-24 shrink-0 rounded-xs border border-line bg-white p-1">
                            <img src="{{ route('nfc.qr', $profile) }}" alt="QR code for {{ $profile->name }}'s profile — select to download" class="size-full" width="96" height="96" loading="lazy">
                        </a>
                    @endif
                    <div class="min-w-0 flex-1">
                        <p class="flex flex-wrap items-center gap-2 font-medium text-ink-950">{{ $profile->name }} <x-ui.status :status="$profile->status" /></p>
                        @if ($profile->designation || $profile->company)<p class="text-[13px] text-ink-500">{{ collect([$profile->designation, $profile->company])->filter()->implode(' · ') }}</p>@endif
                        <div class="mt-3 flex flex-wrap items-center gap-2" x-data="{ copied: false }">
                            <code class="max-w-full truncate rounded-xs bg-paper px-2 py-1 text-[13px]">{{ $profile->publicUrl() }}</code>
                            <button type="button" class="text-[13px] text-leather-700 underline underline-offset-4" @click="navigator.clipboard.writeText(@js($profile->publicUrl())); copied = true; setTimeout(() => copied = false, 2000)">
                                <span x-text="copied ? 'Link copied' : 'Copy link'">Copy link</span>
                            </button>
                        </div>
                        <p class="mt-2 text-[13px] text-ink-500">Opened {{ $profile->views_count }} {{ Str::plural('time', $profile->views_count) }}</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ $profile->publicUrl() }}" target="_blank" class="btn-secondary btn-sm">Preview<span class="sr-only"> (opens in a new tab)</span></a>
                        <a href="{{ route('account.nfc.edit', $profile) }}" class="btn-primary btn-sm">Edit</a>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</x-account-layout>
@endsection
