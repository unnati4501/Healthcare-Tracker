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
use App\Events\MasterclassFeedbackReportExportEvent;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use DB;

class ExportMasterclassFeedbackReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $targetType;
    public $payload;
    public $user;
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
            $timezone    = (!empty($this->user->timezone) ? $this->user->timezone : $appTimezone);
            $queryStr    = json_decode($this->payload['queryString'], true);

            if (!empty($this->payload['start_date'])) {
                $startDate = $this->payload['start_date'] . '00:00';
                $startDate = Carbon::parse($startDate, $timezone)->setTimeZone(config('app.timezone'))->toDateTimeString();
            }
            if (!empty($this->payload['end_date'])) {
                $endDate   = $this->payload['end_date'] . '23:59:59';
                $endDate   = Carbon::parse($endDate, $timezone)->setTimeZone(config('app.timezone'))->toDateTimeString();
            }

            $query = \DB::table('masterclass_csat_user_logs')
            ->select(
                'sub_categories.name AS category_name',
                'courses.title AS course_title',
                'companies.name AS company_name',
                \DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS author_name"),
                \DB::raw('(CASE WHEN masterclass_csat_user_logs.feedback_type = "very_happy" THEN "Very Happy" WHEN masterclass_csat_user_logs.feedback_type = "happy" THEN "Happy" WHEN masterclass_csat_user_logs.feedback_type = "neutral" THEN "Neutral" WHEN masterclass_csat_user_logs.feedback_type = "unhappy" THEN "Unhappy" WHEN masterclass_csat_user_logs.feedback_type = "very_unhappy" THEN "Very Unhappy" ELSE "Very Happy" END) AS feedback_type'),
                'masterclass_csat_user_logs.feedback',
            )->selectRaw("DATE_FORMAT(CONVERT_TZ(masterclass_csat_user_logs.created_at, ?, ?), '%b %d, %Y, %H:%i')",[
                $appTimezone,$timezone
            ])
            ->join('courses', 'courses.id', '=', 'masterclass_csat_user_logs.course_id')
            ->join('users', 'users.id', '=', 'courses.creator_id')
            ->join('companies', 'companies.id', '=', 'masterclass_csat_user_logs.company_id')
            ->join('sub_categories', 'sub_categories.id', 'courses.sub_category_id');

            if (!empty($startDate) && !empty($endDate)) {
                $query->whereBetween('masterclass_csat_user_logs.created_at', [$startDate, $endDate]);
            } elseif (!empty($startDate) && empty($endDate)) {
                $query->where('masterclass_csat_user_logs.created_at', '>=', $startDate);
            } elseif (empty($startDate) && !empty($endDate)) {
                $query->where('masterclass_csat_user_logs.created_at', '<=', $endDate);
            }
            if (!empty($queryStr['category'])) {
                $query->where('courses.sub_category_id', $queryStr['category']);
            }
            if (!empty($queryStr['course'])) {
                $query->where('courses.id', $queryStr['course']);
            }
            if (!empty($queryStr['company'])) {
                $query->where("masterclass_csat_user_logs.company_id", $queryStr['company']);
            }
            if (!empty($queryStr['author'])) {
                $query->where('courses.creator_id', $queryStr['author']);
            }
            if (!empty($queryStr['feedback']) && $queryStr['feedback'] != 'all') {
                $query->where("masterclass_csat_user_logs.feedback_type", $queryStr['feedback']);
            }

            $query->orderByDesc('masterclass_csat_user_logs.updated_at');
           
            $userNPSDataRecords = $query->get()->chunk(100);
            if ($query->get()->count() == 0) {
                $messageData = [
                    'data'   => trans('challenges.messages.export_error'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.reports.masterclass-feedback')->with('message', $messageData);
            }
            $dateTimeString = Carbon::now()->toDateTimeString();

            $sheetTitle = [
                'Masterclass Category',
                'Masterclass Name',
                'Company Name',
                'Author Name',
                'Feedback Type',
                'Notes',
                'Date',
            ];

            $fileName = "Masterclass Feedback_".$dateTimeString.'.xlsx';

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
            
            event(new MasterclassFeedbackReportExportEvent($this->user, $url, $this->payload, $fileName));
            return true;
            
        } catch (\Exception $exception) {
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}
