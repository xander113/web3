<?php
namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function store(Request $request, string $id)
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $reported = User::findOrFail($id);

        if ((string)$reported->id === (string)Auth::id()) {
            return back()->withErrors(['error' => 'You cannot report yourself.']);
        }

        $alreadyReported = Report::where('reporter_id', Auth::id())
            ->where('reported_user_id', $reported->id)
            ->exists();

        if ($alreadyReported) {
            return back()->withErrors(['error' => 'You have already reported this user.']);
        }

        Report::create([
            'reporter_id'      => Auth::id(),
            'reported_user_id' => $reported->id,
            'reason'           => $request->reason,
        ]);

        return back()->with('success', 'Report submitted.');
    }
}
