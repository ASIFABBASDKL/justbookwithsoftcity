@extends('web.layouts.auth')
@section('title', 'Log in')

@section('content')
<h1 class="font-display text-3xl">Welcome back</h1>
<p class="mt-2 text-sm text-ink/60">Same account as the mobile app.</p>
<form method="POST" action="{{ route('login') }}" class="mt-8 space-y-4">
    @csrf
    <label class="block text-sm">
        Email
        <input name="email" type="email" value="{{ old('email') }}" required class="mt-1 w-full rounded-xl border border-line bg-cream px-3 py-2">
    </label>
    <label class="block text-sm">
        Password
        <input name="password" type="password" required class="mt-1 w-full rounded-xl border border-line bg-cream px-3 py-2">
    </label>
    @error('email')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
    <button class="w-full rounded-full bg-moss py-3 text-white">Log in</button>
</form>
<p class="mt-6 text-sm">No web account yet? <a class="text-moss underline" href="{{ route('register') }}">Register</a></p>
@endsection
