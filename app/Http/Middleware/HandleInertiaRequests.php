<?php
namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'username' => $request->user()->username,
                    'email' => $request->user()->email,
                    'rank' => $request->user()->rank,
                    'coins' => $request->user()->coins,
                    'avatar_hash' => $request->user()->avatar_hash,
                    'unread_messages' => $request->user() ? $request->user()->receivedMessages()->where('read', false)->where('receiver_deleted', false)->count() : 0,
                    'pending_requests' => $request->user() ? $request->user()->friendRequests()->count() : 0,
                ] : null,
            ],
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ];
    }
}
