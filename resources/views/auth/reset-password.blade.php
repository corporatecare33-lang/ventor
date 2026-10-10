@extends('layouts.app')

@section('content')
<x-auth.shell title="Choose a new password">
    <form method="POST" action="{{ route('password.update') }}" class="mt-8 space-y-5" x-data="{ busy: false }" @submit="busy = true" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <x-form.input name="email" label="Email" type="email" required :value="$email" autocomplete="email" />
        <x-form.input name="password" label="New password" type="password" required autocomplete="new-password" hint="At least 8 characters, with letters and numbers." />
        <x-form.input name="password_confirmation" label="Confirm new password" type="password" required autocomplete="new-password" />
        <x-form.submit label="Save new password" loading="Saving…" class="w-full" />
    </form>
</x-auth.shell>
@endsection
