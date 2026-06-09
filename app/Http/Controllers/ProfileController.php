<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function show(string $id)
    {
        // Accept both numeric ID and username in the URL
        $user = is_numeric($id)
            ? User::with(['badges', 'friends' => fn($q) => $q->take(6), 'groups', 'gameServers', 'equippedItems.item'])->findOrFail($id)
            : User::with(['badges', 'friends' => fn($q) => $q->take(6), 'groups', 'gameServers', 'equippedItems.item'])->where('username', $id)->firstOrFail();
        $isFriend = Auth::check() && Auth::user()->friends()->where('friend_id', $user->id)->exists();
        $hasPendingRequest = Auth::check() && Auth::user()->sentFriendRequests()->where('receiver_id', $user->id)->exists();
        $receivedRequest = Auth::check() && Auth::user()->friendRequests()->where('sender_id', $user->id)->first();
        return Inertia::render('Profile/Show', [
            'profile' => $user,
            'isFriend' => $isFriend,
            'hasPendingRequest' => $hasPendingRequest,
            'receivedRequest' => $receivedRequest,
        ]);
    }
}
