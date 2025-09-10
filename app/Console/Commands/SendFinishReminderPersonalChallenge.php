<?php

namespace App\Console\Commands;

use App\Models\PersonalChallengeUser;
use DB;
use Illuminate\Console\Command;

class SendFinishReminderPersonalChallenge extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'personalchallenge:finishreminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notifications about personal challenge end reminder';

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
            $now         = \now(config('app.timezone'))->toDateTimeString();
            $appTimeZone = config('app.timezone');

            PersonalChallengeUser::select('personal_challenge_users.*')
                ->join('users', 'users.id', '=', 'personal_challenge_users.user_id')
                ->where('personal_challenge_users.joined', 1)
                ->where('personal_challenge_users.completed', 0)
                ->whereRaw(
                    "CONVERT_TZ(personal_challenge_users.end_date, ?, users.timezone) <= CONVERT_TZ(?, ?, users.timezone)"
                ,[$appTimeZone,$now,$appTimeZone])
                ->get()
                ->each
                ->sendFinishReminderNotification();

            cronlog($cronData, 1);
        } catch (\Exception $exception) {
            \Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
            $cronData['is_exception'] = 1;
            $cronData['log_desc']     = $exception->getMessage();
            cronlog($cronData, 1);
        }
    }
}
