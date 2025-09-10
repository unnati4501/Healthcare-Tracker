<?php

namespace App\Console\Commands;

use App\Jobs\SendSyncTrackerPushNotificationJob;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UserSyncTracker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:synctracker';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send sync tracker push notification to user.';

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
            // get timings to set notification
            $timings = config('zevolifesettings.sync_notification_timings');

            // get all users those needed to be sent notification
            $users = User::select(
                'users.id',
                'users.first_name',
                'users.timezone',
                'user_notification_settings.flag AS notification_flag'
            )
                ->join('user_device', 'user_device.user_id', '=', 'users.id')
                ->leftJoin('user_notification_settings', function ($join) {
                    $join->on('user_notification_settings.user_id', '=', 'users.id')
                        ->where('user_notification_settings.flag', '=', 1)
                        ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['datasync', 'all']);
                })
                ->where("user_device.tracker", "!=", "default")
                ->where("can_access_app", true)
                ->where("is_blocked", false)
                ->groupBy('users.id')
                ->get();

            // get total count of users
            $totalUsers = $users->count();

            if ($totalUsers > 0) {
                // get size of chunk based on total users and timings
                $chunkSize = (int) ceil($totalUsers / count($timings));

                // divide all users into specified chunk
                $userChunk = $users->chunk($chunkSize);

                foreach ($userChunk as $key => $users) {
                    foreach ($users as $user) {
                        if (!empty($user)) {
                            // get user notification time based on chunk
                            $time = Carbon::parse($timings[$key], $user->timezone)
                                ->setTimezone(config('app.timezone'))
                                ->todatetimeString();

                            // send push notification to user for sync trackers
                            \dispatch(new SendSyncTrackerPushNotificationJob($user, 'sync-tracker', [
                                'type'         => 'Manual',
                                'scheduled_at' => $time,
                                'push'         => $user->notification_flag,
                            ]));
                        }
                    }
                }
            }

            cronlog($cronData, 1);
        } catch (\Exception $exception) {
            \Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
            $cronData['is_exception'] = 1;
            $cronData['log_desc']     = $exception->getMessage();
            cronlog($cronData, 1);
        }
    }
}
