<?php

namespace App\Listeners;

use App\Events\SendSingleUseCodeEvent;
use App\Mail\SingleUseCodeEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendSingleUseCodeListener implements ShouldQueue
{
    /**
     * The name of the connection the job should be sent to.
     *
     * @var string|null
     */
    public $connection = 'redis';

    /**
     * The name of the queue the job should be sent to.
     *
     * @var string|null
     */
    public $queue = 'listeners';

    /**
     * The time (seconds) before the job should be processed.
     *
     * @var int
     */
    public $delay = 5;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(SendSingleUseCodeEvent $event)
    {
        if ($event instanceof SendSingleUseCodeEvent) {
            $recipients = [$event->data['email']];
            Mail::to($recipients)
                ->queue(new SingleUseCodeEmail($event->data));
        }
    }
}