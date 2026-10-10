@extends('layouts.app')

@section('content')
<x-auth.shell title="Sign in">
    <x-slot:intro>New to LeatherX? <a href="{{ route('register') }}" class="text-leather-700 underline underline-offset-4">Create an account</a></x-slot:intro>
    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5" x-data="{ busy: false }" @submit="busy = true" novalidate>
        @csrf
        <x-form.input name="email" label="Email" type="email" required autocomplete="email" autofocus />
        <div>
            <x-form.input name="password" label="Password" type="password" required autocomplete="current-password" />
            <a href="{{ route('password.request') }}" class="mt-2 inline-block text-[13px] text-leather-700 underline underline-offset-4">Forgot your password?</a>
        </div>
        <x-form.checkbox name="remember" label="Keep me signed in on this device" />
        <x-form.submit label="Sign in" loading="Signing in…" class="w-full" />
    </form>
</x-auth.shell>
@endsection
