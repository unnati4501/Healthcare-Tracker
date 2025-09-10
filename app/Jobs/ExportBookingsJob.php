<?php

namespace App\Jobs;

use App\Events\ExportBookingsEvent;
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

class ExportBookingsJob implements ShouldQueue
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
            $events = EventBookingLogs::select(
                'events.name AS event_name',
                'companies.name AS company_name',
                \DB::raw('IF(events.is_special = "1", events.special_event_category_title , sub_categories.name) AS subcategory_name'),
                \DB::raw("CASE WHEN event_booking_logs.status = '3' THEN 'Cancelled' WHEN event_booking_logs.status = '4' THEN 'Booked' WHEN event_booking_logs.status = '5' THEN 'Completed' WHEN event_booking_logs.status = '6' THEN 'Pending' WHEN event_booking_logs.status = '7' THEN 'Elapsed' ELSE 'Rejected' END as eventStatus"),
            )
            ->selectRaw(
                "CONCAT(DATE_FORMAT(CONVERT_TZ(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time), ?, ?), '%b %d, %Y, %H:%i'),'-',DATE_FORMAT(CONVERT_TZ(event_booking_logs.end_time, ?, ?), '%H:%i'))"
            ,[
                $appTimezone,$timezone,$appTimezone,$timezone
            ])
            ->join('events', 'events.id', '=', 'event_booking_logs.event_id')
            ->join('companies', 'companies.id', '=', 'event_booking_logs.company_id')
            ->join('sub_categories', 'sub_categories.id', '=', 'events.subcategory_id')
            ->where('events.status', 2)
            ->where(function ($query) use ($role) {
                if ($role->group == 'zevo') {
                    $query->whereNull('events.company_id');
                    if ($role->slug == 'health_coach'){
                         $query->where('event_booking_logs.presenter_user_id', $this->user->id);
                    }
                }
            });
            if (!empty($this->payload['event'])) {
                $events->where('events.name', 'like', "%" . $this->payload['event'] . "%");
            }
            if (!empty($this->payload['event_company'])) {
                $events->where('event_booking_logs.company_id', $this->payload['event_company']);
            }
            if (!empty($this->payload['event_status'])) {
                $events->where('event_booking_logs.status', $this->payload['event_status']);
            }
            if (!empty($this->payload['event_category'])) {
                $events->where('events.subcategory_id', $this->payload['event_category']);
            }
            
            $events->groupBy('event_booking_logs.id');
            $events->orderByDesc('event_booking_logs.updated_at');
            $bookingDataRecords = $events->get()->chunk(100);
            
            if ($events->get()->count() == 0) {
                $messageData = [
                    'data'   => trans('challenges.messages.export_error'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.bookings.index')->with('message', $messageData);
            }
            $dateTimeString = Carbon::now()->toDateTimeString();

            $sheetTitle = [
                'Event Name',
                'Company',
                'Category',
                'Date/Time',
                'Status',
            ];

            $fileName = "Events_Booking_Logs_" . $this->user['full_name'] . "_" . $dateTimeString . '.xlsx';
            
            $spreadsheet = new Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();
            $sheet->fromArray($sheetTitle, null, 'A1');

            $index = 2;
            foreach ($bookingDataRecords as $value) {
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

            $uploaded = uploadFileToSpaces($source, "{$root}/{$foldername}/{$fileName}", "public");
            if (null != $uploaded && is_string($uploaded->get('ObjectURL'))) {
                $url      = $uploaded->get('ObjectURL');
                $uploaded = true;
            }
            
            event(new ExportBookingsEvent($this->user, $url, $this->payload, $fileName));
            return true;

        } catch (\Exception $exception) {
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}
