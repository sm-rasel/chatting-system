<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public $chat;

    public function __construct($chat)
    {
        $this->chat = $chat;
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('conversations.' . $this->chat->conversation_id);
    }
}
