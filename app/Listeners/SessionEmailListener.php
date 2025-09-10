<?php

namespace App\Listeners;

use App\Events\SendSessionCancelledEvent;
use App\Mail\SendSessionCancelledEmail;
use App\Events\SendSessionBookedEvent;
use App\Mail\SendSessionBookedEmail;
use App\Events\SendSessionRescheduledEvent;
use App\Mail\SendSessionRescheduledEmail;
use App\Mail\SendSessionConsentEmail;
use App\Events\SendSessionNotesReminderEvent;
use App\Mail\SendSessionNotesReminderEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use App\Events\SendEmailConsentEvent;
use App\Models\AdminAlert;

class SessionEmailListener implements ShouldQueue
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
     * @param  mixed SendSessionCancelledEvent $event
     * @return void
     */
    public function handle($event)
    {
        $bccUsers = [];
        $bccUsers = AdminAlert::join('admin_alert_users', 'admin_alert_users.alert_id', '=', 'admin_alerts.id')
            ->select('admin_alert_users.user_email','admin_alert_users.user_name')->where('admin_alerts.title','Digital Therapy Emails')->pluck('user_email')->toArray();

        if ($event instanceof SendSessionCancelledEvent) {
            // recipients array
            $recipients = [$event->data['email']];
            // Event cancelled email
            $mail = Mail::to($recipients);
            if (sizeof($bccUsers) > 0){
                $mail->bcc($bccUsers);
            } 
            $mail->queue(new SendSessionCancelledEmail($event->data));
        }
        if ($event instanceof SendSessionBookedEvent) {
            // recipients array
            $recipients = [$event->data['email']];
            // Event booked email
            $mail = Mail::to($recipients);
            if (sizeof($bccUsers) > 0){
                $mail->bcc($bccUsers);
            } 
            $mail->queue(new SendSessionBookedEmail($event->data));
        }
        if ($event instanceof SendSessionRescheduledEvent) {
            // recipients array
            $recipients = [$event->data['email']];
            // Event rescheduled email
            $mail = Mail::to($recipients);
            if (sizeof($bccUsers) > 0){
                $mail->bcc($bccUsers);
            } 
            $mail->queue(new SendSessionRescheduledEmail($event->data));
        }
        if ($event instanceof SendEmailConsentEvent) {
            // recipients array
            $recipients = [$event->data['email']];
            // Event rescheduled email
            Mail::to($recipients)->queue(new SendSessionConsentEmail($event->data));
        }
        if ($event instanceof SendSessionNotesReminderEvent) {
            // recipients array
            $recipients = [$event->data['email']];
            // notes reminder email
            Mail::to($recipients)->queue(new SendSessionNotesReminderEmail($event->data));
        }
    }
}
