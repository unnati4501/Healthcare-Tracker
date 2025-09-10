<?php

namespace App\Listeners;

use App\Events\ContactUsEvent;
use App\Mail\ContactUsEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendContactUsEmailListener implements ShouldQueue
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
    public function handle(ContactUsEvent $event)
    {
        $appEnvironment = app()->environment();
        
        if ($appEnvironment == 'production' || $appEnvironment == 'uat') {
            $userEmail = (!empty($event->data['type']) && $event->data['type'] == 'clinical' ? config('zevolifesettings.mail-zendesk-admin.zevotherapy') : config('zevolifesettings.mail-zendesk-admin.uat'));
        } else {
            $userEmail =  config('zevolifesettings.mail-zendesk-admin.local');
        }

        if ($event instanceof ContactUsEvent) {
            $toUsers = [
                $userEmail,
            ];
            Mail::to($toUsers)
                ->queue(new ContactUsEmail($event->data));
        }
    }
}
