<?php

namespace App\Console\Commands;

use App\Jobs\SendGeneralPushNotification;
use App\Models\HsSurvey;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendHealthScoreSurvey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:survey';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send wellbeing survey to users';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(User $user, HsSurvey $hssurvey)
    {
        parent::__construct();
        $this->user     = $user;
        $this->hssurvey = $hssurvey;
    }

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
            $now       = Carbon::now();
            $lastMonth = Carbon::now()->subHours(720);

            $allUsers = $this->user->where('can_access_app', 1)->where('is_blocked', 0)->get();

            $surveyUsers    = $this->hssurvey->groupBy('user_id')->latest('user_id')->get();
            $surveyUsersIds = $surveyUsers->pluck('user_id')->toArray();

            $eligibleUsers = $allUsers->filter(function ($value) use ($surveyUsersIds, $now) {
                return !in_array($value->id, $surveyUsersIds) && $value->created_at < $now;
            });

            $oldSurveyUserIds = $surveyUsers->filter(function ($value) use ($lastMonth) {
                return !is_null($value->survey_complete_time) && $value->survey_complete_time < $lastMonth;
            })->pluck('user_id')->toArray();

            $oldEligibleUsers = $this->user->where('can_access_app', 1)
                ->where('is_blocked', 0)
                ->whereIn('id', $oldSurveyUserIds)
                ->get();

            $allEligibleUsers = $eligibleUsers->merge($oldEligibleUsers);

            $surveyData = [];
            foreach ($allEligibleUsers as $value) {
                if (!isset($value->company->first()->id)) {
                    continue;
                }

                $notification_setting = $value
                    ->notificationSettings()
                    ->select('flag')
                    ->where('flag', 1)
                    ->where(function ($query) {
                        $query->where('module', '=', 'nps')
                            ->orWhere('module', '=', 'all');
                    })
                    ->first();

                $value->update(['hs_show_banner' => true]);

                $surveyData = [
                    'company_id'         => $value->company->first()->id,
                    'department_id'      => $value->company->first()->pivot->department_id,
                    'team_id'            => $value->company->first()->pivot->team_id,
                    'user_id'            => $value->id,
                    'title'              => 'Wellbeing survey',
                    'rolled_out_to_user' => $now,
                ];

                HsSurvey::insert($surveyData);

                $checkAuditSurveyAccess = getCompanyPlanAccess($value, 'audit-survey');

                if ($checkAuditSurveyAccess) {
                    \dispatch(new SendGeneralPushNotification($value, 'survey-reminder', [
                        'push'       => ($notification_setting->flag ?? false),
                        'company_id' => $value->company->first()->id,
                        'user_id'    => $value->id,
                    ]));
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
