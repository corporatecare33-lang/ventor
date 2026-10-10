@php
    $links = array_values(array_filter([
        ['phone', 'Call', $profile->phone ? 'tel:'.$profile->phone : null],
        ['chat', 'WhatsApp', $profile->whatsapp ? 'https://wa.me/'.preg_replace('/\D/', '', $profile->whatsapp) : null],
        ['mail', 'Email', $profile->email ? 'mailto:'.$profile->email : null],
        ['globe', 'Website', $profile->website],
        ['facebook', 'Facebook', $profile->facebook],
        ['instagram', 'Instagram', $profile->instagram],
        ['linkedin', 'LinkedIn', $profile->linkedin],
    ], fn ($l) => $l[2]));
@endphp
<!DOCTYPE html>
<html lang="en-BD">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $profile->name }} · LeatherX Tap</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#16130f">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-dvh bg-ink-950 text-white">
    <main class="mx-auto flex min-h-dvh max-w-md flex-col px-5 py-10">
        <div class="text-center">
            <span class="mx-auto flex size-20 items-center justify-center rounded-full border border-brass-300/50 font-display text-3xl text-brass-300" aria-hidden="true">{{ mb_strtoupper(mb_substr($profile->name, 0, 1)) }}</span>
            <h1 class="mt-5 text-[2rem] leading-tight text-white">{{ $profile->name }}</h1>
            @if ($profile->designation || $profile->company)
                <p class="mt-1 text-[15px] text-white/70">{{ collect([$profile->designation, $profile->company])->filter()->implode(' · ') }}</p>
            @endif
            @if ($profile->bio)<p class="mt-4 text-[15px] leading-relaxed text-white/75">{{ $profile->bio }}</p>@endif
            <a href="{{ route('nfc.vcard', $profile) }}" class="btn-inverse mt-7 w-full"><x-lx-icon name="download" class="size-4" /> Save contact</a>
        </div>

        @if ($links)
            <ul class="mt-6 space-y-2">
                @foreach ($links as [$icon, $label, $url])
                    @php $external = ! str_starts_with($url, 'tel:') && ! str_starts_with($url, 'mailto:'); @endphp
                    <li>
                        <a href="{{ $url }}" @if ($external) target="_blank" rel="noopener nofollow" @endif
                           class="flex min-h-14 items-center gap-4 rounded-xs border border-white/15 px-5 transition hover:border-brass-300">
                            <x-lx-icon :name="$icon" class="size-5 text-brass-300" />
                            <span class="text-[15px]">{{ $label }}</span>
                            @if ($external)<span class="sr-only">(opens in a new tab)</span>@endif
                            <x-lx-icon name="chevron-right" class="ml-auto size-4 text-white/50" />
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif

        <a href="{{ route('tap') }}" class="mt-auto pt-12 text-center text-[13px] text-white/60 hover:text-white">Made with a LeatherX Tap wallet</a>
    </main>
</body>
</html>
