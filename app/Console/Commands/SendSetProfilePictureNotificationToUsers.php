<?php

namespace App\Console\Commands;

use App\Jobs\SendSetProfilePictureReminderPushNotification;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendSetProfilePictureNotificationToUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:setprofilepicture';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notification will sent to users after 7 days of registration for set profile picture';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $cronData = [
            'cron_name'  => class_basename(__CLASS__),
            'unique_key' => generateProcessKey(),
        ];

        cronlog($cronData);

        try {
            $appTimeZone = config('app.timezone');
            $now         = now($appTimeZone);
            $usersChunk  = User::select('users.id', 'users.timezone')
                ->leftJoin('media', function ($join) {
                    $join
                        ->on('media.model_id', '=', 'users.id')
                        ->where('media.model_type', 'like', '%\User')
                        ->where('media.collection_name', '=', 'logo');
                })
                ->where('users.can_access_app', 1)
                ->where('users.is_blocked', 0)
                ->where('users.created_at', '<=', $now->subDays(7)->setTime(23, 59, 59, 0)->toDateTimeString())
                ->whereNotNull('start_date')
                ->whereNull(['media.id', 'users.set_profile_picture_reminder_at'])
                ->get()
                ->chunk(500);

            $usersChunk->each(function ($users) {
                \dispatch(new SendSetProfilePictureReminderPushNotification($users));
            });

            cronlog($cronData, 1);
        } catch (\Exception $exception) {
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
            $cronData['is_exception'] = 1;
            $cronData['log_desc']     = $exception->getMessage();
            cronlog($cronData, 1);
        }
    }
}
