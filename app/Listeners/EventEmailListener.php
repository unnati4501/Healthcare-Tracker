<?php

namespace App\Listeners;

use App\Events\EventBookedEvent;
use App\Events\EventUpdatedEvent;
use App\Events\EventPendingEvent;
use App\Events\EventRejectedEvent;
use App\Events\EventExpiredEvent;
use App\Events\EventStatusChangeEvent;
use App\Events\SendEventCancelledEvent;
use App\Events\SendEventEmailNotesEvent;
use App\Events\SendEventReminderEvent;
use App\Mail\EventBookedEmail;
use App\Mail\EventReminderEmail;
use App\Mail\EventUpdatedEmail;
use App\Mail\EventPendingEmail;
use App\Mail\EventRejectedEmail;
use App\Mail\EventExpiredEmail;
use App\Mail\EventStatusChangeEmail;
use App\Mail\SendEventCancelledEmail;
use App\Mail\SendEventEmailNotesEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class EventEmailListener implements ShouldQueue
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
            $zendeskEmail = config('zevolifesettings.mail-zendesk-event.production.email');
        } elseif ($appEnvironment == 'uat') {
            $zendeskEmail =  config('zevolifesettings.mail-zendesk-event.uat.email');
        } elseif ($appEnvironment == 'local') {
            $zendeskEmail =  config('zevolifesettings.mail-zendesk-event.local.email');
        } elseif ($appEnvironment == 'dev') {
            $zendeskEmail =  config('zevolifesettings.mail-zendesk-event.dev.email');
        } else {
            $zendeskEmail =  config('zevolifesettings.mail-zendesk-event.qa.email');
        }
        if ($event instanceof EventBookedEvent) {
            // recipients array
            $recipients = [$event->data['email']];
            // Event booked email
            Mail::to($recipients)->queue(new EventBookedEmail($event->data));
        } elseif ($event instanceof SendEventCancelledEvent) {
            // recipients array
            $recipients = [$event->user->email];
            // Event cancelled email
            Mail::to($recipients)->queue(new SendEventCancelledEmail($event->user, $event->data));
        } elseif ($event instanceof EventUpdatedEvent) {
            // recipients array
            $recipients = [$event->user->email];
            // Event updated email
            Mail::to($recipients)->queue(new EventUpdatedEmail($event->user, $event->data));
        } elseif ($event instanceof SendEventReminderEvent) {
            // recipients array
            $recipients = [$event->user->email];
            // Event cancelled email
            Mail::to($recipients)->queue(new EventReminderEmail($event->user, $event->data));
        } elseif ($event instanceof SendEventEmailNotesEvent) {
            // recipients array
            $recipients = [$event->user->email];
            // Event email notes email
            Mail::to($recipients)->queue(new SendEventEmailNotesEmail($event->user, $event->data));
        }  elseif ($event instanceof EventStatusChangeEvent) {
            // recipients array
            if (!empty($event->data['email'])) {
                $recipients = [$event->data['email']];
            } else {
                $recipients = [$zendeskEmail];
            }
            // Event booked email
            Mail::to($recipients)->queue(new EventStatusChangeEmail($event->data));
        }
    }
}
