<?php
namespace App\Http\Controllers;

use App\Models\CatalogItem;
use App\Models\GameServer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class DiagnosticsController extends Controller
{
    private function resolveComputeUrl(): string
    {
        $port = (int) config('graphictoria.cloud_compute_port', 0);
        if (!$port) return '';
        return "http://127.0.0.1:{$port}";
    }

    private function resolveComputePath(): string
    {
        $clientPath = trim((string) config('graphictoria.client_path'));
        if (!$clientPath) return '';
        return rtrim(dirname($clientPath), '/\\') . DIRECTORY_SEPARATOR . 'Cloud Compute Service';
    }

    public function index()
    {
        $port = config('graphictoria.cloud_compute_port');
        return Inertia::render('Admin/Diagnostics', [
            'cloudComputePort' => $port ?: null,
            'cloudComputePath' => $this->resolveComputePath() ?: null,
            'clientPath'       => config('graphictoria.client_path'),
            'studioPath'       => config('graphictoria.studio_path'),
        ]);
    }

    /**
     * Ping the Cloud Compute Service to check if it is online.
     */
    public function pingCloudCompute(Request $request)
    {
        $url = $this->resolveComputeUrl();
        if (!$url) {
            return response()->json(['ok' => false, 'error' => 'CLOUD_COMPUTE_URL is not set in .env']);
        }
        try {
            $start = microtime(true);
            $response = Http::timeout(5)->get("{$url}/ping");
            $ms = round((microtime(true) - $start) * 1000);
            return response()->json([
                'ok'     => $response->successful(),
                'status' => $response->status(),
                'ms'     => $ms,
                'body'   => substr($response->body(), 0, 200),
            ]);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Request an avatar render for a given user ID via Cloud Compute.
     */
    public function renderAvatar(Request $request)
    {
        $request->validate(['user_id' => ['required', 'integer', 'exists:users,id']]);
        $userId  = $request->integer('user_id');
        $url     = $this->resolveComputeUrl();

        if (!$url) {
            return response()->json(['ok' => false, 'error' => 'CLOUD_COMPUTE_URL is not set in .env']);
        }

        $start = microtime(true);
        try {
            $response = Http::timeout(30)->post("{$url}/render/avatar", [
                'userId'  => $userId,
                'baseUrl' => config('app.url'),
            ]);
            $ms = round((microtime(true) - $start) * 1000);

            if ($response->successful() && str_contains($response->header('Content-Type'), 'image')) {
                Storage::disk('local')->makeDirectory('renders/avatar');
                Storage::disk('local')->put("renders/avatar/{$userId}.png", $response->body());
                return response()->json(['ok' => true, 'ms' => $ms, 'thumbUrl' => "/Thumbs/Avatar.ashx?userId={$userId}&t=" . time()]);
            }

            return response()->json(['ok' => false, 'ms' => $ms, 'status' => $response->status(), 'body' => substr($response->body(), 0, 500)]);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'ms' => round((microtime(true) - $start) * 1000), 'error' => $e->getMessage()]);
        }
    }

    /**
     * Request a catalog item thumbnail render via Cloud Compute.
     */
    public function renderCatalogItem(Request $request)
    {
        $request->validate(['item_id' => ['required', 'integer', 'exists:catalog_items,id']]);
        $itemId = $request->integer('item_id');
        $item   = CatalogItem::findOrFail($itemId);
        $url    = $this->resolveComputeUrl();

        if (!$url) {
            return response()->json(['ok' => false, 'error' => 'CLOUD_COMPUTE_URL is not set in .env']);
        }

        if (!$item->data_file) {
            return response()->json(['ok' => false, 'error' => 'Item has no model file (.rbxm) to render']);
        }

        $start = microtime(true);
        try {
            $response = Http::timeout(30)->post("{$url}/render/item", [
                'itemId'   => $itemId,
                'type'     => $item->type,
                'dataFile' => $item->data_file,
                'baseUrl'  => config('app.url'),
            ]);
            $ms = round((microtime(true) - $start) * 1000);

            if ($response->successful() && str_contains($response->header('Content-Type'), 'image')) {
                Storage::disk('local')->makeDirectory('assets/' . $item->type . '/thumbnail');
                Storage::disk('local')->put("assets/{$item->type}/thumbnail/{$item->data_file}.png", $response->body());
                return response()->json(['ok' => true, 'ms' => $ms, 'thumbUrl' => "/catalog/thumbnail/{$item->type}/{$item->data_file}.png?t=" . time()]);
            }

            return response()->json(['ok' => false, 'ms' => $ms, 'status' => $response->status(), 'body' => substr($response->body(), 0, 500)]);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'ms' => round((microtime(true) - $start) * 1000), 'error' => $e->getMessage()]);
        }
    }

    /**
     * Stress-test avatar rendering — render N avatars concurrently.
     */
    public function stressRender(Request $request)
    {
        $request->validate([
            'count' => ['required', 'integer', 'min:1', 'max:20'],
            'type'  => ['required', 'in:avatar,item'],
        ]);

        $count   = $request->integer('count');
        $type    = $request->input('type');
        $url     = $this->resolveComputeUrl();

        if (!$url) {
            return response()->json(['ok' => false, 'error' => 'CLOUD_COMPUTE_URL is not set in .env']);
        }

        if ($type === 'avatar') {
            $ids = User::inRandomOrder()->limit($count)->pluck('id')->toArray();
        } else {
            $ids = CatalogItem::whereNotNull('data_file')->inRandomOrder()->limit($count)->pluck('id')->toArray();
        }

        $results = [];
        $start   = microtime(true);

        // Run renders sequentially (concurrent HTTP in PHP would need async lib)
        foreach ($ids as $id) {
            $t = microtime(true);
            try {
                $endpoint = $type === 'avatar' ? '/render/avatar' : '/render/item';
                $payload  = $type === 'avatar'
                    ? ['userId' => $id, 'baseUrl' => config('app.url')]
                    : ['itemId' => $id, 'baseUrl' => config('app.url')];

                $response = Http::timeout(30)->post("{$url}{$endpoint}", $payload);
                $results[] = [
                    'id' => $id,
                    'ok' => $response->successful() && str_contains($response->header('Content-Type'), 'image'),
                    'ms' => round((microtime(true) - $t) * 1000),
                    'status' => $response->status(),
                ];
            } catch (\Exception $e) {
                $results[] = ['id' => $id, 'ok' => false, 'ms' => round((microtime(true) - $t) * 1000), 'error' => $e->getMessage()];
            }
        }

        $totalMs   = round((microtime(true) - $start) * 1000);
        $succeeded = count(array_filter($results, fn($r) => $r['ok']));

        return response()->json([
            'ok'        => true,
            'total_ms'  => $totalMs,
            'count'     => $count,
            'succeeded' => $succeeded,
            'failed'    => $count - $succeeded,
            'results'   => $results,
        ]);
    }

    /**
     * Simulate the full game join flow for a given server.
     */
    public function testGameJoin(Request $request)
    {
        $request->validate(['server_id' => ['required', 'integer', 'exists:game_servers,id']]);
        $server = GameServer::findOrFail($request->integer('server_id'));

        $log = [];

        // Step 1: generate auth ticket
        $ticket   = Str::random(64);
        $issuedAt = now()->timestamp;
        Cache::put("game_ticket:{$ticket}", [
            'user_id'   => $request->user()->id,
            'server_id' => $server->id,
            'issued_at' => $issuedAt,
        ], now()->addMinutes(5));
        $log[] = ['step' => 'Ticket generated', 'ok' => true, 'data' => substr($ticket, 0, 16) . '…'];

        // Step 2: simulate PlaceLauncher call
        $baseUrl = config('app.url');
        $launcherUrl = "{$baseUrl}/Game/PlaceLauncher.ashx?gameinfo={$ticket}";
        try {
            $resp = Http::timeout(5)->get($launcherUrl);
            $log[] = ['step' => 'PlaceLauncher', 'ok' => $resp->successful(), 'status' => $resp->status(), 'body' => substr($resp->body(), 0, 200)];
            $body = $resp->json();
        } catch (\Exception $e) {
            $log[] = ['step' => 'PlaceLauncher', 'ok' => false, 'error' => $e->getMessage()];
            return response()->json(['ok' => false, 'log' => $log]);
        }

        // Step 3: simulate Join call
        if (!empty($body['joinScript'])) {
            try {
                $resp = Http::timeout(5)->get($body['joinScript']);
                $log[] = ['step' => 'Join.ashx', 'ok' => $resp->successful(), 'status' => $resp->status(), 'body' => substr($resp->body(), 0, 300)];
            } catch (\Exception $e) {
                $log[] = ['step' => 'Join.ashx', 'ok' => false, 'error' => $e->getMessage()];
            }
        }

        // Step 4: check server reachability if IP is configured
        if ($server->ip && $server->port) {
            $connected = @fsockopen($server->ip, $server->port, $errno, $errstr, 2);
            if ($connected) {
                fclose($connected);
                $log[] = ['step' => 'TCP connectivity', 'ok' => true, 'data' => "{$server->ip}:{$server->port}"];
            } else {
                $log[] = ['step' => 'TCP connectivity', 'ok' => false, 'data' => "Cannot reach {$server->ip}:{$server->port} — $errstr"];
            }
        } else {
            $log[] = ['step' => 'TCP connectivity', 'ok' => null, 'data' => 'No IP/port configured for this server'];
        }

        $allOk = empty(array_filter($log, fn($l) => $l['ok'] === false));
        return response()->json(['ok' => $allOk, 'server' => $server->name, 'log' => $log]);
    }
}
