@extends('layouts.app')

@section('content')
<x-auth.shell title="Reset your password" intro="Enter your account email and we will send you a link to choose a new password.">
    <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-5" x-data="{ busy: false }" @submit="busy = true" novalidate>
        @csrf
        <x-form.input name="email" label="Email" type="email" required autocomplete="email" autofocus />
        <x-form.submit label="Send reset link" loading="Sending…" class="w-full" />
        <a href="{{ route('login') }}" class="inline-block text-[13px] text-leather-700 underline underline-offset-4">Back to sign in</a>
    </form>
</x-auth.shell>
@endsection
