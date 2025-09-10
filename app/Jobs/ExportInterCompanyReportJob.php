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
use App\Events\InterCopmanyReportExportEvent;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use DB;

class ExportInterCompanyReportJob implements ShouldQueue
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
             
            if ($this->payload['tab'] == "ic-company") {
                $query = DB::table('challenge_wise_company_points')
                    ->join('challenges', 'challenges.id', '=', 'challenge_wise_company_points.challenge_id')
                    ->join('companies', 'companies.id', '=', 'challenge_wise_company_points.company_id')
                    ->where('challenge_wise_company_points.challenge_id', $queryStr['challenge'])
                    ->where(DB::raw("round(challenge_wise_company_points.points, 1)"), '>', 0)
                    ->select(
                        'challenge_wise_company_points.rank',
                        'companies.name AS company_name',
                        DB::raw("(SELECT COUNT(DISTINCT team_id) FROM challenge_wise_user_ponits where challenge_id = challenge_wise_company_points.challenge_id and company_id = challenge_wise_company_points.company_id) AS total_teams"),
                        DB::raw("(SELECT COUNT(DISTINCT user_id) FROM challenge_wise_user_ponits where challenge_id = challenge_wise_company_points.challenge_id and company_id = challenge_wise_company_points.company_id) AS total_users"),
                        DB::raw("round(challenge_wise_company_points.points, 1) as points"),
                    );
                if (!empty($startDate) && !empty($endDate)) {
                    $query->whereBetween('challenges.start_date', [$startDate, $endDate]);
                } elseif (!empty($startDate) && empty($endDate)) {
                    $query->where('challenges.start_date', '>=', $startDate);
                } elseif (empty($startDate) && !empty($endDate)) {
                    $query->where('challenges.start_date', '<=', $endDate);
                }
                if (!empty($queryStr['company'])) {
                    $query->where('challenge_wise_company_points.company_id', $queryStr['company']);
                }
                    $query->orderBy('challenge_wise_company_points.rank', 'ASC');
            } else {
                $query = DB::table('challenge_wise_team_ponits')
                    ->join('companies', 'companies.id', '=', 'challenge_wise_team_ponits.company_id')
                    ->join('challenges', 'challenges.id', '=', 'challenge_wise_team_ponits.challenge_id')
                    ->join('teams', 'teams.id', '=', 'challenge_wise_team_ponits.team_id')
                    ->where('challenge_wise_team_ponits.challenge_id', $queryStr['challenge'])
                    ->where(DB::raw("round(challenge_wise_team_ponits.points, 1)"), '>', 0)
                    ->select(
                        'challenge_wise_team_ponits.rank',
                        'teams.name AS team_name',
                        'companies.name AS company_name',
                        DB::raw("round(challenge_wise_team_ponits.points, 1) as points")
                    );
                
                if (!empty($startDate) && !empty($endDate)) {
                    $query->whereBetween('challenges.start_date', [$startDate, $endDate]);
                } elseif (!empty($startDate) && empty($endDate)) {
                    $query->where('challenges.start_date', '>=', $startDate);
                } elseif (empty($startDate) && !empty($endDate)) {
                    $query->where('challenges.start_date', '<=', $endDate);
                }

                if (!empty($queryStr['company'])) {
                    $query->where('challenge_wise_team_ponits.company_id', $queryStr['company']);
                }
                $query->orderBy('challenge_wise_team_ponits.rank', 'ASC');
            }
            
            /**/
            
            $userNPSDataRecords = $query->get()->chunk(100);
            if ($query->get()->count() == 0) {
                $messageData = [
                    'data'   => trans('challenges.messages.export_error'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.reports.intercompanyreport')->with('message', $messageData);
            }
            $dateTimeString = Carbon::now()->toDateTimeString();
            if ($this->payload['tab'] == "ic-company") {
                $sheetTitle = [
                    'Rank',
                    'Company Name',
                    'Total Teams',
                    'Total Users',
                    'Points'
                ];
    
                $fileName = "Intercompany_Report".$dateTimeString.'.xlsx';
            } else {
                $sheetTitle = [
                    'Rank',
                    'Team Name',
                    'Company Name',
                    'Points'
                ];
    
                $fileName = "Intercompany_Team_Report".$dateTimeString.'.xlsx';
            }
            
            $spreadsheet = new Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();
            $sheet->fromArray($sheetTitle, null, 'A1');
            
            $index = 2;
            foreach ($userNPSDataRecords as $value) {
                $records = (count($value) > 0) ? json_decode(json_encode($value), true) : [];
                $sheet->fromArray($records, null, 'A'.$index, true);
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
            event(new InterCopmanyReportExportEvent($this->user, $url, $this->payload, $fileName));
            return true;
            
        } catch (\Exception $exception) {
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}
