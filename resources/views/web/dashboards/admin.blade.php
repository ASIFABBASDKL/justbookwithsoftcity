@extends('web.layouts.app')
@section('title', 'Admin')
@section('eyebrow', 'Control room')
@section('heading', 'Admin dashboard')

@section('content')
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    @foreach([
        ['Users', $stats['users']],
        ['Orders', $stats['orders']],
        ['Fee revenue', '$'.number_format($stats['revenue'], 0)],
        ['Gigs pending', $stats['gigs_pending']],
        ['Open disputes', $stats['disputes']],
        ['Payouts waiting', $stats['payouts']],
    ] as $card)
        <div class="rounded-2xl border border-line bg-cream p-4">
            <p class="text-xs uppercase tracking-wide text-ink/50">{{ $card[0] }}</p>
            <p class="mt-2 font-display text-3xl">{{ $card[1] }}</p>
        </div>
    @endforeach
</div>

<div class="mt-8 grid gap-8 lg:grid-cols-2">
    <section>
        <h2 class="font-display text-xl">Recent users</h2>
        <div class="mt-3 overflow-hidden rounded-2xl border border-line bg-cream">
            @foreach($recentUsers as $u)
                <div class="flex justify-between border-b border-line px-4 py-3 last:border-0 text-sm">
                    <span>{{ $u->fullname }}</span>
                    <span class="text-ink/50">{{ $u->is_seller ? 'seller' : 'buyer' }}</span>
                </div>
            @endforeach
        </div>
    </section>
    <section>
        <h2 class="font-display text-xl">Latest orders</h2>
        <div class="mt-3 overflow-hidden rounded-2xl border border-line bg-cream">
            @forelse($recentOrders as $order)
                <div class="border-b border-line px-4 py-3 last:border-0 text-sm">
                    <p class="font-medium">{{ $order->order_number }} · {{ $order->status }}</p>
                    <p class="text-ink/50">{{ optional($order->buyer)->fullname }} → {{ optional($order->seller)->fullname }}</p>
                </div>
            @empty
                <p class="px-4 py-8 text-sm text-ink/50">No marketplace orders yet.</p>
            @endforelse
        </div>
    </section>
</div>

<div class="mt-8 grid gap-8 lg:grid-cols-2">
    <section>
        <h2 class="font-display text-xl">Gig approval queue</h2>
        <div class="mt-3 overflow-hidden rounded-2xl border border-line bg-cream">
            @forelse($pendingGigs as $gig)
                <div class="border-b border-line px-4 py-3 last:border-0">
                    <p class="font-medium">{{ $gig->title }}</p>
                    <p class="text-xs text-ink/50">{{ optional(optional($gig->seller)->user)->fullname }}</p>
                </div>
            @empty
                <p class="px-4 py-8 text-sm text-ink/50">Queue is clear.</p>
            @endforelse
        </div>
    </section>
    <section>
        <h2 class="font-display text-xl">Open disputes</h2>
        <div class="mt-3 overflow-hidden rounded-2xl border border-line bg-cream">
            @forelse($disputes as $d)
                <div class="border-b border-line px-4 py-3 last:border-0 text-sm">
                    <p class="font-medium">{{ $d->reason }}</p>
                    <p class="text-ink/50">Order #{{ optional($d->order)->order_number }}</p>
                </div>
            @empty
                <p class="px-4 py-8 text-sm text-ink/50">No open disputes.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
