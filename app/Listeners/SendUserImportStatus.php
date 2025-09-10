<?php

namespace App\Listeners;

use App\Events\UserImportStatusEvent;
use App\Mail\ImportStatusMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendUserImportStatus implements ShouldQueue
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
    public $delay = 10;

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
    public function handle(UserImportStatusEvent $event)
    {
        if ($event instanceof UserImportStatusEvent) {
            $toUsers = $event->emailRecipients;
            Mail::to($toUsers)
                ->queue(new ImportStatusMail($event->mailData, $event->company));
        }
    }
}
