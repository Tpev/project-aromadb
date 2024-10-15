<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;

class SignalingEvent implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $type;
    public $payload;
    public $room;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($type, $payload, $room)
    {
        $this->type = $type; // 'offer', 'answer', 'ice-candidate'
        $this->payload = $payload;
        $this->room = $room;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('room.' . $this->room);
    }

    public function broadcastAs()
    {
        return 'SignalingEvent';
    }
}
