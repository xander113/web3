<?php
namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('user.' . $this->message->receiver_id)];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'subject' => $this->message->subject,
            'sender' => [
                'id' => $this->message->sender->id,
                'username' => $this->message->sender->username,
            ],
            'created_at' => $this->message->created_at,
        ];
    }
}
