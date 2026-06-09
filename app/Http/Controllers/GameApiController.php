<?php
namespace App\Http\Controllers;

use App\Models\CatalogItem;
use App\Models\GameServer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class GameApiController extends Controller
{
    /**
     * Generate an auth ticket and return the graphictoria:// URI for launching the player.
     * Used by the Play button on game server pages.
     */
    public function launch(string $id)
    {
        $server = GameServer::findOrFail($id);
        $user   = Auth::user();

        $ticket   = Str::random(64);
        $issuedAt = now()->timestamp;

        Cache::put("game_ticket:{$ticket}", [
            'user_id'   => $user->id,
            'server_id' => $server->id,
            'issued_at' => $issuedAt,
        ], now()->addMinutes(5));

        $baseUrl         = config('app.url');
        $placeLauncherUrl = urlencode("{$baseUrl}/Game/PlaceLauncher.ashx?request=RequestGame&placeId={$server->id}&isPartyLeader=false&gender=&isTeleport=false");

        $uri = "graphictoria://1+launchmode:play+gameinfo:{$ticket}+launchtime:{$issuedAt}+placelauncherurl:{$placeLauncherUrl}";

        return Inertia::render('Games/Launch', [
            'server'    => $server,
            'launchUri' => $uri,
            'clientPath' => config('graphictoria.client_path'),
        ]);
    }

    /**
     * Dev-only: execute the game client/studio exe directly on the server machine.
     * Only works when G5_CLIENT_PATH / G5_STUDIO_PATH is configured.
     */
    public function devLaunch(Request $request, string $id)
    {
        $request->validate(['type' => ['required', 'in:player,studio']]);
        $isStudio  = $request->input('type') === 'studio';
        $basePath  = trim($isStudio
            ? (string) config('graphictoria.studio_path')
            : (string) config('graphictoria.client_path'));

        if (!$basePath) {
            return response()->json(['ok' => false, 'error' => $isStudio ? 'G5_STUDIO_PATH not set' : 'G5_CLIENT_PATH not set']);
        }

        $server   = GameServer::findOrFail($id);
        $user     = Auth::user();
        $ticket   = Str::random(64);
        $issuedAt = now()->timestamp;

        Cache::put("game_ticket:{$ticket}", [
            'user_id'   => $user->id,
            'server_id' => $server->id,
            'issued_at' => $issuedAt,
        ], now()->addMinutes(5));

        $baseUrl          = config('app.url');
        $placeLauncherUrl = urlencode("{$baseUrl}/Game/PlaceLauncher.ashx?request=RequestGame&placeId={$server->id}&isPartyLeader=false&gender=&isTeleport=false");

        if ($isStudio) {
            $uri = "graphictoria-studio://1+launchmode:edit+gameinfo:{$ticket}+launchtime:{$issuedAt}+placeId:{$server->id}+baseUrl:" . urlencode($baseUrl);
        } else {
            $uri = "graphictoria://1+launchmode:play+gameinfo:{$ticket}+launchtime:{$issuedAt}+placelauncherurl:{$placeLauncherUrl}";
        }

        // Find the exe in the configured directory
        $exeGlob = glob(rtrim(str_replace('\\', '/', $basePath), '/') . '/*.exe') ?: [];
        if (empty($exeGlob)) {
            return response()->json(['ok' => false, 'error' => "No .exe found in: {$basePath}"]);
        }
        $exe = $exeGlob[0];

        // Launch the process detached — works on Windows (start "") and Linux (nohup)
        if (PHP_OS_FAMILY === 'Windows') {
            $cmd = 'start "" ' . escapeshellarg($exe) . ' ' . escapeshellarg($uri);
            pclose(popen($cmd, 'r'));
        } else {
            $cmd = 'nohup ' . escapeshellarg($exe) . ' ' . escapeshellarg($uri) . ' > /dev/null 2>&1 &';
            exec($cmd);
        }

        return response()->json(['ok' => true, 'exe' => basename($exe), 'uri' => $uri]);
    }

    /**
     * Launch Graphictoria Studio for a game server/place.
     */
    public function launchStudio(string $id)
    {
        $server = GameServer::findOrFail($id);
        $user   = Auth::user();

        $ticket   = Str::random(64);
        $issuedAt = now()->timestamp;

        Cache::put("studio_ticket:{$ticket}", [
            'user_id'   => $user->id,
            'server_id' => $server->id,
            'issued_at' => $issuedAt,
        ], now()->addMinutes(5));

        $baseUrl = config('app.url');
        $uri = "graphictoria-studio://1+launchmode:edit+gameinfo:{$ticket}+launchtime:{$issuedAt}+placeId:{$server->id}+baseUrl:" . urlencode($baseUrl);

        return Inertia::render('Games/Launch', [
            'server'      => $server,
            'launchUri'   => $uri,
            'clientPath'  => config('graphictoria.studio_path'),
            'isStudio'    => true,
        ]);
    }

    /**
     * Called by the game client to get the server IP/port and a join ticket.
     * Mirrors /Game/PlaceLauncher.ashx
     */
    public function placeLauncher(Request $request)
    {
        $ticket = $request->query('gameinfo') ?? $request->query('ticket');
        if (!$ticket) {
            return response()->json(['status' => 'error', 'message' => 'Missing ticket'], 400);
        }

        $data = Cache::get("game_ticket:{$ticket}");
        if (!$data) {
            return response()->json(['status' => 'error', 'message' => 'Invalid or expired ticket'], 403);
        }

        $server = GameServer::find($data['server_id']);
        if (!$server) {
            return response()->json(['status' => 'error', 'message' => 'Server not found'], 404);
        }

        $joinTicket = Str::random(64);
        Cache::put("join_ticket:{$joinTicket}", [
            'user_id'   => $data['user_id'],
            'server_id' => $server->id,
        ], now()->addMinutes(2));

        return response()->json([
            'status'    => 'ok',
            'jobId'     => $server->id,
            'status'    => 'waiting',
            'joinScript' => config('app.url') . "/Game/Join.ashx?ticket={$joinTicket}&placeId={$server->id}",
        ]);
    }

    /**
     * Called by the game client to finalize the join. Returns server connection details.
     * Mirrors /Game/Join.ashx
     */
    public function join(Request $request)
    {
        $ticket = $request->query('ticket');
        $data   = $ticket ? Cache::pull("join_ticket:{$ticket}") : null;

        if (!$data) {
            return response('|TICKET INVALID|', 403)->header('Content-Type', 'text/plain');
        }

        $server = GameServer::find($data['server_id']);
        $user   = User::find($data['user_id']);

        if (!$server || !$user) {
            return response('|SERVER NOT FOUND|', 404)->header('Content-Type', 'text/plain');
        }

        $baseUrl = config('app.url');

        // Roblox join response format
        $response = implode("\r\n", [
            'RBXAuthenticationNegotiation:',
            "serverip={$server->ip}",
            "serverport={$server->port}",
            "userid={$user->id}",
            "username={$user->username}",
            "characterappearance={$baseUrl}/Asset/AvatarFetch.ashx?userId={$user->id}",
            "clientticket=" . Str::random(32),
            "gameid={$server->id}",
            "placeId={$server->id}",
        ]);

        return response($response)->header('Content-Type', 'text/plain');
    }

    /**
     * Validate an auth ticket (called by the game server to verify a joining player).
     */
    public function validate(Request $request)
    {
        // Simple stub — the game server posts a ticket, we verify it
        return response('true')->header('Content-Type', 'text/plain');
    }

    /**
     * Serve a catalog asset (.rbxm model file) by place/asset ID.
     * Mirrors /Asset/?id={id}
     */
    public function serveAsset(Request $request)
    {
        $id = $request->query('id');
        if (!$id) abort(400);

        $item = CatalogItem::find($id);
        if (!$item || !$item->data_file) abort(404);

        $path = 'assets/' . $item->type . '/' . $item->data_file . '.rbxm';
        if (!Storage::disk('local')->exists($path)) abort(404);

        return response()->file(
            Storage::disk('local')->path($path),
            ['Content-Type' => 'application/octet-stream']
        );
    }

    /**
     * Return XML place info for the game server script.
     * Mirrors /Game/LoadPlaceInfo.ashx?PlaceId={id}
     */
    public function loadPlaceInfo(Request $request)
    {
        $placeId = $request->query('PlaceId');
        $server  = GameServer::find($placeId);

        if (!$server) abort(404);

        $xml = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<GameInfo>
  <PlaceId>{$server->id}</PlaceId>
  <Name><![CDATA[{$server->name}]]></Name>
  <Description><![CDATA[{$server->description}]]></Description>
  <CreatorId>{$server->creator_id}</CreatorId>
  <MaxPlayers>12</MaxPlayers>
</GameInfo>
XML;

        return response($xml)->header('Content-Type', 'text/xml');
    }

    /**
     * Simple chat filter stub — returns the message unchanged.
     */
    public function chatFilter(Request $request)
    {
        $message = $request->input('message', $request->query('message', ''));
        return response($message)->header('Content-Type', 'text/plain');
    }

    /**
     * Insert asset list stub for the InsertService.
     */
    public function insertAsset(Request $request)
    {
        return response('<items/>', 200)->header('Content-Type', 'text/xml');
    }

    /**
     * Return an avatar thumbnail.
     * Mirrors /Thumbs/Avatar.ashx?userId={id}
     */
    public function avatarThumb(Request $request)
    {
        $userId = $request->query('userId');
        if (!$userId) abort(400);

        $path = "renders/avatar/{$userId}.png";
        if (Storage::disk('local')->exists($path)) {
            return response()->file(Storage::disk('local')->path($path));
        }

        // Trigger a render via Cloud Compute if configured
        $this->requestAvatarRender((int) $userId);

        // Return default placeholder
        $placeholder = public_path('images/default-avatar.png');
        if (file_exists($placeholder)) {
            return response()->file($placeholder);
        }
        abort(404);
    }

    /**
     * Trigger avatar render via Cloud Compute Service.
     * The render result is stored and served on subsequent requests.
     */
    public function renderAvatar(Request $request)
    {
        $userId = $request->query('userId') ?? Auth::id();
        if (!$userId) abort(400);

        $result = $this->requestAvatarRender((int) $userId);
        if (!$result) {
            return response()->json(['error' => 'Render failed or Cloud Compute not configured'], 503);
        }

        return response()->json(['ok' => true, 'path' => "/Thumbs/Avatar.ashx?userId={$userId}"]);
    }

    /**
     * Call the Cloud Compute Service to render a user's avatar.
     * Returns true on success, false if not configured or failed.
     */
    protected function requestAvatarRender(int $userId): bool
    {
        $computeUrl = config('graphictoria.cloud_compute_url');
        if (!$computeUrl) return false;

        try {
            $response = Http::timeout(30)->post("{$computeUrl}/render/avatar", [
                'userId'  => $userId,
                'baseUrl' => config('app.url'),
            ]);

            if ($response->successful() && $response->header('Content-Type') === 'image/png') {
                Storage::disk('local')->makeDirectory('renders/avatar');
                Storage::disk('local')->put("renders/avatar/{$userId}.png", $response->body());
                return true;
            }
        } catch (\Exception $e) {
            // Cloud Compute unavailable — silently fail
        }

        return false;
    }
}
