<?php
namespace App\Http\Controllers;

use App\Models\GameServer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class GameServerController extends Controller
{
    public function index()
    {
        $servers = GameServer::where('is_public', true)
            ->with('creator:id,username')
            ->withCount('players')
            ->orderByDesc('updated_at')
            ->paginate(20);
        return Inertia::render('Games/Index', ['servers' => $servers]);
    }

    public function create() { return Inertia::render('Games/Create'); }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'ip'          => ['nullable', 'ip'],
            'port'        => ['nullable', 'integer', 'min:1', 'max:65535'],
            'is_public'   => ['boolean'],
            'place_file'  => ['nullable', 'file', 'max:102400'],
        ]);

        $placeFile = null;
        if ($request->hasFile('place_file') && $request->file('place_file')->isValid()) {
            $uuid      = (string) Str::uuid();
            Storage::disk('local')->makeDirectory('places');
            $request->file('place_file')->storeAs('places', "{$uuid}.rbxm", 'local');
            $placeFile = $uuid;
        }

        $server = GameServer::create([
            'creator_id'  => Auth::id(),
            'name'        => $request->name,
            'description' => $request->description,
            'ip'          => $request->ip,
            'port'        => $request->port,
            'server_key'  => Str::random(32),
            'private_key' => Str::random(32),
            'is_public'   => $request->boolean('is_public', true),
            'place_file'  => $placeFile,
        ]);

        return redirect()->route('profile.show', Auth::id())->with('success', 'Server created!');
    }
}
