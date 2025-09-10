<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserImportStatusEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $mailData;
    public $emailRecipients;
    public $company;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($mailData, $emailRecipients, $company = [])
    {
        $this->mailData        = $mailData;
        $this->emailRecipients = $emailRecipients;
        $this->company         = $company;
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
