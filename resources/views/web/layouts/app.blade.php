<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · JustBook</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600&family=Outfit:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-paper text-ink antialiased">
    <div class="flex min-h-screen">
        <aside class="hidden w-60 shrink-0 border-r border-line bg-cream md:flex md:flex-col">
            <a href="{{ route('home') }}" class="px-6 py-6 font-display text-xl">JustBook</a>
            <nav class="flex flex-1 flex-col gap-1 px-3 text-sm font-medium">
                <a href="{{ route('web.buyer') }}" class="rounded-lg px-3 py-2 {{ request()->routeIs('web.buyer') ? 'bg-ink text-cream' : 'hover:bg-line/60' }}">Buyer</a>
                <a href="{{ route('web.seller') }}" class="rounded-lg px-3 py-2 {{ request()->routeIs('web.seller*') ? 'bg-ink text-cream' : 'hover:bg-line/60' }}">Seller</a>
                @if(auth()->user()->hasAnyRole(['admin', 'moderator']))
                    <a href="{{ route('web.admin') }}" class="rounded-lg px-3 py-2 {{ request()->routeIs('web.admin') ? 'bg-ink text-cream' : 'hover:bg-line/60' }}">Admin</a>
                @endif
            </nav>
            <form method="POST" action="{{ route('logout') }}" class="p-4">
                @csrf
                <button class="text-sm text-ink/60 hover:text-ink">Log out</button>
            </form>
        </aside>
        <div class="flex min-w-0 flex-1 flex-col">
            <header class="flex items-center justify-between border-b border-line bg-cream px-5 py-4 md:px-8">
                <div>
                    <p class="text-xs uppercase tracking-wider text-ink/50">@yield('eyebrow')</p>
                    <h1 class="font-display text-2xl">@yield('heading')</h1>
                </div>
                <div class="text-right text-sm">
                    <p class="font-medium">{{ auth()->user()->fullname }}</p>
                    <p class="text-ink/50">{{ auth()->user()->email }}</p>
                </div>
            </header>
            <main class="flex-1 px-5 py-6 md:px-8">
                @if(session('success'))
                    <p class="mb-4 rounded-lg bg-moss/10 px-4 py-3 text-sm text-moss-dark">{{ session('success') }}</p>
                @endif
                @if(session('info'))
                    <p class="mb-4 rounded-lg bg-gold/20 px-4 py-3 text-sm">{{ session('info') }}</p>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
