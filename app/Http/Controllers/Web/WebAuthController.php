<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class WebAuthController extends Controller
{
    public function showLogin()
    {
        return view('web.auth.login');
    }

    public function showRegister()
    {
        return view('web.auth.register');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $data['email'])->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return back()->withErrors(['email' => 'Invalid email or password.'])->onlyInput('email');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        $user->forceFill(['last_seen_at' => now()])->save();

        return redirect()->intended($this->homePath($user));
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'fullname' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone_number' => 'required|string|max:20|unique:users,phone_number',
            'password' => 'required|string|min:6|confirmed',
            'as_seller' => 'sometimes|boolean',
        ]);

        $user = User::create([
            'fullname' => $data['fullname'],
            'email' => $data['email'],
            'phone_number' => $data['phone_number'],
            'password' => $data['password'],
            'is_buyer' => true,
            'is_seller' => false,
            'timezone' => 'UTC',
            'phone_verified_at' => now(),
        ]);
        $user->username = Str::slug($user->fullname).$user->id;
        $user->save();
        $user->ensureBuyerProfile();

        if ($request->boolean('as_seller')) {
            $user->becomeSeller();
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->to($this->homePath($user->fresh()));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function homePath(User $user): string
    {
        if ($user->hasAnyRole(['admin', 'moderator'])) {
            return route('web.admin');
        }
        if ($user->is_seller) {
            return route('web.seller');
        }

        return route('web.buyer');
    }
}
