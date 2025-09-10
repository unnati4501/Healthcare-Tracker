<?php

namespace App\Listeners;

use App\Events\IntercompanyChallengeExportEvent;
use App\Mail\IntercompanyChallengeExport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Log;
use App\Models\ChallengeExportHistory;
use Carbon\Carbon;

class SendIntercompanyChallengeEmail implements ShouldQueue
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
    public function handle(IntercompanyChallengeExportEvent $event)
    {
        if ($event instanceof IntercompanyChallengeExportEvent) {
            $email = $event->user->email;
            if ($event->payload['email'] != null) {
                $email = $event->payload['email'];
            }
            $toUsers = [
                $email,
            ];

            Mail::to($toUsers)
                ->queue(new IntercompanyChallengeExport($event->user, $event->challenge, $event->tempPath, $event->payload, $event->challengeExportHistory, $event->fileName));

            if (!Mail::failures()) {
                $challengeExporthistory = ChallengeExportHistory::where('challenge_id', $event->challenge->id)
                                                ->orderby('id', 'DESC')
                                                ->first();
                $challengeExporthistory->status = '2';
                $challengeExporthistory->process_completed_at = Carbon::now()->toDateTimeString();
                $challengeExporthistory->update();

                //Unlink the attachement file from local
                removeFileToSpaces(config('zevolifesettings.report-export.intercomapnychallenge').$event->fileName);
            }
        }
    }
}
