<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;

class ChallengeActivityReportExportEvent
{
    use Dispatchable, InteractsWithSockets;

    public $user;
    public $tab;
    public $tempPath;
    public $payload;
    public $fileName;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($user, $tab, $tempPath, $payload, $fileName)
    {
        $this->user                   = $user;
        $this->tab                    = $tab;
        $this->tempPath               = $tempPath;
        $this->payload                = $payload;
        $this->fileName               = $fileName;
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
