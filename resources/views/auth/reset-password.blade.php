@extends('layouts.app')

@section('content')
<x-auth.shell>
    <h1 class="font-display text-4xl font-semibold">Choose a new password</h1>
    <form method="POST" action="{{ route('password.update') }}" class="mt-8 space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <x-input name="email" label="Email" type="email" required :value="$email" />
        <x-input name="password" label="New password" type="password" required autocomplete="new-password" />
        <x-input name="password_confirmation" label="Confirm password" type="password" required autocomplete="new-password" />
        <button class="btn-dark w-full">Reset password</button>
    </form>
</x-auth.shell>
@endsection
