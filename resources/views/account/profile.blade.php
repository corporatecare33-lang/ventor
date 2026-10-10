@extends('layouts.app')

@section('content')
<x-account-layout title="Profile & password">
    <div class="grid gap-12 lg:grid-cols-2">
        <form method="POST" action="{{ route('account.profile.update') }}" class="space-y-5" x-data="{ busy: false }" @submit="busy = true" novalidate>
            @csrf @method('PUT')
            <h2 class="font-display text-2xl text-ink-950">Personal details</h2>
            <x-form.input name="name" label="Full name" required :value="$user->name" autocomplete="name" />
            <x-form.input name="email" label="Email" type="email" required :value="$user->email" autocomplete="email" />
            <x-form.input name="phone" label="Mobile number" type="tel" required :value="$user->phone" inputmode="numeric" autocomplete="tel" />
            <x-form.submit label="Save changes" loading="Saving…" />
        </form>
        <form method="POST" action="{{ route('account.password.update') }}" class="space-y-5" x-data="{ busy: false }" @submit="busy = true" novalidate>
            @csrf @method('PUT')
            <h2 class="font-display text-2xl text-ink-950">Change password</h2>
            <x-form.input name="current_password" label="Current password" type="password" required autocomplete="current-password" />
            <x-form.input name="password" label="New password" type="password" required autocomplete="new-password" hint="At least 8 characters, with letters and numbers." />
            <x-form.input name="password_confirmation" label="Confirm new password" type="password" required autocomplete="new-password" />
            <x-form.submit label="Update password" loading="Updating…" />
        </form>
    </div>
</x-account-layout>
@endsection
