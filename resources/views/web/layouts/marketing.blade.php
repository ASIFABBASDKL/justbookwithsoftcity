<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'JustBook') — Freelance, two ways</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-cream text-ink antialiased">
    <header class="sticky top-0 z-30 border-b border-line/80 bg-cream/90 backdrop-blur">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-5 py-4">
            <a href="{{ route('home') }}" class="font-display text-2xl tracking-tight">JustBook</a>
            <nav class="hidden items-center gap-8 text-sm font-medium md:flex">
                <a href="#how" class="hover:text-moss">How it works</a>
                <a href="#paths" class="hover:text-moss">Hire or sell</a>
                <a href="#trust" class="hover:text-moss">Trust</a>
            </nav>
            <div class="flex items-center gap-3 text-sm">
                @auth
                    <a href="{{ route('web.app') }}" class="rounded-full bg-ink px-4 py-2 text-cream">Open dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="px-3 py-2">Log in</a>
                    <a href="{{ route('register') }}" class="rounded-full bg-moss px-4 py-2 text-white">Get started</a>
                @endauth
            </div>
        </div>
    </header>
    @yield('content')
    <footer class="border-t border-line bg-paper">
        <div class="mx-auto flex max-w-6xl flex-col gap-2 px-5 py-10 text-sm text-ink/70 md:flex-row md:justify-between">
            <p class="font-display text-lg text-ink">JustBook</p>
            <p>Gigs + jobs. One order room. Mobile app stays the client — this site is your web workspace.</p>
        </div>
    </footer>
</body>
</html>
