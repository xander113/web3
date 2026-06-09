<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function show(int $id)
    {
        $user = User::with(['badges', 'friends' => fn($q) => $q->take(6), 'groups', 'gameServers', 'equippedItems.item'])->findOrFail($id);
        $isFriend = Auth::check() && Auth::user()->friends()->where('friend_id', $id)->exists();
        $hasPendingRequest = Auth::check() && Auth::user()->sentFriendRequests()->where('receiver_id', $id)->exists();
        $receivedRequest = Auth::check() && Auth::user()->friendRequests()->where('sender_id', $id)->first();
        return Inertia::render('Profile/Show', [
            'profile' => $user,
            'isFriend' => $isFriend,
            'hasPendingRequest' => $hasPendingRequest,
            'receivedRequest' => $receivedRequest,
        ]);
    }
}
