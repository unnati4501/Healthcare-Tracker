<?php

namespace App\Jobs;

use App\Events\BookingReportCompanyWiseExportEvent;
use App\Models\Company;
use App\Models\EventBookingLogs;
use Carbon\Carbon;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class exportBookingReportCompanyWiseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $company;
    public $payload;
    public $user;
    public $columnName;

    /**
     * Create a new job instance.
     * @return void
     */
    public function __construct($payload, $user, $company)
    {
        $this->queue    = 'mail';
        $this->company  = $company;
        $this->payload  = $payload;
        $this->user     = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $rquestedCompanyId = $this->company->id;
            $appTimezone       = config('app.timezone');
            $timezone          = (!empty($this->user['timezone']) ? $this->user['timezone'] : $appTimezone);
            $role              = getUserRole($this->user);
            $userCompany       = $this->user->company()->first();
            $queryStr          = json_decode($this->payload['queryString'], true);

            $events = EventBookingLogs::
                select(
                    'events.name AS event_name',
                    \DB::raw('meta->>"$.presenter" AS presenter'),
                    \DB::raw("IFNULL(companies.name, 'Zevo') AS created_by"),
                    \DB::raw("CASE WHEN events.location_type = 1 THEN 'Online' ELSE 'Onsite' END"),
                    DB::raw("IFNULL((SELECT IFNULL(COUNT(event_registered_users_logs.id), '0') FROM event_registered_users_logs
                    WHERE event_registered_users_logs.event_booking_log_id = event_booking_logs.id
                    GROUP BY event_registered_users_logs.event_id), '0') as totalParticipants"),
                    \DB::raw("CONCAT('€',' ',events.fees)"),
                    \DB::raw("CASE WHEN event_booking_logs.is_complementary = 0 THEN 'No' ELSE 'Yes' END"),
                    \DB::raw("CASE WHEN event_booking_logs.status = '3' THEN 'Cancelled' WHEN event_booking_logs.status = '4' THEN 'Booked' WHEN event_booking_logs.status = '5' THEN 'Completed' WHEN event_booking_logs.status = '6' THEN 'Pending' WHEN event_booking_logs.status = '7' THEN 'Elapsed' ELSE 'Rejected' END"),
                    \DB::raw('IF(event_booking_logs.status = "3", meta->>"$.cancelled_by_name", null) AS cancelled_by_name'),
                )->selectRaw("IF(event_booking_logs.status = '3', CONVERT_TZ(meta->>'$.cancelled_on', ?, ?), null) AS cancelled_on",[
                    $appTimezone,$timezone
                ])
                ->selectRaw("CONCAT(DATE_FORMAT(CONVERT_TZ(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time), ?, ?), '%b %d, %Y, %H:%i'),'-',DATE_FORMAT(CONVERT_TZ(event_booking_logs.end_time, ?, ?), '%H:%i'))",[
                    $appTimezone,$timezone,$appTimezone,$timezone 
                ])
                ->join('events', 'events.id', '=', 'event_booking_logs.event_id')
                ->join('event_companies', function ($join) {
                    $join
                        ->on('event_companies.event_id', '=', 'events.id')
                        ->whereColumn('event_companies.company_id', 'event_booking_logs.company_id');
                })
                ->leftJoin('companies', 'companies.id', '=', 'events.company_id')
                ->where('event_booking_logs.company_id', $rquestedCompanyId)
                ->whereHas('event', function ($query) use ($role, $userCompany) {
                    $query
                        ->select('events.id')
                        ->where('events.status', 2);
                    if ($role->group == 'zevo') {
                        $query->whereNull('events.company_id');
                    } elseif ($role->group == 'company') {
                        $query->where('event_companies.company_id', $userCompany->id);
                    } elseif ($role->group == 'reseller') {
                        if ($userCompany->is_reseller) {
                            $assigneeComapnies = Company::select('id')
                                ->where('parent_id', $userCompany->id)
                                ->orWhere('id', $userCompany->id)
                                ->get()->pluck('id')->toArray();
                            $query
                                ->whereIn('event_companies.company_id', $assigneeComapnies)
                                ->where(function ($where) use ($userCompany) {
                                    $where
                                        ->whereNull('events.company_id')
                                        ->orWhere('events.company_id', $userCompany->id);
                                });
                        } elseif (!is_null($userCompany->parent_id)) {
                            $query->where('event_companies.company_id', $userCompany->id);
                        }
                    }
                });
            if (!empty($queryStr['presenter'])) {
                $events->where('event_booking_logs.presenter_user_id', $queryStr['presenter']);
            }
            if (!empty($queryStr['status'])) {
                $events->where('event_booking_logs.status', $queryStr['status']);
            }
            if (!empty($queryStr['complementary'])) {
                $events->where('event_booking_logs.is_complementary', $queryStr['complementary']);
            }
            $exportStartDate = !empty($this->payload['start_date']) ? $this->payload['start_date'] : '';
            $startDateSearch = !empty($queryStr['fromdate']) ? $queryStr['fromdate'] : $exportStartDate;
            $exportEndDate   = !empty($this->payload['end_date']) ? $this->payload['end_date'] : '';
            $endDateSearch   = !empty($queryStr['todate']) ? $queryStr['todate'] : $exportEndDate;
            if (!empty($startDateSearch) && !empty($endDateSearch)) {
                $fromdate = Carbon::parse($startDateSearch, $timezone)->setTime(0, 0, 0, 0)->setTimezone($appTimezone)->toDateTimeString();
                $todate   = Carbon::parse($endDateSearch, $timezone)->setTime(23, 59, 59, 0)->setTimezone($appTimezone)->toDateTimeString();
                $events
                    ->where(function ($where) use ($fromdate, $todate) {
                        $where
                            ->whereRaw("TIMESTAMP(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time)) BETWEEN ? AND ?",[
                                $fromdate, $todate
                            ])
                            ->orWhereRaw("TIMESTAMP(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.end_time)) BETWEEN ? AND ?",[
                                $fromdate, $todate
                            ]);
                    });
            }
            $events->groupBy('event_booking_logs.id');
            $events->orderByDesc('event_booking_logs.updated_at');
            $userNPSDataRecords = $events->get()->chunk(100);
            if ($events->get()->count() == 0) {
                $messageData = [
                    'data' => trans('challenges.messages.export_error'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.reports.booking-report')->with('message', $messageData);
            }
            $dateTimeString = Carbon::now()->toDateTimeString();
            $sheetTitle = [
                'Event Name',
                'Presenter',
                'Date/Time',
                'Created By',
                'Location Type',
                'Participants',
                'Billable',
                'Complementary',
                'Status',
                'Cancelled By',
                'Cancelled Date',
            ];
            $fileName    = "Event_Details_View_" . $this->user['full_name'] . "_" . $dateTimeString . '.xlsx';
            $spreadsheet = new Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();
            $sheet->fromArray($sheetTitle, null, 'A1');
            $index = 2;
            foreach ($userNPSDataRecords as $value) {
                $records = (count($value) > 0) ? json_decode(json_encode($value), true) : [];
                $sheet->fromArray($records, null, 'A' . $index);
                $index = $index + count($value);
            }

            $writer    = new Xlsx($spreadsheet);
            $temp_file = tempnam(sys_get_temp_dir(), $fileName);
            $source    = fopen($temp_file, 'rb');
            $writer->save($temp_file);
            $root       = config("filesystems.disks.spaces.root");
            $foldername = config('zevolifesettings.excelfolderpath');
            $uploaded   = uploadFileToSpaces($source, "{$root}/{$foldername}/{$fileName}", "public");
            if (null != $uploaded && is_string($uploaded->get('ObjectURL'))) {
                $url      = $uploaded->get('ObjectURL');
                $uploaded = true;
            }
            event(new BookingReportCompanyWiseExportEvent($this->user, $url, $this->payload, $fileName));
            return true;
        } catch (\Exception $exception) {
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}
