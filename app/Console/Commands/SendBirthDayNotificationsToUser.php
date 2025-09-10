<?php
declare (strict_types = 1);

namespace App\Console\Commands;

use App\Jobs\SendBirthDayPushNotificationJob;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendBirthDayNotificationsToUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:birthday';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send birthday wish to user as a push notification.';

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
            $users = User::select('users.id', 'users.first_name', 'users.timezone', 'user_profile.birth_date')
                ->leftJoin('user_profile', 'user_profile.user_id', '=', 'users.id')
                ->where('can_access_app', 1)
                ->where('is_blocked', 0)
                ->whereDay('user_profile.birth_date', '=', date('d'))
                ->whereMonth('user_profile.birth_date', '=', date('m'))
                ->whereNotNull('user_profile.birth_date')
                ->get();

            foreach ($users as $user) {
                $date = date('Y-m-d 07:59:00');
                $time = Carbon::parse($date, $user->timezone)->setTimezone(config('app.timezone'))->todatetimeString();

                // send push notification to user for birthday wish
                \dispatch(new SendBirthDayPushNotificationJob($user, 'birthday-greet', [
                    'type'         => 'Manual',
                    'scheduled_at' => $time,
                    'push'         => true,
                ]));
            }

            cronlog($cronData, 1);
        } catch (\Exception $exception) {
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
            $cronData['is_exception'] = 1;
            $cronData['log_desc']     = $exception->getMessage();
            cronlog($cronData, 1);
        }
    }
}
