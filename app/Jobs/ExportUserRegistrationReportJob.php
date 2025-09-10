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
use App\Events\UserRegistrationReportExportEvent;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use DB;

class ExportUserRegistrationReportJob implements ShouldQueue
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
            $company     = $this->user->company()->first();
            $role        = getUserRole($this->user);
            $appTimezone = config('app.timezone');
            $timezone    = (!empty($this->user->timezone) ? $this->user->timezone : $appTimezone);
            $queryStr    = json_decode($this->payload['queryString'], true);
            
            $exportStartDate = !empty($this->payload['start_date']) ? $this->payload['start_date'] : '';
            $startDateSearch = !empty($queryStr['fromDate']) ? $queryStr['fromDate'] : $exportStartDate;
            if (!empty($startDateSearch)) {
                $startDate = Carbon::parse($startDateSearch, $timezone)->setTime(0, 0, 0, 0)->setTimezone($appTimezone)->toDateTimeString();
            }

            $exportEndDate = !empty($this->payload['end_date']) ? $this->payload['end_date'] : '';
            $endDateSearch = !empty($queryStr['toDate']) ? $queryStr['toDate'] : $exportEndDate;
            if (!empty($endDateSearch)) {
                $endDate   = Carbon::parse($endDateSearch, $timezone)->setTime(23, 59, 59, 0)->setTimezone($appTimezone)->toDateTimeString();
            }

            $query       = \DB::table('users')
            ->leftJoin('user_team', function ($join) {
                $join->on('user_team.user_id', '=', 'users.id');
            })
            ->leftJoin('companies', function ($join) {
                $join
                    ->on('users.id', '=', 'user_team.user_id')
                    ->on('user_team.company_id', '=', 'companies.id');
            })
            ->leftJoin("team_location", function ($join) {
                $join->on("team_location.team_id", "=", "user_team.team_id");
            })
            ->leftJoin("company_locations", function ($join) {
                $join->on("company_locations.id", "=", "team_location.company_location_id");
            })
            ->leftJoin("departments", function ($join) {
                $join
                    ->on('user_team.user_id', '=', 'users.id')
                    ->on('user_team.department_id', '=', 'departments.id');
            })
            ->leftJoin('teams', function ($join) {
                $join
                    ->on('users.id', '=', 'user_team.user_id')
                    ->on('user_team.team_id', '=', 'teams.id');
            })
            ->leftJoin('role_user', function ($join) {
                $join->on('role_user.user_id', '=', 'users.id');
            })
            ->leftJoin('roles', function ($join) {
                $join->on('roles.id', '=', 'role_user.role_id');
            })
            ->select(
                DB::raw("CONCAT(users.first_name, ' ', users.last_name) as fullName"),
                'users.email',
                'roles.name AS roleName',
                'companies.name as companyName',
                'departments.name AS departmentName',
                'company_locations.name AS locationName',
                'teams.name as teamName',
            )
            ->selectRaw("DATE_FORMAT(CONVERT_TZ(users.created_at, ?, ?), '%b %d, %Y, %H:%i')",[
                $appTimezone,$timezone
            ])
            ->where('users.email', '!=', 'superadmin@grr.la')
            ->where('users.id', '!=', $this->user->id)
            ->where(function ($where) use ($role, $company) {
                if ($role->group == 'company') {
                    $where->where('user_team.company_id', '=', $company->id);
                } elseif ($role->group == 'reseller') {
                    $where
                        ->where('user_team.company_id', '=', $company->id)
                        ->orWhere('companies.parent_id', '=', $company->id);
                }
            });
            if (!empty($queryStr['company'])) {
                $query->where('user_team.company_id', $queryStr['company']);
            }

            if (!empty($queryStr['rolename'])) {
                $query->where('roles.id', $queryStr['rolename']);
            }

            if (!empty($queryStr['rolegroup'])) {
                $query->where('roles.group', $queryStr['rolegroup']);
            }

            if (!empty($startDate) && !empty($endDate)) {
                $query->whereBetween('users.created_at', [$startDate, $endDate]);
            } elseif (!empty($startDate) && empty($endDate)) {
                $query->where('users.created_at', '>=', $startDate);
            } elseif (empty($startDate) && !empty($endDate)) {
                $query->where('users.created_at', '<=', $endDate);
            }
            
            $query->groupBy('users.id');
            
            $query->orderByDesc('users.id');

            $userNPSDataRecords = $query->get()->chunk(100);
            if ($query->get()->count() == 0) {
                $messageData = [
                    'data'   => trans('challenges.messages.export_error'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.reports.user-registration')->with('message', $messageData);
            }
            $dateTimeString = Carbon::now()->toDateTimeString();

            $sheetTitle = [
                'Full Name',
                'Email',
                'Role',
                'Company',
                'Department',
                'Location',
                'Team',
                'Registration'
            ];

            $fileName = "User_Registration_".$dateTimeString.'.xlsx';

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
            
            event(new UserRegistrationReportExportEvent($this->user, $url, $this->payload, $fileName));
            return true;
            
        } catch (\Exception $exception) {
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}
