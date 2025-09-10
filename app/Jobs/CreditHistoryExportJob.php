<?php

namespace App\Jobs;

use App\Events\CreditHistoryExportEvent;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Log;

class CreditHistoryExportJob implements ShouldQueue
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
    public function __construct(array $records, $email, $user)
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
                'Date Time',
                'Action',
                'Credit Count',
                'Updated By',
                'Available Credit Balance',
                'Notes'
            ];
            $fileName = 'credit_history_' . $dateTimeString . '.xlsx';

            $spreadsheet = new Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();
            $sheet->fromArray($sheetTitle, null, 'A1');
            $spreadsheet->getActiveSheet()->getStyle("A1:F1")->getFont()->setBold( true );

            $fetchedRecords = $this->records;
            $fetchedRecords = json_decode(json_encode($fetchedRecords), true);

            $sheet->fromArray($fetchedRecords, null, 'A2', true);

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
                event(new CreditHistoryExportEvent($this->email, $this->user, $url, $fileName));
                return true;
            }
        } catch (\Exception $exception) {
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}
