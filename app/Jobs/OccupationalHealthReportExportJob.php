<?php

namespace App\Jobs;

use App\Events\OccupationalHealthReportExportEvent;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Log;

class OccupationalHealthReportExportJob implements ShouldQueue
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
                'ID',
                'Client Name',
                'Client Email',
                'Company Name',
                'Date Added',
                'Confirmation Client',
                'Confirmation Date',
                'Note',
                'Attended',
                'Wellbeing Specialist Name',
                'Referred By'
            ];
            $fileName = 'occupational_health_' . $dateTimeString . '.xlsx';
            $spreadsheet = new Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();
            $sheet->fromArray($sheetTitle, null, 'A1');
            $exportedRecords = json_decode(json_encode($this->records), true);
            $exportdata = [];
            foreach ($exportedRecords as $key => $value) {
                $data['id']                     = $key+1;
                $data['user_name']              = $value['user_name'];
                $data['user_email']             = $value['user_email'];
                $data['company']                = $value['company_name'];
                $data['log_date']               = $value['log_date'];
                $data['confirmation_client']    = $value['is_confirmed'];
                $data['confirmation_date']      = $value['confirmation_date'];
                $data['note']                   = $value['note'];
                $data['attended']               = $value['is_attended'];
                $data['ws_name']                = $value['ws_name'];
                $data['referred_by']            = $value['referred_by'];
                $exportdata[]                   = $data;
            }
            $sheet->fromArray($exportdata, null, 'A2');

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
                event(new OccupationalHealthReportExportEvent($this->email, $this->user, $url, $fileName));
                return true;
            }
        } catch (\Exception $exception) {
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}
