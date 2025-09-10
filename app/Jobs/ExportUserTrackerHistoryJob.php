<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Events\UserTrackerHistoryExportEvent;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use DB;

class ExportUserTrackerHistoryJob implements ShouldQueue
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
            $timezone    = (!empty($this->user['timezone']) ? $this->user['timezone'] : $appTimezone);
            $queryStr    = json_decode($this->payload['queryString'], true);
           
            $exportStartDate = !empty($this->payload['start_date']) ? $this->payload['start_date'] : '';
            $startDateSearch = !empty($queryStr['fromdate']) ? $queryStr['fromdate'] : $exportStartDate;
            if (!empty($startDateSearch)) {
                $startDate = Carbon::parse($startDateSearch, $timezone)->setTime(0, 0, 0, 0)->setTimezone($appTimezone)->toDateTimeString();
            }

            $exportEndDate = !empty($this->payload['end_date']) ? $this->payload['end_date'] : '';
            $endDateSearch = !empty($queryStr['todate']) ? $queryStr['todate'] : $exportEndDate;
            if (!empty($endDateSearch)) {
                $endDate   = Carbon::parse($endDateSearch, $timezone)->setTime(23, 59, 59, 0)->setTimezone($appTimezone)->toDateTimeString();
            }

            $query =  \DB::table('user_device_history')->select('tracker', \DB::raw("DATE_FORMAT(log_date, '%b %d, %Y, %H:%i')"))
                ->where('user_id', $this->user['id']);

            if (!empty($queryStr['trackerName'])) {
                $query->where("user_device_history.tracker", 'like', '%' . $queryStr['trackerName'] . '%');
            }
            
            if (!empty($startDate) && !empty($endDate)) {
                $query->whereBetween('log_date', [$startDate, $endDate]);
            } elseif (!empty($startDate) && empty($endDate)) {
                $query->where('log_date', '>=', $startDate);
            } elseif (empty($startDate) && !empty($endDate)) {
                $query->where('log_date', '<=', $endDate);
            }

            $query->orderByDesc('log_date');

            $userNPSDataRecords = $query->get()->chunk(100);
            
            if ($query->get()->count() == 0) {
                $messageData = [
                    'data'   => trans('challenges.messages.export_error'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.users.tracker-history', $this->user)->with('message', $messageData);
            }
            $dateTimeString = Carbon::now()->toDateTimeString();

            $sheetTitle = [
                'Tracker Name',
                'Tracker Date/Time'
            ];

            $fileName = "TrackerHistory_".$this->user['full_name']."_".$dateTimeString.'.xlsx';

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
            
            event(new UserTrackerHistoryExportEvent($this->user, $url, $this->payload, $fileName));
            return true;
            
        } catch (\Exception $exception) {
            dd($exception);
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}
