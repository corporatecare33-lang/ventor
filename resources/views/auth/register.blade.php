@extends('layouts.app')

@section('content')
<x-auth.shell>
    <h1 class="font-display text-4xl font-semibold">Create your account</h1>
    <p class="mt-2 text-sm text-ink-600">Already have one? <a href="{{ route('login') }}" class="font-medium text-leather-700 underline">Sign in</a></p>
    <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-4">
        @csrf
        <x-input name="name" label="Full name" required autocomplete="name" />
        <x-input name="email" label="Email" type="email" required autocomplete="email" />
        <x-input name="phone" label="Mobile number" type="tel" required placeholder="01XXXXXXXXX" autocomplete="tel" />
        <x-input name="password" label="Password" type="password" required autocomplete="new-password" />
        <x-input name="password_confirmation" label="Confirm password" type="password" required autocomplete="new-password" />
        <p class="text-xs text-ink-600">At least 8 characters with letters and numbers.</p>
        <button class="btn-dark w-full">Create account</button>
    </form>
</x-auth.shell>
@endsection
