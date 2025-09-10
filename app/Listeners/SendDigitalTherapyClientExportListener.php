<?php

namespace App\Listeners;

use App\Events\DigitalTherapyClientExportEvent;
use App\Mail\DigitalTherapyClientExportEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Log;
use Carbon\Carbon;

class SendDigitalTherapyClientExportListener implements ShouldQueue
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
    public function handle(DigitalTherapyClientExportEvent $digitalTherapy)
    {
        if ($digitalTherapy instanceof DigitalTherapyClientExportEvent) {
            $email = $digitalTherapy->user->email;
            if ($digitalTherapy->payload['email'] != null) {
                $email = $digitalTherapy->payload['email'];
            }
            $toUsers = [
                $email,
            ];

            Mail::to($toUsers)
                ->queue(new DigitalTherapyClientExportEmail($digitalTherapy->user, $digitalTherapy->url, $digitalTherapy->fileName));

            if (!Mail::failures()) {
                //Unlink the attachement file from local
                removeFileToSpaces(config('zevolifesettings.report-export.intercomapnychallenge').$digitalTherapy->fileName);
            }
        }
    }
}
