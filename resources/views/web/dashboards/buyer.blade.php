@extends('web.layouts.app')
@section('title', 'Buyer')
@section('eyebrow', 'Workspace')
@section('heading', 'Buyer dashboard')

@section('content')
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach([
        ['Active orders', $stats['active_orders']],
        ['Completed', $stats['completed']],
        ['Open jobs', $stats['open_jobs']],
        ['Spent', '$'.number_format($stats['spent'], 0)],
    ] as $card)
        <div class="rounded-2xl border border-line bg-cream p-4">
            <p class="text-xs uppercase tracking-wide text-ink/50">{{ $card[0] }}</p>
            <p class="mt-2 font-display text-3xl">{{ $card[1] }}</p>
        </div>
    @endforeach
</div>

<div class="mt-8 grid gap-8 lg:grid-cols-2">
    <section>
        <h2 class="font-display text-xl">Your orders</h2>
        <div class="mt-3 overflow-hidden rounded-2xl border border-line bg-cream">
            @forelse($orders as $order)
                <div class="flex items-center justify-between border-b border-line px-4 py-3 last:border-0">
                    <div>
                        <p class="font-medium">{{ $order->title }}</p>
                        <p class="text-xs text-ink/50">{{ $order->order_number }} · {{ $order->status }}</p>
                    </div>
                    <p class="text-sm">${{ number_format($order->price + $order->extras_total, 2) }}</p>
                </div>
            @empty
                <p class="px-4 py-8 text-sm text-ink/50">No orders yet. Browse gigs below or post a job from the mobile app.</p>
            @endforelse
        </div>
    </section>
    <section>
        <h2 class="font-display text-xl">Jobs you posted</h2>
        <div class="mt-3 overflow-hidden rounded-2xl border border-line bg-cream">
            @forelse($jobs as $job)
                <div class="border-b border-line px-4 py-3 last:border-0">
                    <p class="font-medium">{{ $job->title }}</p>
                    <p class="text-xs text-ink/50">{{ $job->status }} · {{ $job->proposals_count }} proposals</p>
                </div>
            @empty
                <p class="px-4 py-8 text-sm text-ink/50">No job posts yet.</p>
            @endforelse
        </div>
    </section>
</div>

<section class="mt-8">
    <h2 class="font-display text-xl">Gigs you can buy</h2>
    <div class="mt-3 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($gigs as $gig)
            <article class="rounded-2xl border border-line bg-cream p-4">
                <p class="text-xs text-moss">{{ $gig->status }}</p>
                <h3 class="mt-1 font-medium">{{ $gig->title }}</h3>
                <p class="mt-2 text-sm text-ink/60">From ${{ number_format(optional($gig->packages->sortBy('price')->first())->price ?? 0, 0) }}</p>
            </article>
        @empty
            <p class="text-sm text-ink/50">No live gigs yet. Sellers will show up here after admin approval.</p>
        @endforelse
    </div>
</section>
@endsection
