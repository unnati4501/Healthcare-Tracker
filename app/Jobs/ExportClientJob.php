<?php

namespace App\Jobs;

use App\Events\DigitalTherapyClientExportEvent;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Carbon\Carbon;

class ExportClientJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $targetType;
    public $records;
    public $user;
    public $email;

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
            $fileName       = "client_" . $dateTimeString . '.xlsx';

            $sheetTitle = [
                'Client Name',
                'Client Email',
                'Company',
                'Location',
                'Completed',
                'Upcoming',
                'Cancelled',
                'Short Cancel',
                'No Show',
            ];

            $spreadsheet = new Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();
            $sheet->fromArray($sheetTitle, null, 'A1');
            
            $res = json_decode(json_encode($this->records), true);
            
            $sheet->fromArray($res, null, 'A2');

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
            event(new DigitalTherapyClientExportEvent($payload, $this->user, $url, $fileName));
            return true;
        } catch (\Exception $exception) {
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}
