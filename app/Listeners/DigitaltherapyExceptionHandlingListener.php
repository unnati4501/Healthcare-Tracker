<?php

namespace App\Listeners;

use App\Events\DigitaltherapyExceptionHandlingEvent;
use App\Mail\DigitaltherapyExceptionHandlingEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

/**
 * Class DigitaltherapyExceptionHandlingListener
 */
class DigitaltherapyExceptionHandlingListener implements ShouldQueue
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
    public function handle(DigitaltherapyExceptionHandlingEvent $event)
    {
        $appEnvironment = app()->environment();
        if ($appEnvironment == 'production' || $appEnvironment == 'uat') {
            $userEmail = config('zevolifesettings.mail-digital-therapy-exception.uat');
        } else {
            $userEmail =  config('zevolifesettings.mail-digital-therapy-exception.local');
        }
        if ($event instanceof DigitaltherapyExceptionHandlingEvent) {
            $toUsers = [
                $userEmail
            ];
            Mail::to($toUsers)
                ->queue(new DigitaltherapyExceptionHandlingEmail($event->data, $userEmail));
        }
    }
}
