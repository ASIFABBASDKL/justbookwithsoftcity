@extends('web.layouts.app')
@section('title', 'Become a seller')
@section('eyebrow', 'Seller')
@section('heading', 'Open a seller workspace')

@section('content')
<div class="max-w-lg rounded-3xl border border-line bg-cream p-8">
    <p class="text-ink/70">You already buy on JustBook. Turn on selling to list gigs, receive orders, and see wallet clearance — same login as the app.</p>
    <form method="POST" action="{{ route('web.seller.become') }}" class="mt-6">
        @csrf
        <button class="rounded-full bg-moss px-5 py-3 text-white">Become a seller</button>
    </form>
</div>
@endsection
