<?php
namespace App\Events;

use App\Models\ForumReply;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ForumReplyPosted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public ForumReply $reply) {}

    public function broadcastOn(): array
    {
        return [new Channel('topic.' . $this->reply->topic_id)];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->reply->id,
            'body' => $this->reply->body,
            'user' => [
                'id' => $this->reply->user->id,
                'username' => $this->reply->user->username,
            ],
            'created_at' => $this->reply->created_at,
        ];
    }
}
