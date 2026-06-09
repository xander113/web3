<?php
namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class GroupController extends Controller
{
    public function index()
    {
        $groups = Group::withCount('members')
            ->with('creator:id,username')
            ->orderByDesc('member_count')
            ->paginate(20);
        return Inertia::render('Groups/Index', ['groups' => $groups]);
    }

    public function show(string $id)
    {
        $group = Group::with(['creator:id,username', 'members:id,username,last_seen_at'])->findOrFail($id);
        $isMember = $group->members()->where('users.id', Auth::id())->exists();
        return Inertia::render('Groups/Show', ['group' => $group, 'isMember' => $isMember]);
    }

    public function create() { return Inertia::render('Groups/Create'); }

    public function store(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name' => ['required', 'string', 'min:5', 'max:50', 'unique:groups'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);
        if ($user->groups()->count() >= 10) {
            return back()->withErrors(['error' => 'Maximum 10 groups allowed.']);
        }
        if ($user->coins < 50) {
            return back()->withErrors(['error' => 'Need 50 coins to create a group.']);
        }
        $user->decrement('coins', 50);
        $group = Group::create([
            'creator_id' => $user->id,
            'name' => $request->name,
            'description' => $request->description,
        ]);
        $group->members()->attach($user->id, ['is_owner' => true]);
        return redirect()->route('groups.show', $group->id)->with('success', 'Group created!');
    }

    public function join(string $id)
    {
        $user = Auth::user();
        $group = Group::findOrFail($id);
        if ($user->groups()->count() >= 10) {
            return back()->withErrors(['error' => 'Maximum 10 groups allowed.']);
        }
        if (!$group->members()->where('users.id', $user->id)->exists()) {
            $group->members()->attach($user->id, ['is_owner' => false]);
            $group->increment('member_count');
        }
        return back()->with('success', 'Joined group.');
    }

    public function leave(string $id)
    {
        $group = Group::findOrFail($id);
        if ($group->creator_id === Auth::id()) {
            return back()->withErrors(['error' => 'Owner cannot leave. Delete the group instead.']);
        }
        $group->members()->detach(Auth::id());
        $group->decrement('member_count');
        return back()->with('success', 'Left group.');
    }
}
