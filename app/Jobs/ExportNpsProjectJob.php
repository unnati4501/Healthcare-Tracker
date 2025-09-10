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
use App\Events\NpsProjectExportEvent;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use DB;

class ExportNpsProjectJob implements ShouldQueue
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
            $appTimeZone = config('app.timezone');
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
           
            $userCompany = $this->user->company()->first();
            $userRole    = $this->user->roles()->whereIn('slug', ['super_admin','company_admin'])->first();

            $now         = \now(config('app.timezone'))->toDateString();
            $userNPSData = \DB::table('nps_project')
                ->leftJoin('user_nps_project_logs', 'nps_project.id', '=', 'user_nps_project_logs.nps_project_id')
                ->select(
                    'nps_project.title as project_title',
                    'nps_project.type as project_type',
                    DB::raw("DATE_FORMAT(nps_project.start_date,'%M %d, %Y') as start_date"),
                    DB::raw("DATE_FORMAT(nps_project.end_date,'%M %d, %Y') as end_date"),
                    DB::raw("count(user_nps_project_logs.nps_project_id) as response"),
                )
                ->selectRaw('(CASE WHEN nps_project.start_date <= ? AND nps_project.end_date >= ? THEN "Active" WHEN nps_project.start_date > ? THEN "Upcoming" ELSE "Expired" END) AS status',[
                    $now,$now,$now
                ])
                ->join("companies", "companies.id", "=", "nps_project.company_id")
                ->join("company_locations", function ($join) {
                    $join->on("company_locations.company_id", "=", "companies.id")
                        ->where("company_locations.default", true);
                })
                ->groupBy("nps_project.id")
                ->orderBy("nps_project.id", "DESC");
                
            if (!empty($userRole) && $userRole->slug == "company_admin") {
                $userNPSData->where("nps_project.company_id", "=", $userCompany->id);
            } else {
                $userNPSData->whereRaw("nps_project.start_date <= DATE(CONVERT_TZ(?, ?, company_locations.timezone))",[
                    $now,$appTimeZone
                ]);
            }

            if (!empty($queryStr['projecttextSearch'])) {
                $userNPSData->where("nps_project.title", "like", '%' . $queryStr['projecttextSearch'] . '%');
            }

            if (!empty($queryStr['projectcompany'])) {
                $userNPSData->where("companies.id", "=", $queryStr['projectcompany']);
            }

            if (!empty($startDate)) {
                $userNPSData->where("nps_project.start_date", ">=", $startDate);
            }

            if (!empty($endDate)) {
                $userNPSData->where("nps_project.start_date", "<=", $endDate);
            }

            if (!empty($queryStr['projectStatus']) && $queryStr['projectStatus'] != "all") {
                if ($queryStr['projectStatus'] == "active") {
                    $userNPSData->where(function ($query) use ($now) {
                        $query->where("nps_project.start_date", "<=", $now)
                            ->Where("nps_project.end_date", ">=", $now);
                    });
                } elseif ($queryStr['projectStatus'] == "upcoming") {
                    $userNPSData->whereRaw("nps_project.start_date > DATE(CONVERT_TZ(?, ?, company_locations.timezone))",[
                        $now,$appTimeZone
                    ]);
                } else {
                    $userNPSData->whereRaw("nps_project.end_date < DATE(CONVERT_TZ(?, ?, company_locations.timezone))",[
                        $now,$appTimeZone
                    ]);
                }
            }
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
                'Project Name',
                'Project Type',
                'Start Date',
                'End Date',
                'Responses',
                'Status'
            ];

            $fileName = "ProjectTab Feedback_".$dateTimeString.'.xlsx';

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
            
            event(new NpsProjectExportEvent($this->user, $url, $this->payload, $fileName));
            return true;
            
        } catch (\Exception $exception) {
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}
