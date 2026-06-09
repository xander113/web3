<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\FriendRequest;
use App\Events\FriendRequestSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class FriendController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $friends = $user->friends()->select('users.id', 'users.username', 'users.last_seen_at')->get();
        $requests = $user->friendRequests()->with('sender:id,username')->get();
        return Inertia::render('Friends/Index', ['friends' => $friends, 'requests' => $requests]);
    }

    public function showFriends(string $id)
    {
        $user = User::findOrFail($id);
        $friends = $user->friends()->select('users.id', 'users.username', 'users.last_seen_at')->paginate(24);
        return Inertia::render('Friends/List', ['profile' => $user, 'friends' => $friends]);
    }

    public function send(Request $request, string $id)
    {
        $user = Auth::user();
        if ((string)$user->id === $id) return back()->withErrors(['error' => 'Cannot friend yourself.']);
        if ($user->last_friend_request_at && $user->last_friend_request_at->gt(now()->subMinute())) {
            return back()->withErrors(['error' => 'Please wait before sending another request.']);
        }
        $target = User::findOrFail($id);
        if ($user->friends()->where('friend_id', $id)->exists()) {
            return back()->withErrors(['error' => 'Already friends.']);
        }
        $existing = FriendRequest::where('sender_id', $user->id)->where('receiver_id', $id)->first();
        if ($existing) return back()->withErrors(['error' => 'Request already sent.']);
        $fr = FriendRequest::create(['sender_id' => $user->id, 'receiver_id' => $id]);
        $user->update(['last_friend_request_at' => now()]);
        try { broadcast(new FriendRequestSent($fr->load('sender')))->toOthers(); } catch (\Exception) {}
        return back()->with('success', 'Friend request sent.');
    }

    public function accept(string $id)
    {
        $request = FriendRequest::where('id', $id)->where('receiver_id', Auth::id())->firstOrFail();
        Auth::user()->friends()->syncWithoutDetaching([$request->sender_id]);
        User::find($request->sender_id)->friends()->syncWithoutDetaching([Auth::id()]);
        $request->delete();
        return back()->with('success', 'Friend added.');
    }

    public function decline(string $id)
    {
        FriendRequest::where('id', $id)->where('receiver_id', Auth::id())->firstOrFail()->delete();
        return back()->with('success', 'Request declined.');
    }

    public function remove(string $id)
    {
        Auth::user()->friends()->detach($id);
        User::find($id)?->friends()->detach(Auth::id());
        return back()->with('success', 'Friend removed.');
    }
}
