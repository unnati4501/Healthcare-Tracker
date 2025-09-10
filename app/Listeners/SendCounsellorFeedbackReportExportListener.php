<?php

namespace App\Listeners;

use App\Events\CounsellorFeedbackReportExportEvent;
use App\Mail\CounsellorFeedbackReportExportEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Log;
use Carbon\Carbon;

class SendCounsellorFeedbackReportExportListener implements ShouldQueue
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
    public function handle(CounsellorFeedbackReportExportEvent $event)
    {
        if ($event instanceof CounsellorFeedbackReportExportEvent) {
            $email = $event->user->email;
            if ($event->payload['email'] != null) {
                $email = $event->payload['email'];
            }
            $toUsers = [
                $email,
            ];
            Mail::to($toUsers)
                ->queue(new CounsellorFeedbackReportExportEmail($event->user, $event->tempPath, $event->payload, $event->fileName));

            if (!Mail::failures()) {
                //Unlink the attachement file from local
                removeFileToSpaces(config('zevolifesettings.report-export.intercomapnychallenge').$event->fileName);
            }
        }
    }
}
