<?php

namespace App\Listeners;

use App\Events\AdminAlertEvent;
use App\Mail\AdminAlertEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class AdminAlertEmailListener implements ShouldQueue
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
    public function handle(AdminAlertEvent $event)
    {
        if ($event instanceof AdminAlertEvent) {
            $toUsers = [$event->data['alertEmails']];
            Mail::to($toUsers)
                ->queue(new AdminAlertEmail($event->data));
        }
    }
}
