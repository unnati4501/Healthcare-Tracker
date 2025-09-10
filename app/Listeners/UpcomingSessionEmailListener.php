<?php

namespace App\Listeners;

use App\Events\UpcomingSessionEmailEvent;
use App\Mail\SendUpcomingSessionEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use App\Models\AdminAlert;

class UpcomingSessionEmailListener implements ShouldQueue
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
    public function handle(UpcomingSessionEmailEvent $event)
    {
        $bccUsers = [];
        $bccUsers = AdminAlert::join('admin_alert_users', 'admin_alert_users.alert_id', '=', 'admin_alerts.id')
            ->select('admin_alert_users.user_email','admin_alert_users.user_name')->where('admin_alerts.title','Digital Therapy Emails')->pluck('user_email')->toArray();

        if ($event instanceof UpcomingSessionEmailEvent) {
            $recipients = [$event->data['email']];
            $mail =  Mail::to($recipients);
            if (sizeof($bccUsers) > 0) {
                $mail->bcc($bccUsers);
            }
            $mail->queue(new SendUpcomingSessionEmail($event->data));
        }
    }
}
