<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->search, fn($q) => $q->where('username', 'ilike', '%' . $request->search . '%'))
            ->where('banned', false)
            ->where('hide_status', false)
            ->select('id', 'username', 'last_seen_at', 'rank', 'coins')
            ->orderByDesc('last_seen_at')
            ->paginate(24);
        return Inertia::render('Users/Index', ['users' => $users, 'search' => $request->search]);
    }
}
