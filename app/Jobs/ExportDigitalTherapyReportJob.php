<?php

namespace App\Jobs;

use App\Events\DigitalTherapyExportEvent;
use App\Models\CronofySchedule;
use App\Models\User;
use App\Models\WsUser;
use App\Models\ScheduleUsers;
use App\Models\CompanyDigitalTherapy;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportDigitalTherapyReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $records;
    public $tab;
    public $email;
    public $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($records, $tab, $email, $user)
    {
        $this->queue   = 'mail';
        $this->records = $records;
        $this->tab     = $tab;
        $this->email   = $email;
        $this->user    = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $dateTimeString = Carbon::now()->toDateTimeString();
            $userTimezone       = $this->user->timezone ?? config('app.timezone');
            if ($this->tab != 'single') {
                $sheetTitle = [
                    trans('digitaltheraphyreport.table.company_name'),
                    trans('digitaltheraphyreport.table.service_name'),
                    trans('digitaltheraphyreport.table.issue'),
                    trans('digitaltheraphyreport.table.location'),
                    trans('digitaltheraphyreport.table.department'),
                    trans('digitaltheraphyreport.table.booking_date'),
                    trans('digitaltheraphyreport.table.session_date'),
                    trans('digitaltheraphyreport.table.duration'),
                    trans('digitaltheraphyreport.table.mode_of_service'),
                    trans('digitaltheraphyreport.table.wellbeing_sepecialist_name'),
                    trans('digitaltheraphyreport.table.ws_timezone'),
                    trans('digitaltheraphyreport.table.ws_shift'),
                    trans('digitaltheraphyreport.table.number_of_participants'),
                    trans('digitaltheraphyreport.table.status'),
                ];
            } else {
                $sheetTitle = [
                    trans('digitaltheraphyreport.table.client_name'),
                    trans('digitaltheraphyreport.table.client_email'),
                    trans('digitaltheraphyreport.table.company_name'),
                    trans('digitaltheraphyreport.table.service_name'),
                    trans('digitaltheraphyreport.table.issue'),
                    trans('digitaltheraphyreport.table.location'),
                    trans('digitaltheraphyreport.table.department'),
                    trans('digitaltheraphyreport.table.booking_date'),
                    trans('digitaltheraphyreport.table.session_date'),
                    trans('digitaltheraphyreport.table.duration'),
                    trans('digitaltheraphyreport.table.mode_of_service'),
                    trans('digitaltheraphyreport.table.wellbeing_sepecialist_name'),
                    trans('digitaltheraphyreport.table.ws_timezone'),
                    trans('digitaltheraphyreport.table.ws_shift'),
                    trans('digitaltheraphyreport.table.status'),
                    trans('digitaltheraphyreport.table.created_by'),
                ];
            }
            if ($this->tab == 'single') {
                $fileName = "Digital_Therapy_1:1_Report_" . $dateTimeString . '.xlsx';
            } elseif ($this->tab == 'group') {
                $fileName = "Digital_Therapy_Group_Report_" . $dateTimeString . '.xlsx';
            } else {
                $fileName = "Digital_Therapy_Report_" . $dateTimeString . '.xlsx';
            }
            $spreadsheet = new Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();
            $sheet->fromArray($sheetTitle, null, 'A1');
            
            $fetchedRecords = json_decode(json_encode($this->records), true);
            $dtData = [];
            
            foreach ($fetchedRecords as $value) {
                $scheduleUser = ScheduleUsers::where('session_id', $value['id'])->get();
                $mode                  = "";
                $companyDigitalTherapy = CompanyDigitalTherapy::where('company_id', $value['company_id'])->first();
                if ($companyDigitalTherapy->dt_is_online) {
                    $mode = 'Online';
                } elseif ($companyDigitalTherapy->dt_is_onsite) {
                    $mode = 'Onsite';
                }
                $shift  = config('zevolifesettings.shift');
                $wsUser = WsUser::where('user_id', $value['ws_id'])->first();

                if($this->tab == 'single'){
                    if (!$value['is_group']) {
                        if(!empty($value['user'])) {
                            $userFullName = $value['user']['first_name'] . ' ' . $value['user']['last_name'];
                            $userEmail    = $value['user']['email'];
                        } else {
                            $userFullName = $value['session_user_name'];
                            $userEmail    = $value['session_user_email'];
                        }
                    } elseif ($value['is_group'] && $value['users_count'] == 1) {
                        $userFullName = $value['session_user_name'];
                        $userEmail    = $value['session_user_email'];
                    }
                    $company['session_user_name']  = $userFullName;
                    $company['session_user_email'] = $userEmail;
                }
                $company['company_name'] = $value['company_name'];
                $company['service_name'] = $value['service_name'];
                $company['issue']        = $value['issue'];
                if($value['is_group'] == 1 && count($scheduleUser) > 1){
                    $company['location_name']  = $value['location_name'];
                } else {
                    $userId = $value['user_id'];
                    if (!empty($scheduleUser) && count($scheduleUser) > 0) {
                        $userId = $scheduleUser[0]->user_id;
                    }
                    $location = User::select('company_locations.name as loc', 'departments.name as dept')->leftJoin('user_team', function ($join) {
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
                    })->leftJoin("departments", function ($join) {
                        $join
                            ->on('user_team.user_id', '=', 'users.id')
                            ->on('user_team.department_id', '=', 'departments.id');
                    })
                    ->where('users.id', $userId)->first();
                    $company['location_name']   = $location->loc ?? $value['location_name'];
                }

                if($value['is_group'] == 1 && count($scheduleUser) > 1){
                    $company['department_name']  = $value['department_name'];
                } else {
                    $userId = $value['user_id'];
                    if (!empty($scheduleUser) && count($scheduleUser) > 0) {
                        $userId = $scheduleUser[0]->user_id;
                    }
                    $location = User::select('company_locations.name as loc', 'departments.name as dept')->leftJoin('user_team', function ($join) {
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
                    })->leftJoin("departments", function ($join) {
                        $join
                            ->on('user_team.user_id', '=', 'users.id')
                            ->on('user_team.department_id', '=', 'departments.id');
                    })
                    ->where('users.id', $userId)->first();
                    $company['department_name'] = $location->dept;
                }

                $company['booking_date']                = Carbon::parse($value['created_at'])->setTimezone($userTimezone)->format('M d, Y, h:i A');
                $company['session_date']                = Carbon::parse($value['start_time'])->setTimezone($userTimezone)->format('M d, Y, h:i A');
                $company['duration']                    = $value['duration'];
                $company['mode_of_service']             = $mode;
                $company['wellbeing_specialist_name']   = $value['wellbeing_specialist_name'];
                $company['timezone']                    = $value['ws_timezone'];
                $company['shift']                       = $shift[$wsUser->shift];
                
                if($this->tab != 'single'){
                    $scheduleUser = ScheduleUsers::join('users', 'users.id', '=', 'session_group_users.user_id')->where('session_id', $value['id'])->select(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS name"), 'users.email')->whereNull('users.deleted_at')->distinct()->get()->toArray();
                    $totalUsers   = sizeof($scheduleUser);
                    $company['number_of_participants'] = $totalUsers;
                }

                $status = $value['status'];
                if (strtolower($value['status']) == 'booked') {
                    if (Carbon::parse($value['start_time']) > Carbon::now()) {
                        $status = 'Upcoming';
                    }
                    if ((Carbon::parse($value['end_time']) < Carbon::now()) && $value['no_show'] == 'No') {
                        $status = 'Completed';
                    }
                    if ((Carbon::parse($value['end_time']) < Carbon::now()) && $value['no_show'] == 'Yes') {
                        $status = 'No Show';
                    }
                    $startDate = Carbon::parse($value['start_time']);
                    $endDate   = Carbon::parse($value['end_time']);
                    if (Carbon::now()->between($startDate, $endDate) && $value['no_show'] == 'No') {
                        $status = 'Ongoing';
                    }
                    if (Carbon::now()->between($startDate, $endDate) && $value['no_show'] == 'Yes') {
                        $status = 'No Show';
                    }
                }
                if ($value['status'] == 'canceled') {
                    $status = 'Cancelled';
                }
                if ($value['status'] == 'rescheduled') {
                    $status = 'Rescheduled';
                }
                if ($value['status'] == 'completed') {
                    $status = 'Completed';
                }
                if ($value['status'] == 'short_canceled') {
                    $status = 'Short Cancel';
                }
                
                $company['status']                      = $status;
                if($this->tab == 'single'){
                    $userRecord = User::find($value['created_by']);
                    $role       = getUserRole($userRecord);
                    $company['created_by'] = ($role->slug == 'company_admin') ? 'User' : $role->name;
                }
                $dtData[]                            = $company;
            }
            $sheet->fromArray($dtData, null, 'A2');

            $writer    = new Xlsx($spreadsheet);
            $temp_file = tempnam(sys_get_temp_dir(), $fileName);
            $source    = fopen($temp_file, 'rb');
            $writer->save($temp_file);

            $root       = config("filesystems.disks.spaces.root");
            $foldername = config('zevolifesettings.excelfolderpath');

            $uploaded = uploadFileToSpaces($source, "{$root}/{$foldername}/{$fileName}", "public");
            if (null != $uploaded && is_string($uploaded->get('ObjectURL'))) {
                $url      = $uploaded->get('ObjectURL');
                $uploaded = true;
            }
            $payload['email'] = $this->email;
            $payload['tab']   = $this->tab;
           
            event(new DigitalTherapyExportEvent($payload, $this->user, $url, $fileName));
            return true;
        } catch (\Exception $exception) {
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}
