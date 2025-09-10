<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Events\NpsExportReportEvent;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use DB;

class ExportNpsReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $targetType;
    public $payload;
    /**
     * @var User $user
     */
    protected $user;
    public $columnName;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($payload, User $user)
    {
        $this->queue                  = 'mail';
        $this->payload                = $payload;
        $this->user                   = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $appTimezone = config('app.timezone');
            $timezone    = !empty($this->user->timezone) ? $this->user->timezone : config('app.timezone');
            $queryStr    = json_decode($this->payload['queryString'], true);

            if (!empty($this->payload['start_date'])) {
                $startDate = $this->payload['start_date'] . '00:00';
                $startDate = Carbon::parse($startDate, $timezone)->setTimeZone(config('app.timezone'))->toDateTimeString();
            }
            if (!empty($this->payload['end_date'])) {
                $endDate   = $this->payload['end_date'] . '23:59:59';
                $endDate   = Carbon::parse($endDate, $timezone)->setTimeZone(config('app.timezone'))->toDateTimeString();
            }
           
            $role        = getUserRole($this->user);
            $companyData = $this->user->company()->first();

            $userNPSData = \DB::table('user_nps_survey_logs')
            ->select(
                'companies.name as companyName',
                \DB::raw('(CASE WHEN user_nps_survey_logs.feedback_type = "very_happy" THEN "Very Happy" WHEN user_nps_survey_logs.feedback_type = "happy" THEN "Happy" WHEN user_nps_survey_logs.feedback_type = "neutral" THEN "Neutral" WHEN user_nps_survey_logs.feedback_type = "unhappy" THEN "Unhappy" WHEN user_nps_survey_logs.feedback_type = "very_unhappy" THEN "Very Unhappy" ELSE "Very Happy" END) AS feedback_type'),
                'user_nps_survey_logs.feedback as Notes',
            )
            ->selectRaw("DATE_FORMAT(CONVERT_TZ(user_nps_survey_logs.survey_received_on, ?, ?), '%b %d, %Y, %H:%i')",[
                $appTimezone,$timezone
            ])
            ->join("users", "users.id", "=", "user_nps_survey_logs.user_id")
            ->join("user_team", "user_team.user_id", "=", "users.id")
            ->join("companies", "companies.id", "=", "user_team.company_id")
            ->whereNotNull('user_nps_survey_logs.survey_received_on')
            ->where('user_nps_survey_logs.is_portal', $this->payload['is_portal']);
            
            if (!empty($queryStr['company'])) {
                $userNPSData->where("companies.id", "=", $queryStr['company']);
            }

            if (!empty($queryStr['feedBackType'])) {
                $userNPSData->where("user_nps_survey_logs.feedback_type", "=", $queryStr['feedBackType']);
            }

            if (!empty($startDate) && !empty($endDate)) {
                $userNPSData->whereBetween('user_nps_survey_logs.survey_received_on', [$startDate, $endDate]);
            } elseif (!empty($startDate) && empty($endDate)) {
                $userNPSData->where('user_nps_survey_logs.survey_received_on', '>=', $startDate);
            } elseif (empty($startDate) && !empty($endDate)) {
                $userNPSData->where('user_nps_survey_logs.survey_received_on', '<=', $endDate);
            }

            if (!empty($role->group) && $role->group == 'reseller' && $companyData->parent_id == null) {
                $childCompany = Company::select('id')->where('parent_id', $companyData->id)->orWhere('id', $companyData->id)->get()->pluck('id')->toArray();
                $userNPSData->whereIn('companies.id', $childCompany);
            }
            $userNPSData->orderBy("user_nps_survey_logs.id", "DESC");

            $userNPSDataRecords = $userNPSData->get()->chunk(100);
            if ($userNPSData->get()->count() == 0) {
                $messageData = [
                    'data'   => trans('challenges.messages.export_error'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.reports.nps')->with('message', $messageData);
            }
            $dateTimeString = Carbon::now()->toDateTimeString();

            $sheetTitle = [
                'Company name',
                'Feedback Type',
                'Notes',
                'Date',
            ];

            $fileName = "App_Feedback_".$dateTimeString.'.xlsx';

            $spreadsheet = new Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();
            $sheet->fromArray($sheetTitle, null, 'A1');
            
            $index = 2;
            foreach ($userNPSDataRecords as $value) {
                $records = (count($value) > 0) ? json_decode(json_encode($value), true) : [];
                $sheet->fromArray($records, null, 'A'.$index);
                $index = $index + count($value);
            }
            
            $writer = new Xlsx($spreadsheet);
            $temp_file = tempnam(sys_get_temp_dir(), $fileName);
            $source = fopen($temp_file, 'rb');
            $writer->save($temp_file);
            
            $root     = config("filesystems.disks.spaces.root");
            $foldername = config('zevolifesettings.excelfolderpath');

            $uploaded = uploadFileToSpaces($source, "{$root}/{$foldername}/{$fileName}", "public");
            if (null != $uploaded && is_string($uploaded->get('ObjectURL'))) {
                $url      = $uploaded->get('ObjectURL');
                $uploaded = true;
            }
            
            event(new NpsExportReportEvent($this->user, $url, $this->payload, $fileName));
            return true;
            
        } catch (\Exception $exception) {
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}
