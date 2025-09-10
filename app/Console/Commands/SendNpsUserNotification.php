<?php

namespace App\Console\Commands;

use App\Models\User;
use DB;
use Illuminate\Console\Command;

class SendNpsUserNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:npsfeedback';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notifications to get feedback as a NPS';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(): void
    {
        $cronData = [
            'cron_name'  => class_basename(__CLASS__),
            'unique_key' => generateProcessKey(),
        ];

        cronlog($cronData);

        try {
            $now = \now(config('app.timezone'))->toDateTimeString();

            // fetch all users whose registration date + 7 days is greater than or equals to current time
            User::whereRaw("TIMESTAMPDIFF(DAY, users.created_at, ?) >= 7",[$now])
                ->whereNotIn('users.id', function ($query) {
                    $query->select(DB::raw('DISTINCT(user_id)'))->from('user_nps_survey_logs');
                })
                ->select('users.*')
                ->where(function ($query) {
                    $query->where('users.can_access_app', 1)
                        ->orWhere('users.can_access_portal', 1);
                })
                ->get()
                ->each
                ->sendSurveyLink();

            // fetch all users who have not submitted app feedback yet
            User::join(\DB::raw("(SELECT unsl1.* from user_nps_survey_logs unsl1 LEFT JOIN user_nps_survey_logs unsl2 ON (unsl1.user_id = unsl2.user_id AND unsl1.id < unsl2.id) where unsl2.id IS NULL) as unsl"), 'unsl.user_id', '=', 'users.id')
                ->whereRaw("TIMESTAMPDIFF(DAY, unsl.survey_sent_on, ?) >= 30",[$now])
                ->whereNull('unsl.feedback_type')
                ->whereNull('unsl.survey_received_on')
                ->select('users.*')
                ->where(function ($query) {
                    $query->where('users.can_access_app', 1)
                        ->orWhere('users.can_access_portal', 1);
                })
                ->get()
                ->each
                ->sendSurveyLink(true);

            // fetch all users who have not submitted app feedback yet
            User::join(\DB::raw("(SELECT unsl1.* from user_nps_survey_logs unsl1 LEFT JOIN user_nps_survey_logs unsl2 ON (unsl1.user_id = unsl2.user_id AND unsl1.id < unsl2.id) where unsl2.id IS NULL) as unsl"), 'unsl.user_id', '=', 'users.id')
                ->whereRaw("TIMESTAMPDIFF(DAY, unsl.survey_received_on, ?) >= 90",[$now])
                ->whereNotNull('unsl.feedback_type')
                ->whereNotNull('unsl.survey_received_on')
                ->select('users.*')
                ->where(function ($query) {
                    $query->where('users.can_access_app', 1)
                        ->orWhere('users.can_access_portal', 1);
                })
                ->get()
                ->each
                ->sendSurveyLink();

            cronlog($cronData, 1);
        } catch (\Exception $exception) {
            \Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
            $cronData['is_exception'] = 1;
            $cronData['log_desc']     = $exception->getMessage();
            cronlog($cronData, 1);
        }
    }
}
