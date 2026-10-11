{{-- Self-contained error page: no database, settings or Livewire, so it renders even when the app is down. --}}
<!DOCTYPE html>
<html lang="en-BD">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $title }} | LeatherX</title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <style>
        body { margin: 0; min-height: 100dvh; display: grid; place-items: center; background: #f5f1ea; color: #16130f; font: 16px/1.6 ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif; }
        main { max-width: 34rem; padding: 3rem 1.25rem; }
        img { height: 3rem; width: auto; }
        p.code { margin: 2.5rem 0 0; font-size: .6875rem; font-weight: 600; letter-spacing: .14em; text-transform: uppercase; color: #7a4d2c; }
        h1 { margin: .75rem 0 0; font: 600 clamp(2rem, 5vw, 2.75rem)/1.1 Georgia, 'Times New Roman', serif; }
        p.msg { margin: 1rem 0 0; color: #57504a; }
        a.btn { display: inline-flex; margin-top: 2rem; min-height: 3rem; align-items: center; padding: 0 1.5rem; border-radius: 2px; background: #16130f; color: #fff; text-decoration: none; font-size: .875rem; font-weight: 500; }
        a.btn:focus-visible { outline: 2px solid #b08443; outline-offset: 3px; }
    </style>
</head>
<body>
    <main>
        <img src="/images/brand/leatherx-logo.svg" alt="LeatherX" width="151" height="23">
        <p class="code">Error {{ $code }}</p>
        <h1>{{ $title }}</h1>
        <p class="msg">{{ $message }}</p>
        <a class="btn" href="/">Back to the homepage</a>
    </main>
</body>
</html>
