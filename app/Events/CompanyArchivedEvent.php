<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CompanyArchivedEvent
{
    use Dispatchable, InteractsWithSockets;

    public $user;
    public $company;
    public $tempPath;
    public $fileName;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($company, $user, $tempPath, $fileName)
    {
        $this->company                = $company;
        $this->user                   = $user;
        $this->tempPath               = $tempPath;
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
