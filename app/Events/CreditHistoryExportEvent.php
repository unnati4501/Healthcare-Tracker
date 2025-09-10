<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;

class CreditHistoryExportEvent
{
    use Dispatchable, InteractsWithSockets;

    public $email;
    public $user;
    public $url;
    public $fileName;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($email, $user, $url, $fileName)
    {
        $this->email    = $email;
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
