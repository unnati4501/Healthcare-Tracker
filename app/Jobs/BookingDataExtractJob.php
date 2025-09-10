<?php

namespace App\Jobs;

use App\Models\EventRegisteredUserLog;
use App\Models\SubCategory;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

/**
 * Class BookingDataExtractJob
 */
class BookingDataExtractJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var $companiesId
     */
    public $companiesId;

    /**
     * @var $dataExtractFileName
     */
    public $dataExtractFileName;

    /**
     * @var $bookingExtract
     */
    protected $bookingExtract;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($companiesId, $dataExtractFileName, $bookingExtract)
    {
        $this->queue               = 'default';
        $this->companiesId         = $companiesId;
        $this->dataExtractFileName = $dataExtractFileName;
        $this->bookingExtract      = $bookingExtract;
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
            $fileName    = $this->dataExtractFileName;
            $spreadsheet = new Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();
            $index       = 2;

            $sheet->fromArray($this->bookingExtract, null, 'A1');

            $subcategory = SubCategory::select('id', 'name')
                ->where(['category_id' => 6, 'status' => 1])
                ->get()
                ->pluck('name', 'id')
                ->toArray();

            $eventStatus = [
                3 => 'Cancelled',
                4 => 'Booked',
                5 => 'Completed',
                6 => 'Pending',
                7 => 'Elapsed',
                8 => 'Rejected',
            ];

            $eventUsers = EventRegisteredUserLog::leftJoin('event_booking_logs', 'event_booking_logs.id', '=', 'event_registered_users_logs.event_booking_log_id')
                ->leftJoin('events', 'events.id', '=', 'event_registered_users_logs.event_id')
                ->leftJoin('companies', 'companies.id', '=', 'event_booking_logs.company_id')
                ->leftJoin('users', 'users.id', '=', 'event_registered_users_logs.user_id')
                ->select(
                    'companies.code',
                    'event_booking_logs.event_id',
                    'event_booking_logs.id',
                    'events.subcategory_id',
                    'event_booking_logs.company_id',
                    'event_booking_logs.status',
                    'events.name',
                    'users.timezone',
                    'event_booking_logs.booking_date',
                    'event_booking_logs.start_time',
                    'event_booking_logs.created_at',
                )
                ->whereIn('event_booking_logs.company_id', $this->companiesId)
                ->groupBy('event_booking_logs.id')
                ->distinct()
                ->get()
                ->chunk(500);

            $index = 2;
            $eventUsers->each(function ($events) use ($sheet, $index, $appTimeZone, $subcategory, $eventStatus) {
                foreach ($events as $value) {
                    $result           = GetUserCountBasedOnEventRegistered($value['id'], $value['event_id']);
                    $bookingStartDate = Carbon::parse($value['booking_date'], $appTimeZone)->setTimezone($value['timezone'])->toDateString();
                    $bookingDate      = Carbon::parse($value['created_at'], $appTimeZone)->setTimezone($value['timezone'])->toDateString();
                    $tempArray        = [
                        $value['name'],
                        $value['code'],
                        $value['event_id'],
                        $subcategory[$value['subcategory_id']],
                        $eventStatus[$value['status']],
                        $bookingDate,
                        $bookingStartDate,
                        isset($result['avg']) ? round($result['avg']) : '0',
                        isset($result['male']) ? $result['male'] : '0',
                        isset($result['female']) ? $result['female'] : '0',
                        isset($result['none']) ? $result['none'] : '0',
                        isset($result['other']) ? $result['other'] : '0',
                    ];
                    $sheet->fromArray($tempArray, null, 'A' . $index);
                    $index = $index + 1;
                }
            });

            $writer    = new Csv($spreadsheet);
            $temp_file = tempnam(sys_get_temp_dir(), $fileName);
            $source    = fopen($temp_file, 'rb');
            $writer->save($temp_file);

            $foldername = config('data-extract.excelfolderpath');

            $uploaded = uploadFileToSpaces($source, "{$foldername}{$fileName}", "public");
            if (null != $uploaded && is_string($uploaded->get('ObjectURL'))) {
                $url      = $uploaded->get('ObjectURL');
                $uploaded = true;
            }

            return $url;
        } catch (\Exception $exception) {
            \Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
            $cronData['is_exception'] = 1;
            $cronData['log_desc']     = $exception->getMessage();
            cronlog($cronData, 1);
        }

    }
}
