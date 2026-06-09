<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use PragmaRX\Google2FA\Google2FA;

class SettingsController extends Controller
{
    public function index() { return Inertia::render('Settings/Index', ['user' => Auth::user()]); }

    public function updateAbout(Request $request)
    {
        $request->validate(['about' => ['nullable', 'string', 'max:256']]);
        Auth::user()->update(['about' => $request->about]);
        return back()->with('success', 'About updated.');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'min:6', 'confirmed'],
        ]);
        if (!Hash::check($request->current_password, Auth::user()->password)) {
            return back()->withErrors(['current_password' => 'Incorrect current password.']);
        }
        Auth::user()->update(['password' => Hash::make($request->password)]);
        return back()->with('success', 'Password changed.');
    }

    public function setup2fa()
    {
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();
        session(['2fa_secret' => $secret]);
        $qrUrl = $google2fa->getQRCodeUrl('Graphictoria', Auth::user()->email, $secret);
        return Inertia::render('Settings/TwoFactor', ['qrUrl' => $qrUrl, 'secret' => $secret]);
    }

    public function enable2fa(Request $request)
    {
        $request->validate(['code' => ['required', 'size:6']]);
        $secret = session('2fa_secret');
        $google2fa = new Google2FA();
        if (!$google2fa->verifyKey($secret, $request->code)) {
            return back()->withErrors(['code' => 'Invalid code.']);
        }
        Auth::user()->update(['two_factor_enabled' => true, 'two_factor_secret' => $secret]);
        session()->forget('2fa_secret');
        return redirect()->route('settings.index')->with('success', '2FA enabled.');
    }

    public function disable2fa(Request $request)
    {
        $request->validate(['code' => ['required', 'size:6']]);
        $google2fa = new Google2FA();
        if (!$google2fa->verifyKey(Auth::user()->two_factor_secret, $request->code)) {
            return back()->withErrors(['code' => 'Invalid code.']);
        }
        Auth::user()->update(['two_factor_enabled' => false, 'two_factor_secret' => null]);
        return redirect()->route('settings.index')->with('success', '2FA disabled.');
    }
}
