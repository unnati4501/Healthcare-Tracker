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

class UserForgotPasswordEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $forgotPasswordToken;
    public $appUser;
    public $xDeviceOs;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user, $forgotPasswordToken = "", $appUser = "", $xDeviceOs = "")
    {
        $this->user = $user;
        $this->forgotPasswordToken = $forgotPasswordToken;
        $this->appUser = $appUser;
        $this->xDeviceOs = $xDeviceOs;
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
