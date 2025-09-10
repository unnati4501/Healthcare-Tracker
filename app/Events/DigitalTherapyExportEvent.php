<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;

class DigitalTherapyExportEvent
{
    use Dispatchable, InteractsWithSockets;

    public $payload;
    public $user;
    public $url;
    public $fileName;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($payload, $user, $url, $fileName)
    {
        $this->payload  = $payload;
        $this->user     = $user;
        $this->url      = $url;
        $this->fileName = $fileName;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
