@extends('web.layouts.app')
@section('title', 'Seller')
@section('eyebrow', 'Workspace')
@section('heading', 'Seller dashboard')

@section('content')
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach([
        ['Active gigs', $stats['active_gigs']],
        ['Incoming orders', $stats['orders_in']],
        ['Available', '$'.number_format($stats['available'], 0)],
        ['Clearing', '$'.number_format($stats['pending'], 0)],
    ] as $card)
        <div class="rounded-2xl border border-line bg-cream p-4">
            <p class="text-xs uppercase tracking-wide text-ink/50">{{ $card[0] }}</p>
            <p class="mt-2 font-display text-3xl">{{ $card[1] }}</p>
        </div>
    @endforeach
</div>

<p class="mt-6 text-sm text-ink/60">{{ $profile->headline ?: 'Add a headline from the app later — this board tracks gigs, orders, and wallet.' }}</p>

<div class="mt-8 grid gap-8 lg:grid-cols-2">
    <section>
        <h2 class="font-display text-xl">Gigs</h2>
        <div class="mt-3 overflow-hidden rounded-2xl border border-line bg-cream">
            @forelse($gigs as $gig)
                <div class="flex justify-between border-b border-line px-4 py-3 last:border-0">
                    <div>
                        <p class="font-medium">{{ $gig->title }}</p>
                        <p class="text-xs text-ink/50">{{ $gig->status }} · {{ $gig->orders_count }} orders</p>
                    </div>
                </div>
            @empty
                <p class="px-4 py-8 text-sm text-ink/50">No gigs yet. Create them from the API or mobile once listing screens ship.</p>
            @endforelse
        </div>
    </section>
    <section>
        <h2 class="font-display text-xl">Orders to deliver</h2>
        <div class="mt-3 overflow-hidden rounded-2xl border border-line bg-cream">
            @forelse($orders as $order)
                <div class="flex justify-between border-b border-line px-4 py-3 last:border-0">
                    <div>
                        <p class="font-medium">{{ $order->title }}</p>
                        <p class="text-xs text-ink/50">{{ $order->status }}@if($order->due_at) · due {{ $order->due_at->format('M j') }}@endif</p>
                    </div>
                    <p class="text-sm">${{ number_format($order->seller_earning, 0) }}</p>
                </div>
            @empty
                <p class="px-4 py-8 text-sm text-ink/50">No seller orders yet.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
