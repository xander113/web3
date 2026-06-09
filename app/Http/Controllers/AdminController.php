<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CatalogItem;
use App\Models\Report;
use App\Models\ForumTopic;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function index()
    {
        $stats = [
            'users' => User::count(),
            'banned' => User::where('banned', true)->count(),
            'items' => CatalogItem::count(),
            'pending_items' => CatalogItem::where('approved', false)->where('declined', false)->count(),
            'reports' => Report::where('status', 'pending')->count(),
        ];
        return Inertia::render('Admin/Index', ['stats' => $stats]);
    }

    public function users(Request $request)
    {
        $users = User::query()
            ->when($request->search, fn($q) => $q->where('username', 'ilike', '%' . $request->search . '%'))
            ->orderByDesc('created_at')
            ->paginate(30);
        return Inertia::render('Admin/Users', ['users' => $users, 'search' => $request->search]);
    }

    public function ban(Request $request, int $id)
    {
        $request->validate(['reason' => ['required', 'string', 'max:500']]);
        $user = User::findOrFail($id);
        if ($user->isAdmin()) return back()->withErrors(['error' => 'Cannot ban an admin.']);
        $user->update(['banned' => true, 'ban_reason' => $request->reason]);
        return back()->with('success', 'User banned.');
    }

    public function unban(int $id)
    {
        User::findOrFail($id)->update(['banned' => false, 'ban_reason' => null]);
        return back()->with('success', 'User unbanned.');
    }

    public function assets()
    {
        $items = CatalogItem::where('approved', false)->where('declined', false)->where('deleted', false)
            ->with('creator:id,username')->orderBy('created_at')->paginate(20);
        return Inertia::render('Admin/Assets', ['items' => $items]);
    }

    public function approveAsset(int $id)
    {
        CatalogItem::findOrFail($id)->update(['approved' => true]);
        return back()->with('success', 'Asset approved.');
    }

    public function declineAsset(int $id)
    {
        CatalogItem::findOrFail($id)->update(['declined' => true]);
        return back()->with('success', 'Asset declined.');
    }

    public function reports()
    {
        $reports = Report::where('status', 'pending')
            ->with('reporter:id,username', 'reportedUser:id,username')
            ->orderBy('created_at')->paginate(20);
        return Inertia::render('Admin/Reports', ['reports' => $reports]);
    }

    public function resolveReport(Request $request, string $id)
    {
        $request->validate(['status' => ['required', 'in:reviewed,dismissed']]);
        Report::findOrFail($id)->update(['status' => $request->status, 'reviewed_by' => auth()->id()]);
        return back()->with('success', 'Report resolved.');
    }
}
