<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Events\TeamExportEvent;
use App\Models\Team;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use DB;

class ExportTeamJob implements ShouldQueue
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
            
            $role    = getUserRole($this->user);
            $company = $this->user->company()->first();
            $payloadCompany = !empty($this->payload['company']) ? $this->payload['company'] : null;
            $searchCompany = !empty($queryStr['company']) ? $queryStr['company'] : $payloadCompany;

            $query   = Team::
            select(
                "companies.name AS company_name",
                "teams.name as team_name",
                DB::raw("IFNULL((SELECT IFNULL(COUNT(user_team.id), '0') FROM user_team
                WHERE user_team.team_id = teams.id
                GROUP BY user_team.team_id), '0') as totalMembers"),
            )
            ->leftJoin('companies', 'companies.id', '=', 'teams.company_id')
            ->when(($queryStr['teamName'] ?? null), function ($query, $name) {
                $query->where('teams.name', 'like', "%{$name}%");
            })
            ->when(($searchCompany ?? null), function ($query, $searchCompany) {
                $query->where('companies.id', $searchCompany);
            });
            if ($role->group == 'reseller') {
                $query
                    ->where(function ($where) use ($company) {
                        $where
                            ->where('teams.company_id', $company->id)
                            ->orWhere('companies.parent_id', $company->id);
                    });
            } elseif ($role->group != 'zevo') {
                $query->where('company_id', $company->id);
            }

            if (!empty($startDate) && !empty($endDate)) {
                $query->whereBetween('teams.created_at', [$startDate, $endDate]);
            } elseif (!empty($startDate) && empty($endDate)) {
                $query->where('teams.created_at', '>=', $startDate);
            } elseif (empty($startDate) && !empty($endDate)) {
                $query->where('teams.created_at', '<=', $endDate);
            }

            $query->orderByDesc('teams.updated_at');
        
            $userNPSDataRecords = $query->get()->chunk(100);
            $dateTimeString = Carbon::now()->toDateTimeString();

            $sheetTitle = [
                'Company',
                'Team',
                'Team Members'
            ];

            $fileName = "Teams_".$dateTimeString.'.xlsx';
            
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

            event(new TeamExportEvent($this->user, $url, $this->payload, $fileName));
            return true;
            
        } catch (\Exception $exception) {
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}
