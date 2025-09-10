<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class SendZCUserSurveyEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $dataParam;
    public $appUser;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user, $dataParam = array())
    {
        $this->user = $user;
        $this->dataParam = $dataParam;
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
