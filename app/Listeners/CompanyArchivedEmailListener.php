<?php

namespace App\Listeners;

use App\Events\CompanyArchivedEvent;
use App\Mail\CompanyArchivedEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use App\Models\AdminAlert;

class CompanyArchivedEmailListener implements ShouldQueue
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
    public function handle(CompanyArchivedEvent $event)
    {
        $toUsers = AdminAlert::join('admin_alert_users', 'admin_alert_users.alert_id', '=', 'admin_alerts.id')
        ->select('admin_alert_users.user_email','admin_alert_users.user_name')->where('admin_alerts.title','Digital Therapy Record Deletion')->pluck('user_email')->toArray();

        if ($event instanceof CompanyArchivedEvent) {
            Mail::to($toUsers)
                ->queue(new CompanyArchivedEmail($event->company, $event->user, $event->tempPath, $event->fileName));
        }
    }
}
