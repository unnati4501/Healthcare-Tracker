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
use App\Events\CounsellorFeedbackReportExportEvent;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use DB;

class ExportCounsellorFeedbackReportJob implements ShouldQueue
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
            $queryStr    = json_decode($this->payload['queryString'], true);

            $timezone    = (!empty($this->user->timezone) ? $this->user->timezone : $appTimezone);
            
            if (!empty($this->payload['start_date'])) {
                $startDate = $this->payload['start_date'] . '00:00';
                $startDate = Carbon::parse($startDate, $timezone)->setTimeZone(config('app.timezone'))->toDateTimeString();
            }
            if (!empty($this->payload['end_date'])) {
                $endDate   = $this->payload['end_date'] . '23:59:59';
                $endDate   = Carbon::parse($endDate, $timezone)->setTimeZone(config('app.timezone'))->toDateTimeString();
            }
            
            $query = \DB::table('eap_csat_user_logs')
            ->select(
                \DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS counsellor_name"),
                'users.email as counsellor_email',
                'companies.name AS company_name',
                \DB::raw("TIMESTAMPDIFF(MINUTE,eap_calendly.start_time,eap_calendly.end_time) as duration"),
                \DB::raw('(CASE WHEN eap_csat_user_logs.feedback_type = "very_happy" THEN "Very Happy" WHEN eap_csat_user_logs.feedback_type = "happy" THEN "Happy" WHEN eap_csat_user_logs.feedback_type = "neutral" THEN "Neutral" WHEN eap_csat_user_logs.feedback_type = "unhappy" THEN "Unhappy" WHEN eap_csat_user_logs.feedback_type = "very_unhappy" THEN "Very Unhappy" ELSE "Very Happy" END) AS feedback_type'),
                'eap_csat_user_logs.feedback as feedback_text',
            )
            ->selectRaw("DATE_FORMAT(CONVERT_TZ(eap_csat_user_logs.created_at, ?, ?), '%b %d, %Y, %H:%i')",[
                $appTimezone,$timezone
            ])
            ->join('eap_calendly', 'eap_calendly.id', '=', 'eap_csat_user_logs.eap_calendy_id')
            ->join('users', 'users.id', '=', 'eap_calendly.therapist_id')
            ->join('companies', 'companies.id', '=', 'eap_csat_user_logs.company_id');
            
            if (!empty($queryStr['timeDuration'])) {
                $query = $query
                ->where(function ($query) use ($queryStr) {
                    $last24Hours = Carbon::now()->subDays()->toDateTimeString();
                    $last7Days   = Carbon::now()->subDays(7)->toDateTimeString();
                    $last30Days  = Carbon::now()->subDays(30)->toDateTimeString();

                    if ($queryStr['timeDuration'] == 'last_24') {
                        $query->where('eap_csat_user_logs.created_at', '>=', $last24Hours);
                    } elseif ($queryStr['timeDuration'] == 'last_7') {
                        $query->where('eap_csat_user_logs.created_at', '>=', $last7Days);
                    } elseif ($queryStr['timeDuration'] == 'last_30') {
                        $query->where('eap_csat_user_logs.created_at', '>=', $last30Days);
                    } else {
                        $query->whereNotNull('eap_csat_user_logs.created_at');
                    }
                });
            }

            if (!empty($queryStr['company'])) {
                $query->where("eap_csat_user_logs.company_id", $queryStr['company']);
            }
            if (!empty($queryStr['counsellor'])) {
                $query->where(DB::raw("CONCAT(users.first_name,' ',users.last_name)"), 'like', '%' . $queryStr['counsellor'] . '%');
            }
            if (!empty($queryStr['feedback']) && $queryStr['feedback'] != 'all') {
                $query->where("eap_csat_user_logs.feedback_type", $queryStr['feedback']);
            }

            if (!empty($startDate) && !empty($endDate)) {
                $query->whereBetween('eap_csat_user_logs.created_at', [$startDate, $endDate]);
            } elseif (!empty($startDate) && empty($endDate)) {
                $query->where('eap_csat_user_logs.created_at', '>=', $startDate);
            } elseif (empty($startDate) && !empty($endDate)) {
                $query->where('eap_csat_user_logs.created_at', '<=', $endDate);
            }
            $query->orderByDesc('eap_csat_user_logs.updated_at');
           
            $userNPSDataRecords = $query->get()->chunk(100);
            if ($query->get()->count() == 0) {
                $messageData = [
                    'data'   => trans('challenges.messages.export_error'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.reports.eap-feedback')->with('message', $messageData);
            }
            $dateTimeString = Carbon::now()->toDateTimeString();

            $sheetTitle = [
                'Counsellor Name',
                'Counsellor Email',
                'Company Name',
                'Duration(Mins)',
                'Feedback Type',
                'Feedback Text',
                'Date/Time',
            ];

            $fileName = "Counsellor Feedback_".$dateTimeString.'.xlsx';

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
            
            event(new CounsellorFeedbackReportExportEvent($this->user, $url, $this->payload, $fileName));
            return true;
            
        } catch (\Exception $exception) {
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}
