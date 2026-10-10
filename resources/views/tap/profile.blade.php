@php
    $links = array_filter([
        ['phone', 'Call', $profile->phone ? 'tel:'.$profile->phone : null],
        ['chat', 'WhatsApp', $profile->whatsapp ? 'https://wa.me/'.preg_replace('/\D/', '', $profile->whatsapp) : null],
        ['mail', 'Email', $profile->email ? 'mailto:'.$profile->email : null],
        ['globe', 'Website', $profile->website],
        ['facebook', 'Facebook', $profile->facebook],
        ['instagram', 'Instagram', $profile->instagram],
        ['linkedin', 'LinkedIn', $profile->linkedin],
    ], fn ($l) => $l[2]);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $profile->name }} · LeatherX Tap</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#0d0b0a">
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-ink-950 text-white">
    <main class="mx-auto flex min-h-screen max-w-md flex-col px-5 py-10">
        <div class="relative overflow-hidden rounded-[28px] border border-white/10 p-8 text-center placeholder-leather">
            <span class="mx-auto grid size-24 place-items-center rounded-full border-2 border-gold-400/60 bg-ink-900 font-display text-4xl font-semibold text-gold-300">{{ mb_strtoupper(mb_substr($profile->name, 0, 1)) }}</span>
            <h1 class="mt-5 font-display text-3xl font-semibold">{{ $profile->name }}</h1>
            @if ($profile->designation || $profile->company)
                <p class="mt-1 text-sm text-white/70">{{ collect([$profile->designation, $profile->company])->filter()->implode(' · ') }}</p>
            @endif
            @if ($profile->bio)<p class="mt-4 text-sm leading-6 text-white/65">{{ $profile->bio }}</p>@endif
            <a href="{{ route('nfc.vcard', $profile) }}" class="btn-gold mt-7 w-full"><x-lx-icon name="download" class="size-4" /> Save contact</a>
        </div>

        <div class="mt-5 space-y-2.5">
            @foreach ($links as [$icon, $label, $url])
                <a href="{{ $url }}" @if (! str_starts_with($url, 'tel:') && ! str_starts_with($url, 'mailto:')) target="_blank" rel="noopener nofollow" @endif
                   class="flex items-center gap-4 rounded-2xl border border-white/10 bg-white/[0.03] px-5 py-4 transition hover:border-gold-400/60">
                    <x-lx-icon :name="$icon" class="size-5 text-gold-400" />
                    <span class="text-sm font-medium">{{ $label }}</span>
                    <x-lx-icon name="chevron-right" class="ml-auto size-4 text-white/40" />
                </a>
            @endforeach
        </div>

        <a href="{{ route('tap') }}" class="mt-auto pt-10 text-center text-xs tracking-[0.2em] text-white/40 uppercase">Powered by LEATHER<span class="text-gold-400">X</span> Tap</a>
    </main>
</body>
</html>
