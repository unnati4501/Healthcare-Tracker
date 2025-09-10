<?php

namespace App\Listeners;

use App\Events\ClientUserNotesExportEvent;
use App\Mail\ClientUserNotesExportEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Log;
use Carbon\Carbon;

class SendClientUserNotesExportListener implements ShouldQueue
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
    public function handle(ClientUserNotesExportEvent $event)
    {
        if ($event instanceof ClientUserNotesExportEvent) {
            $email = $event->user->email;
            if ($event->payload['email'] != null) {
                $email = $event->payload['email'];
            }
            $toUsers = [
                $email,
            ];
            Mail::to($toUsers)
                ->queue(new ClientUserNotesExportEmail($event->user, $event->tempPath, $event->payload, $event->fileName));

            if (!Mail::failures()) {
                //Unlink the attachement file from local
                removeFileToSpaces(config('zevolifesettings.report-export.intercomapnychallenge').$event->fileName);
            }
        }
    }
}
