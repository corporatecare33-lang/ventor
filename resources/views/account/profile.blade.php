@extends('layouts.app')

@section('content')
<x-account-layout title="Profile & password">
    <div class="grid gap-6 lg:grid-cols-2">
        <form method="POST" action="{{ route('account.profile.update') }}" class="card space-y-4 p-6">
            @csrf @method('PUT')
            <h2 class="font-display text-2xl font-semibold">Personal details</h2>
            <x-input name="name" label="Full name" required :value="$user->name" />
            <x-input name="email" label="Email" type="email" required :value="$user->email" />
            <x-input name="phone" label="Mobile" type="tel" required :value="$user->phone" />
            <button class="btn-dark">Save changes</button>
        </form>
        <form method="POST" action="{{ route('account.password.update') }}" class="card space-y-4 p-6">
            @csrf @method('PUT')
            <h2 class="font-display text-2xl font-semibold">Change password</h2>
            <x-input name="current_password" label="Current password" type="password" required autocomplete="current-password" />
            <x-input name="password" label="New password" type="password" required autocomplete="new-password" />
            <x-input name="password_confirmation" label="Confirm new password" type="password" required autocomplete="new-password" />
            <button class="btn-dark">Update password</button>
        </form>
    </div>
</x-account-layout>
@endsection
