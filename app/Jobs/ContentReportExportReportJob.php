<?php

namespace App\Jobs;

use App\Events\ContentReportExportEvent;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Log;

class ContentReportExportReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $records;
    public $email;
    public $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($records, $email, $user)
    {
        $this->queue   = 'mail';
        $this->records = $records;
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

            $sheetTitle = [
                'Title',
                'Category',
                'Types',
                'Likes',
                'View Count',
            ];
            $fileName = 'contentreport_' . $dateTimeString . '.xlsx';

            $spreadsheet = new Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();
            $sheet->fromArray($sheetTitle, null, 'A1');

            $fetchedRecords = $this->records->toArray();
            $fetchedRecords = json_decode(json_encode($fetchedRecords), true);

            $sheet->fromArray($fetchedRecords, null, 'A2');

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

            if ($uploaded) {
                event(new ContentReportExportEvent($this->email, $this->user, $url, $fileName));
                return true;
            }
        } catch (\Exception $exception) {
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}
