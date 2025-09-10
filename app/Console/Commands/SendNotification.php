<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Illuminate\Console\Command;

class SendNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send all notifications to users which are scheduled before current time';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $cronData = [
            'cron_name'  => class_basename(__CLASS__),
            'unique_key' => generateProcessKey(),
        ];

        cronlog($cronData);

        try {
            // fetch all notifications which are pending to send
            Notification::join('notification_user', 'notifications.id', '=', 'notification_user.notification_id')
                ->join('users', 'users.id', '=', 'notification_user.user_id')
                ->select('users.id as recepient_id', 'users.timezone', 'notification_user.sent', 'notifications.id', 'notifications.title', 'notifications.message', 'notifications.deep_link_uri', 'notifications.creator_timezone', 'notifications.push', 'notifications.is_mobile')
                ->where('notification_user.sent', false)
                ->where('notifications.is_mobile', true)
                ->where('notifications.scheduled_at', '<=', now()->toDateTimeString())
                ->get()
                ->each
                ->sendNotification();

            cronlog($cronData, 1);
        } catch (\Exception $exception) {
            \Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
            $cronData['is_exception'] = 1;
            $cronData['log_desc']     = $exception->getMessage();
            cronlog($cronData, 1);
        }
    }
}
