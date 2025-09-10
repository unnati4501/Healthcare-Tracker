<?php

namespace App\Listeners;

use App\Events\SendProjectSurveyEvent;
use App\Mail\SendProjectSurveyEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendProjectSurvey implements ShouldQueue
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
    public function handle(SendProjectSurveyEvent $event)
    {
        if ($event instanceof SendProjectSurveyEvent) {
            $toUsers = [
                $event->user->email,
            ];
            Mail::to($toUsers)
                ->queue(new SendProjectSurveyEmail($event->user, $event->dataParam));
        }
    }
}
