<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Events\UserPresenceUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function showLogin() { return Inertia::render('Auth/Login'); }
    public function showRegister() { return Inertia::render('Auth/Register'); }

    public function register(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:20', 'unique:users', 'regex:/^[a-zA-Z0-9_]+$/'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', Password::min(6)->max(100), 'confirmed'],
        ]);

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'register_ip' => $request->ip(),
            'last_ip' => $request->ip(),
        ]);

        // Give starter badge
        $user->badges()->create(['badge_id' => 5]);

        Auth::login($user);
        return redirect()->route('home');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors(['username' => 'Invalid credentials.']);
        }

        if ($user->banned) {
            return back()->withErrors(['username' => 'Your account has been banned: ' . $user->ban_reason]);
        }

        Auth::login($user, $request->boolean('remember'));
        $user->update(['last_ip' => $request->ip(), 'last_seen_at' => now()]);

        if ($user->two_factor_enabled) {
            session(['2fa_user_id' => $user->id]);
            Auth::logout();
            return redirect()->route('2fa.verify');
        }

        return redirect()->route('home');
    }

    public function show2fa() { return Inertia::render('Auth/TwoFactor'); }

    public function verify2fa(Request $request)
    {
        $request->validate(['code' => ['required', 'string', 'size:6']]);
        $userId = session('2fa_user_id');
        if (!$userId) return redirect()->route('login');
        $user = User::findOrFail($userId);
        $google2fa = app(\PragmaRX\Google2FA\Google2FA::class);
        if (!$google2fa->verifyKey($user->two_factor_secret, $request->code)) {
            return back()->withErrors(['code' => 'Invalid 2FA code.']);
        }
        session()->forget('2fa_user_id');
        Auth::login($user);
        return redirect()->route('home');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        if ($user) broadcast(new UserPresenceUpdated($user, false));
        return redirect()->route('login');
    }
}
