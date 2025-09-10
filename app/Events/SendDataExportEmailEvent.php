<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendDataExportEmailEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

/**
     * email which are required in send email
     *
     * @var string $email
     */
    public $email;

    /**
     * Data which are required in email
     *
     * @var array $data
     */
    public $data;

    /**
     * Create a new event instance.
     *
     * @param array $data
     * @return void
     */
    public function __construct($email, array $data)
    {
        $this->email = $email;
        $this->data = $data;
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
