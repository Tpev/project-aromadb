<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SignalingEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $type;
    public $payload;
    public $room;

    /**
     * Create a new event instance.
     *
     * @param string $type
     * @param array $payload
     * @param string $room
     * @return void
     */
    public function __construct($type, $payload, $room)
    {
        $this->type = $type;
        $this->payload = $payload;
        $this->room = $room;

        \Log::info("SignalingEvent: Instance créée - Type: {$type}, Salle: {$room}");
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel
     */
    public function broadcastOn()
    {
        return new Channel('room.' . $this->room);
    }

    /**
     * Définir le nom de l'événement.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'SignalingEvent';
    }
}
