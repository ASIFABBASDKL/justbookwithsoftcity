@extends('web.layouts.auth')
@section('title', 'Register')

@section('content')
<h1 class="font-display text-3xl">Start on the web</h1>
<p class="mt-2 text-sm text-ink/60">You can still use the mobile app with this login.</p>
<form method="POST" action="{{ route('register') }}" class="mt-8 space-y-4">
    @csrf
    <label class="block text-sm">Full name
        <input name="fullname" value="{{ old('fullname') }}" required class="mt-1 w-full rounded-xl border border-line bg-cream px-3 py-2">
    </label>
    <label class="block text-sm">Email
        <input name="email" type="email" value="{{ old('email') }}" required class="mt-1 w-full rounded-xl border border-line bg-cream px-3 py-2">
    </label>
    <label class="block text-sm">Phone
        <input name="phone_number" value="{{ old('phone_number') }}" required class="mt-1 w-full rounded-xl border border-line bg-cream px-3 py-2">
    </label>
    <label class="block text-sm">Password
        <input name="password" type="password" required class="mt-1 w-full rounded-xl border border-line bg-cream px-3 py-2">
    </label>
    <label class="block text-sm">Confirm password
        <input name="password_confirmation" type="password" required class="mt-1 w-full rounded-xl border border-line bg-cream px-3 py-2">
    </label>
    <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" name="as_seller" value="1" @checked(old('as_seller'))>
        Also open a seller workspace
    </label>
    @if($errors->any())
        <ul class="text-sm text-red-700">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    @endif
    <button class="w-full rounded-full bg-moss py-3 text-white">Create account</button>
</form>
<p class="mt-6 text-sm">Already here? <a class="text-moss underline" href="{{ route('login') }}">Log in</a></p>
@endsection
