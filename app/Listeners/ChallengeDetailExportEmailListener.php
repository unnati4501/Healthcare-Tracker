<?php

namespace App\Listeners;

use App\Events\ChallengeDetailExportEvent;
use App\Mail\ChallengeDetailExportEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Log;
use Carbon\Carbon;

class ChallengeDetailExportEmailListener implements ShouldQueue
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
    public function handle(ChallengeDetailExportEvent $event)
    {
        if ($event instanceof ChallengeDetailExportEvent) {
            $email = $event->user->email;
            if ($event->payload['email'] != null) {
                $email = $event->payload['email'];
            }
            $toUsers = [
                $email,
            ];

            Mail::to($toUsers)
                ->queue(new ChallengeDetailExportEmail($event->user, $event->challenge, $event->tempPath, $event->payload, $event->fileName));
        }
    }
}
