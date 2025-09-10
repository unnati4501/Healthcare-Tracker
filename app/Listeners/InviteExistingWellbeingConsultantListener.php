<?php

namespace App\Listeners;

use App\Events\InviteExistingWellbeingConsultantEvent;
use App\Mail\InviteExistingWellbeingConsultantEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

/**
 * Class InviteExistingWellbeingConsultantListener
 */
class InviteExistingWellbeingConsultantListener implements ShouldQueue
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
    public function handle(InviteExistingWellbeingConsultantEvent $event)
    {
        if ($event instanceof InviteExistingWellbeingConsultantEvent) {
            $toUsers = [
                $event->data['email']
            ];
            Mail::to($toUsers)
                ->queue(new InviteExistingWellbeingConsultantEmail($event->data));
        }
    }
}
