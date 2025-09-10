<?php

namespace App\Listeners;

use App\Events\CreditHistoryExportEvent;
use App\Mail\SendCreditHistoryEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendCreditHistoryExportListener implements ShouldQueue
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
    public function handle(CreditHistoryExportEvent $event)
    {
        if ($event instanceof CreditHistoryExportEvent) {
            $email = $event->email;
            if ($email == null) {
                $email = $event->user->email;
            }

            $toUsers = [
                $email,
            ];

            Mail::to($toUsers)
                ->queue(new SendCreditHistoryEmail($event->user, $event->url, $event->fileName));

            if (!Mail::failures()) {
                //Unlink the attachement file from local
                removeFileToSpaces(config('zevolifesettings.report-export.intercomapnychallenge').$event->fileName);
            }
        }
    }
}
