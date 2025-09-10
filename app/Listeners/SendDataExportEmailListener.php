<?php

namespace App\Listeners;

use App\Events\SendDataExportEmailEvent;
use App\Mail\SendDataExportEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendDataExportEmailListener implements ShouldQueue
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
    }

    /**
     * Handle the event.
     *
     * @param  mixed EventBookedEvent|SendEventCancelledEvent|EventUpdatedEvent $event
     * @return void
     */
    public function handle($event)
    {
        $appEnvironment = app()->environment();

        if ($appEnvironment == 'production') {
            $email = config('data-extract.irishlife_data_extract.emails.production');
        } elseif ($appEnvironment == 'uat') {
            $email = config('data-extract.irishlife_data_extract.emails.uat');
        } elseif ($appEnvironment == 'local') {
            $email = config('data-extract.irishlife_data_extract.emails.local');
        } elseif ($appEnvironment == 'dev') {
            $email = config('data-extract.irishlife_data_extract.emails.dev');
        } else {
            $email = config('data-extract.irishlife_data_extract.emails.qa');
        }

        if ($event instanceof SendDataExportEmailEvent) {
            // recipients array
            $recipients = $email;
            // Event booked email
            Mail::to($recipients)->queue(new SendDataExportEmail($event->data));
        }
    }
}
