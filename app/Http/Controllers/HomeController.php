<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            Auth::user()->update(['last_seen_at' => now()]);
        }

        $onlineUsers = User::where('last_seen_at', '>=', now()->subMinutes(5))
            ->where('hide_status', false)
            ->where('banned', false)
            ->select('id', 'username', 'last_seen_at')
            ->orderByDesc('last_seen_at')
            ->take(20)
            ->get();

        return Inertia::render('Home', ['onlineUsers' => $onlineUsers]);
    }

    public function ping()
    {
        if (Auth::check()) {
            Auth::user()->update(['last_seen_at' => now()]);
        }
        return response()->json(['ok' => true]);
    }
}
