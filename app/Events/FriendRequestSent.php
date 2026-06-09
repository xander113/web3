<?php
namespace App\Events;

use App\Models\FriendRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FriendRequestSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public FriendRequest $friendRequest) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('user.' . $this->friendRequest->receiver_id)];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->friendRequest->id,
            'sender' => [
                'id' => $this->friendRequest->sender->id,
                'username' => $this->friendRequest->sender->username,
            ],
        ];
    }
}
