<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\Department;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Events\DepartmentExportEvent;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use DB;

class ExportDepartmentJob implements ShouldQueue
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
            
            if (!empty($this->payload['start_date'])) {
                $startDate = $this->payload['start_date'] . '00:00';
                $startDate = Carbon::parse($startDate, $timezone)->setTimeZone(config('app.timezone'))->toDateTimeString();
            }
            if (!empty($this->payload['end_date'])) {
                $endDate   = $this->payload['end_date'] . '23:59:59';
                $endDate   = Carbon::parse($endDate, $timezone)->setTimeZone(config('app.timezone'))->toDateTimeString();
            }

            $role     = getUserRole($this->user);
            $company  = $this->user->company()->first();
            $queryStr = json_decode($this->payload['queryString'], true);
            
            $payloadCompany = !empty($this->payload['company']) ? $this->payload['company'] : null;
            $searchCompany = !empty($queryStr['company']) ? $queryStr['company'] : $payloadCompany;
            
            $query = Department::
                select(
                    'companies.name AS company_name',
                    'departments.name',
                    DB::raw("IFNULL((SELECT IFNULL(COUNT(teams.id), '0') FROM teams
                    WHERE teams.department_id = departments.id
                    GROUP BY teams.department_id), '0') as totalTeams"),
                    DB::raw("IFNULL((SELECT IFNULL(COUNT(user_team.id), '0') FROM user_team
                    WHERE user_team.department_id = departments.id
                    GROUP BY user_team.department_id), '0') as totalMembers"),
                )
                ->join('companies', function ($join) {
                    $join->on('companies.id', '=', 'departments.company_id');
                });

            if ($role->group == 'reseller') {
                $query
                    ->where(function ($where) use ($company) {
                        $where
                            ->where('departments.company_id', $company->id)
                            ->orWhere('companies.parent_id', $company->id);
                    });
            } elseif ($role->group == 'company') {
                $query->where('departments.company_id', $company->id);
            }

            if (!empty($queryStr['department'])) {
                $query->where('departments.name', 'like', '%' . $queryStr['department'] . '%');
            }

            if (!empty($searchCompany)) {
                $query->where('departments.company_id', $searchCompany);
            }

            if (!empty($startDate) && !empty($endDate)) {
                $query->whereBetween('departments.created_at', [$startDate, $endDate]);
            } elseif (!empty($startDate) && empty($endDate)) {
                $query->where('departments.created_at', '>=', $startDate);
            } elseif (empty($startDate) && !empty($endDate)) {
                $query->where('departments.created_at', '<=', $endDate);
            }

            $query->orderByDesc('departments.updated_at');

            $userNPSDataRecords = $query->get()->chunk(100);
            $dateTimeString = Carbon::now()->toDateTimeString();

            $sheetTitle = [
                'Company',
                'Department',
                'Teams',
                'Members'
            ];

            $fileName = "Department_".$dateTimeString.'.xlsx';
            $spreadsheet = new Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();
            $sheet->fromArray($sheetTitle, null, 'A1');
            
            $index = 2;
            foreach ($userNPSDataRecords as $value) {
                $records = (count($value) > 0) ? json_decode(json_encode($value), true) : [];
                $sheet->fromArray($records, null, 'A'.$index, true);
                $index = $index + count($value);
            }
            
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

            event(new DepartmentExportEvent($this->user, $url, $this->payload, $fileName));
            return true;
            
        } catch (\Exception $exception) {
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}
