<?php

namespace App\Listeners;

use App\Events\SendMcSurveyReportExportEvent;
use App\Events\SendZcSurveyReportExportEvent;
use App\Events\SendZCUserSurveyEvent;
use App\Mail\McSurveyReportExportEmail;
use App\Mail\SendZCUserSurveyEmail;
use App\Mail\ZcSurveyReportExportEmail;
use Illuminate\Support\Facades\Mail;

class ZcSurveyEmailListener
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
     * @param  mixed SendZCUserSurveyEvent|SendZcSurveyReportExportEvent $event
     * @return void
     */
    public function handle($event)
    {
        if ($event instanceof SendZCUserSurveyEvent) {
            $toUsers = [$event->user->email];
            Mail::to($toUsers)
                ->queue(new SendZCUserSurveyEmail($event->user, $event->dataParam));
        } elseif ($event instanceof SendZcSurveyReportExportEvent) {
            $toUsers = [$event->payload['email']];
            Mail::to($toUsers)
                ->queue(new ZcSurveyReportExportEmail($event->logRecord, $event->payload));
        } elseif ($event instanceof SendMcSurveyReportExportEvent) {
            $toUsers = [$event->payload['email']];
            Mail::to($toUsers)
                ->queue(new McSurveyReportExportEmail($event->logRecord, $event->payload));
        }
    }
}
