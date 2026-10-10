@extends('layouts.app')

@section('content')
<x-auth.shell>
    <h1 class="font-display text-4xl font-semibold">Reset your password</h1>
    <p class="mt-2 text-sm text-ink-600">We'll email you a link to choose a new password.</p>
    <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-4">
        @csrf
        <x-input name="email" label="Email" type="email" required autofocus />
        <button class="btn-dark w-full">Send reset link</button>
        <a href="{{ route('login') }}" class="block text-center text-sm text-ink-600 underline">Back to sign in</a>
    </form>
</x-auth.shell>
@endsection
