@extends('web.layouts.marketing')

@section('content')
<section class="mx-auto max-w-6xl px-5 py-16 md:py-24">
    <p class="mb-4 text-sm font-semibold uppercase tracking-[0.2em] text-moss">Hybrid marketplace</p>
    <h1 class="max-w-3xl font-display text-5xl leading-tight md:text-7xl">
        Hire a gig today.<br>Post a job tomorrow.
    </h1>
    <p class="mt-6 max-w-xl text-lg text-ink/70">
        JustBook is Fiverr-style packages and Upwork-style bidding in one product. Both paths land in the same order room — delivery, revisions, escrow.
    </p>
    <div class="mt-8 flex flex-wrap gap-3">
        <a href="{{ route('register') }}" class="rounded-full bg-moss px-6 py-3 text-white">Create a free account</a>
        <a href="{{ route('login') }}" class="rounded-full border border-ink/20 px-6 py-3">I already have the app</a>
    </div>
</section>

<section id="paths" class="border-y border-line bg-paper">
    <div class="mx-auto grid max-w-6xl gap-8 px-5 py-16 md:grid-cols-2">
        <div class="rounded-3xl bg-cream p-8 ring-1 ring-line">
            <p class="text-xs font-semibold uppercase tracking-widest text-gold">Door A</p>
            <h2 class="mt-2 font-display text-3xl">Buy a gig</h2>
            <p class="mt-3 text-ink/70">Sellers list Basic / Standard / Premium. You pick a package, pay, and the order clock starts after requirements.</p>
        </div>
        <div class="rounded-3xl bg-ink p-8 text-cream">
            <p class="text-xs font-semibold uppercase tracking-widest text-gold">Door B</p>
            <h2 class="mt-2 font-display text-3xl">Post a job</h2>
            <p class="mt-3 text-cream/70">Describe the brief. Sellers spend connects to bid. You hire one proposal — same order engine takes over.</p>
        </div>
    </div>
</section>

<section id="how" class="mx-auto max-w-6xl px-5 py-16">
    <h2 class="font-display text-4xl">How work actually moves</h2>
    <ol class="mt-10 grid gap-6 md:grid-cols-4">
        @foreach([
            ['01', 'Match', 'Gig checkout or accepted bid.'],
            ['02', 'Hold', 'Funds sit in escrow — not in the seller wallet yet.'],
            ['03', 'Deliver', 'Files, revisions, due dates, one timeline.'],
            ['04', 'Release', 'You approve (or we auto-complete). Then payout clearance.'],
        ] as $step)
            <li class="rounded-2xl border border-line bg-cream p-5">
                <span class="text-sm text-moss">{{ $step[0] }}</span>
                <h3 class="mt-2 font-display text-xl">{{ $step[1] }}</h3>
                <p class="mt-2 text-sm text-ink/65">{{ $step[2] }}</p>
            </li>
        @endforeach
    </ol>
</section>

<section id="trust" class="bg-moss-dark text-cream">
    <div class="mx-auto max-w-6xl px-5 py-16">
        <h2 class="font-display text-4xl">Built for a real marketplace</h2>
        <div class="mt-8 grid gap-6 md:grid-cols-3 text-sm text-cream/80">
            <p>Buyer and seller can be the same account. Switch dashboards anytime.</p>
            <p>Mobile app remains the client. This website is landing + web dashboards only — Stripe comes later.</p>
            <p>Admin sees users, gig approvals, disputes, and fees from one board.</p>
        </div>
        <a href="{{ route('register') }}" class="mt-10 inline-block rounded-full bg-gold px-6 py-3 text-ink">Open a workspace</a>
    </div>
</section>
@endsection
