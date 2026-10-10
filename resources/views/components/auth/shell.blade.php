{{-- Shared two-column shell for auth pages --}}
<section class="grid min-h-[calc(100vh-120px)] lg:grid-cols-2">
    <div class="relative hidden overflow-hidden bg-ink-950 lg:block">
        <div class="absolute inset-0 placeholder-leather opacity-90"></div>
        <div class="relative flex h-full flex-col justify-end p-12 text-white">
            <p class="eyebrow text-gold-400">LeatherX account</p>
            <p class="mt-4 max-w-md font-display text-4xl leading-tight font-semibold">Track orders, manage your Tap profile and register warranty — all in one place.</p>
        </div>
    </div>
    <div class="flex items-center justify-center px-4 py-14">
        <div class="w-full max-w-md">{{ $slot }}</div>
    </div>
</section>
