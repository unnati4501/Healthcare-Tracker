<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\NpsProject;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;

class SendProjectSurveyToCompanyUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'projectsurvey:userrollout';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send project survey notifications to company users';

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

            $data = NpsProject::join("companies", "companies.id", "=", "nps_project.company_id")
                            ->join('company_locations', function ($join) {
                                $join->on("company_locations.company_id", "=", "companies.id")
                                    ->where("company_locations.default", true);
                            })
                            ->where('nps_project.survey_sent', false)
                            ->where('nps_project.type', 'system')
                            ->whereRaw(
                                "nps_project.start_date = DATE_FORMAT(CONVERT_TZ(?, ?, company_locations.timezone),'%Y-%m-%d')"
                            ,[$now,$appTimeZone])
                            ->where('companies.subscription_start_date', '<=', $now)
                            ->where('companies.subscription_end_date', '>=', $now)
                            ->select('nps_project.*')
                            ->get();

            foreach ($data as $value) {
                $value->triggerProjectSurvey();
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
