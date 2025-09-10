<?php

namespace App\Jobs;

use App\Events\BookingReportDetailExportEvent;
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

class ExportBookingReportDetailJob implements ShouldQueue
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
    public function __construct($payload, $user)
    {
        $this->queue   = 'mail';
        $this->payload = $payload;
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
            $appTimezone = config('app.timezone');
            $timezone    = (!empty($this->user['timezone']) ? $this->user['timezone'] : $appTimezone);

            $role     = getUserRole($this->user);
            $company  = $this->user->company()->first();
            $queryStr = json_decode($this->payload['queryString'], true);

            if ($this->payload['tab'] == 'booking-details') {
                $events = EventBookingLogs::select(
                    'events.name AS event_name',
                    \DB::raw('meta->>"$.presenter" AS presenter'),
                    'sub_categories.name AS subcategory_name',
                    \DB::raw("assignee_co.name AS company_name"),
                    \DB::raw("CASE WHEN events.location_type = 1 THEN 'Online' ELSE 'Onsite' END"),
                    \DB::raw("CONCAT('€',' ',events.fees)"),
                    \DB::raw("CASE WHEN event_booking_logs.is_complementary = 0 THEN 'No' ELSE 'Yes' END"),
                    \DB::raw("CASE WHEN event_booking_logs.status = '3' THEN 'Cancelled' WHEN event_booking_logs.status = '4' THEN 'Booked' WHEN event_booking_logs.status = '5' THEN 'Completed' WHEN event_booking_logs.status = '6' THEN 'Pending' WHEN event_booking_logs.status = '7' THEN 'Elapsed' ELSE 'Rejected' END"),
                    \DB::raw('IF(event_booking_logs.status = "3", meta->>"$.cancelled_by_name", null) AS cancelled_by_name'),
                )->selectRaw(
                    "IF(event_booking_logs.status = '3', CONVERT_TZ(meta->>'$.cancelled_on', ?, ?), null) AS cancelled_on"
                ,[
                    $appTimezone,$timezone
                ])
                ->selectRaw(
                    "CONCAT(DATE_FORMAT(CONVERT_TZ(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time), ?, ?), '%b %d, %Y, %H:%i'),'-',DATE_FORMAT(CONVERT_TZ(event_booking_logs.end_time, ?, ?), '%H:%i'))"
                ,[
                    $appTimezone,$timezone,$appTimezone,$timezone
                ])
                    ->join('events', 'events.id', '=', 'event_booking_logs.event_id')
                    ->join('event_companies', function ($join) {
                        $join
                            ->on('event_companies.event_id', '=', 'events.id')
                            ->whereColumn('event_companies.company_id', 'event_booking_logs.company_id');
                    })
                    ->leftJoin('companies AS creator_co', 'creator_co.id', '=', 'events.company_id')
                    ->join('companies AS assignee_co', 'assignee_co.id', '=', 'event_booking_logs.company_id')
                    ->join('sub_categories', 'sub_categories.id', '=', 'events.subcategory_id')
                    ->whereHas('event', function ($query) use ($role, $company) {
                        $query
                            ->select('events.id')
                            ->where('events.status', 2);
                        if ($role->group == 'zevo') {
                            $query->whereNull('events.company_id');
                        } elseif ($role->group == 'company') {
                            $query->where('event_companies.company_id', $company->id);
                        } elseif ($role->group == 'reseller') {
                            if ($company->is_reseller) {
                                $assigneeComapnies = Company::select('id')
                                    ->where('parent_id', $company->id)
                                    ->orWhere('id', $company->id)
                                    ->get()->pluck('id')->toArray();
                                $query
                                    ->whereIn('event_companies.company_id', $assigneeComapnies)
                                    ->where(function ($where) use ($company) {
                                        $where
                                            ->whereNull('events.company_id')
                                            ->orWhere('events.company_id', $company->id);
                                    });
                            } elseif (!is_null($company->parent_id)) {
                                $query->where('event_companies.company_id', $company->id);
                            }
                        }
                    });
                if (!empty($queryStr['dtName'])) {
                    $eventName    = $queryStr['dtName'];
                    $eventNamePos = strpos($queryStr['dtName'], '%20');
                    if ($eventNamePos) {
                        $eventName = str_replace("%20", " ", $queryStr['dtName']);
                    }
                    $eventNamePos = strpos($queryStr['dtName'], '%09');
                    if ($eventNamePos) {
                        $eventName = str_replace("%09", "", $eventName);
                    }
                    $events->where('events.name', 'like', "%" . $eventName. "%");
                }
                if (!empty($queryStr['dtCompany'])) {
                    $events->where('event_booking_logs.company_id', $queryStr['dtCompany']);
                }
                if (!empty($queryStr['dtPresenter'])) {
                    $events->where('event_booking_logs.presenter_user_id', $queryStr['dtPresenter']);
                }
                if (!empty($queryStr['dtStatus'])) {
                    $events->where('event_booking_logs.status', $queryStr['dtStatus']);
                }
                if (!empty($queryStr['dtCategory'])) {
                    $events->where('events.subcategory_id', $queryStr['dtCategory']);
                }
                if (!empty($queryStr['dtComplementary'])) {
                    $events->where('event_booking_logs.is_complementary', $queryStr['dtComplementary']);
                }
                $exportStartDate = !empty($this->payload['start_date']) ? $this->payload['start_date'] : '';
                $startDateSearch = !empty($queryStr['dtFromdate']) ? str_replace("%2F", "/", $queryStr['dtFromdate']) : $exportStartDate;

                $exportEndDate = !empty($this->payload['end_date']) ? $this->payload['end_date'] : '';
                $endDateSearch = !empty($queryStr['dtTodate']) ? str_replace("%2F", "/", $queryStr['dtTodate']) : $exportEndDate;

                if (!empty($startDateSearch)) {
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
                        'data'   => trans('challenges.messages.export_error'),
                        'status' => 0,
                    ];
                    return \Redirect::route('admin.reports.booking-report')->with('message', $messageData);
                }
                $dateTimeString = Carbon::now()->toDateTimeString();

                $sheetTitle = [
                    'Event Name',
                    'Presenter',
                    'Category',
                    'Date/Time',
                    'Company',
                    'Location Type',
                    'Billable',
                    'Complementary',
                    'Status',
                    'Cancelled By',
                    'Cancelled Date',
                ];

                $fileName = "Event_Details_View_" . $this->user['full_name'] . "_" . $dateTimeString . '.xlsx';
            } else {
                $creatorCoStmt     = $rsaWhereCond     = $searchWhere     = $statusWhere     = "";
                $assigneeComapnies = [];

                if (is_null($company)) {
                    $creatorCoStmt = "AND `events`.`company_id` IS NULL";
                } elseif (!is_null($company) && $company->is_reseller) {
                    $assigneeComapnies = Company::select('id')
                        ->where('parent_id', $company->id)
                        ->orWhere('id', $company->id)
                        ->get()->pluck('id');
                    $assigneeComapniesString = $assigneeComapnies->implode(',');
                    $creatorCoStmt           = "AND (`events`.`company_id` IS NULL OR `events`.`company_id` = {$company->id}) AND `event_booking_logs`.`company_id` IN ({$assigneeComapniesString})";
                    $rsaWhereCond            = "AND `companies`.`id` IN ({$assigneeComapniesString})";
                }

                if (!empty($queryStr['stCompany'])) {
                    $searchWhere .= "AND `ebl`.`company_id` = {$queryStr['stCompany']}";
                }

                if (!empty($queryStr['status'])) {
                    $statusWhere = "HAVING `status` = {$queryStr['stStatus']}";
                }

                $column = '`records`.`max_updated_at`';
                $order  = 'DESC';

                // WHEN (`records`.`total_events` = `records`.`cancelled_events` OR `records`.`total_events` = `records`.`completed_events`) THEN 5
                // ELSE 4
                $companyWiseEventsQuery = "SELECT
            `records`.`company_name`,
            `records`.`total_events`,
            `records`.`booked_events`,
            `records`.`cancelled_events`,
            CONCAT('€',' ',records.billable),
            (CASE
                WHEN (`records`.`booked_events` > 0) THEN 'Booked'
                ELSE 'Completed'
            END) AS `status`
            FROM (SELECT `ebl`.`company_id`,
                    `companies`.`name` AS company_name,
                    MAX(`ebl`.`updated_at`) AS max_updated_at,
                    (SELECT COUNT(`event_booking_logs`.`id`)
                        FROM `event_booking_logs`
                        INNER JOIN `events` ON (`events`.`id` = `event_booking_logs`.`event_id`)
                        WHERE `event_booking_logs`.`company_id` = `ebl`.`company_id` ?
                    ) AS `total_events`,
                    (SELECT IFNULL(COUNT(`event_booking_logs`.`id`), 0)
                        FROM `event_booking_logs`
                        INNER JOIN `events` ON (`events`.`id` = `event_booking_logs`.`event_id`)
                        WHERE `event_booking_logs`.`company_id` = `ebl`.`company_id` AND `event_booking_logs`.`status` = '3' ?
                    ) AS `cancelled_events`,
                    (SELECT IFNULL(COUNT(`event_booking_logs`.`id`), 0)
                        FROM `event_booking_logs`
                        INNER JOIN `events` ON (`events`.`id` = `event_booking_logs`.`event_id`)
                        WHERE `event_booking_logs`.`company_id` = `ebl`.`company_id` AND `event_booking_logs`.`status` = '4' ?
                    ) AS `booked_events`,
                    (SELECT IFNULL(COUNT(`event_booking_logs`.`id`), 0)
                        FROM `event_booking_logs`
                        INNER JOIN `events` ON (`events`.`id` = `event_booking_logs`.`event_id`)
                        WHERE `event_booking_logs`.`company_id` = `ebl`.`company_id` AND `event_booking_logs`.`status` = '5' ?
                    ) AS `completed_events`,
                    ((SELECT IFNULL(SUM(`events`.`fees`), 0)
                        FROM `event_booking_logs`
                        INNER JOIN `events` ON (`events`.`id` = `event_booking_logs`.`event_id`)
                        WHERE `event_booking_logs`.`company_id` = `ebl`.`company_id` AND `event_booking_logs`.`status` IN ('4', '5') ?
                    ) - (SELECT IFNULL(SUM(`events`.`fees`), 0)
                        FROM `event_booking_logs`
                        INNER JOIN `events` ON (`events`.`id` = `event_booking_logs`.`event_id`)
                        WHERE `event_booking_logs`.`company_id` = `ebl`.`company_id` AND `event_booking_logs`.`status` = '3' ?
                    )) AS `billable`
                FROM `event_booking_logs` AS `ebl`
                INNER JOIN `companies` ON `companies`.`id` = `ebl`.`company_id`
                WHERE 1 = 1 ? ?
                GROUP BY `ebl`.`company_id`
                HAVING `total_events` > 0
            ) AS `records`
            ?
            ORDER BY ? ?";
                $userNPSDataRecords = \DB::select($companyWiseEventsQuery,[
                    $creatorCoStmt,$creatorCoStmt,$creatorCoStmt,$creatorCoStmt,$creatorCoStmt,$creatorCoStmt,$rsaWhereCond,$searchWhere,$statusWhere,$column,$order
                ]);
                if (count($userNPSDataRecords) == 0) {
                    $messageData = [
                        'data'   => trans('challenges.messages.export_error'),
                        'status' => 0,
                    ];
                    return \Redirect::route('admin.reports.booking-report')->with('message', $messageData);
                }
                $userNPSDataRecords = collect($userNPSDataRecords);
                $userNPSDataRecords = $userNPSDataRecords->chunk(100);

                $dateTimeString = Carbon::now()->toDateTimeString();

                $sheetTitle = [
                    'Company',
                    'Total events',
                    'Booked',
                    'Cancelled',
                    'Billable',
                    'Status',
                ];

                $fileName = "Event_Summary_View_" . $this->user['full_name'] . "_" . $dateTimeString . '.xlsx';
            }

            $spreadsheet = new Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();
            $sheet->fromArray($sheetTitle, null, 'A1');

            $index = 2;
            foreach ($userNPSDataRecords as $value) {
                $records = (count($value) > 0) ? json_decode(json_encode($value), true) : [];
                $sheet->fromArray($records, null, 'A' . $index, true);
                $index = $index + count($value);
            }

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

            event(new BookingReportDetailExportEvent($this->user, $url, $this->payload, $fileName));
            return true;

        } catch (\Exception $exception) {
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}
