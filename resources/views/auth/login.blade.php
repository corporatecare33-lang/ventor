@extends('layouts.app')

@section('content')
<x-auth.shell>
    <h1 class="font-display text-4xl font-semibold">Welcome back</h1>
    <p class="mt-2 text-sm text-ink-600">New here? <a href="{{ route('register') }}" class="font-medium text-leather-700 underline">Create an account</a></p>
    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-4">
        @csrf
        <x-input name="email" label="Email" type="email" required autocomplete="email" autofocus />
        <div>
            <div class="flex items-center justify-between"><label for="password" class="label">Password</label><a href="{{ route('password.request') }}" class="mb-1.5 text-xs text-ink-600 underline">Forgot?</a></div>
            <input id="password" name="password" type="password" required autocomplete="current-password" class="field">
        </div>
        <label class="flex items-center gap-2 text-sm text-ink-700"><input type="checkbox" name="remember" value="1" class="size-4 accent-ink-900"> Keep me signed in</label>
        <button class="btn-dark w-full">Sign in</button>
    </form>
</x-auth.shell>
@endsection
