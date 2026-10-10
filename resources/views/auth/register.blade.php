@extends('layouts.app')

@section('content')
<x-auth.shell title="Create an account">
    <x-slot:intro>Already have one? <a href="{{ route('login') }}" class="text-leather-700 underline underline-offset-4">Sign in</a></x-slot:intro>
    <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5" x-data="{ busy: false }" @submit="busy = true" novalidate>
        @csrf
        <x-form.input name="name" label="Full name" required autocomplete="name" />
        <x-form.input name="email" label="Email" type="email" required autocomplete="email" />
        <x-form.input name="phone" label="Mobile number" type="tel" required inputmode="numeric" autocomplete="tel" hint="11 digits, starting with 01." />
        <x-form.input name="password" label="Password" type="password" required autocomplete="new-password" hint="At least 8 characters, with letters and numbers." />
        <x-form.input name="password_confirmation" label="Confirm password" type="password" required autocomplete="new-password" />
        <x-form.submit label="Create account" loading="Creating your account…" class="w-full" />
        <p class="text-[13px] text-ink-500">By creating an account you agree to our <a href="{{ route('pages.terms') }}" class="underline underline-offset-4">terms</a> and <a href="{{ route('pages.privacy-policy') }}" class="underline underline-offset-4">privacy policy</a>.</p>
    </form>
</x-auth.shell>
@endsection
