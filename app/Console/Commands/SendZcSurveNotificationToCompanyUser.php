<?php

namespace App\Console\Commands;

use App\Models\Company;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;

class SendZcSurveNotificationToCompanyUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zcsurvey:userrollout {company? : Send survey to specified company.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send company survey notifications to company users';

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
            $now         = now($appTimeZone)->toDateTimeString();
            $argCompany  = $this->argument('company');
            $companies   = Company::select('companies.id', 'companies.name', 'companies.is_branding', 'companies.zcsurvey_on_email', 'company_locations.timezone', 'companies.parent_id', 'companies.is_reseller', 'zc_survey_settings.survey_id', 'zc_survey_settings.survey_frequency', 'zc_survey_settings.survey_roll_out_day', 'zc_survey_settings.survey_roll_out_time', 'zc_survey_settings.survey_to_all')
                ->join('zc_survey_settings', 'zc_survey_settings.company_id', '=', 'companies.id')
                ->join('company_locations', function ($join) {
                    $join
                        ->on("company_locations.company_id", "=", "companies.id")
                        ->where("company_locations.default", true);
                })
                ->whereNotNull('zc_survey_settings.survey_id')
                ->whereRaw(
                    "LOWER(zc_survey_settings.survey_roll_out_day) = LOWER(DAYNAME(CONVERT_TZ(?, ?, company_locations.timezone)))"
                ,[$now,$appTimeZone])
                ->whereRaw(
                    "zc_survey_settings.survey_roll_out_time <= DATE_FORMAT(CONVERT_TZ(?, ?, company_locations.timezone),'%H:%i:%s')"
                ,[$now,$appTimeZone])
                ->where('companies.subscription_start_date', '<=', $now)
                ->where('companies.subscription_end_date', '>=', $now)
                ->where('companies.enable_survey', true)
                ->when($argCompany, function ($query, $companyId) {
                    $query->where('companies.id', $companyId);
                })
                ->get();

            foreach ($companies as $company) {
                $sendSurvey     = false;
                $lastSurveySend = $company->companySurveyLog()->select('id', 'roll_out_date', 'expire_date')->orderByDesc("id")->first();

                if (empty($lastSurveySend)) {
                    $sendSurvey = true;
                } else {
                    $currentDay   = now($company->timezone);
                    $previousTime = Carbon::parse($lastSurveySend->roll_out_date, $appTimeZone)->setTimezone($company->timezone);
                    $expireTime   = Carbon::parse($lastSurveySend->expire_date, $appTimeZone)->setTimezone($company->timezone);
                    $diffInHours  = $previousTime->diffInHours($currentDay);
                    $surveyHr     = config('zevolifesettings.survey_frequency_day.' . $company->survey_frequency) * 24;

                    if (($diffInHours >= $surveyHr) || ($expireTime <= $currentDay)) {
                        $sendSurvey = true;
                    }
                }

                if ($sendSurvey) {
                    $company->triggerZcSurvey();
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
