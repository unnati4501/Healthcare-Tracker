<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use App\Events\RealtimeAvailabilityEvent;
use App\Models\Company;
use App\Models\User;
use App\Repositories\CronofyRepository;
use Carbon\CarbonPeriod;

class GenerateRealtimeAvailabilityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;

    /**
     * @var $targetType
     */
    public $targetType;
    /**
     * @var $payload
     */
    public $payload;
    /**
     * @var $user
     */
    public $user;
    /**
     * @var $columnName
     */
    public $columnName;
    /**
     * @var $wbsRecords
     */
    public $wbsRecords;

    /**
     * variable to store the Cronofy Repository Repository object
     * @var CronofyRepository $cronofyRepository
     */
    private $cronofyRepository;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($payload, $user, $wbsRecords)
    {
        $this->queue                = 'mail';
        $this->payload              = $payload;
        $this->user                 = $user;
        $this->wbsRecords           = $wbsRecords;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(CronofyRepository $cronofyRepository)
    {
        try {
            $appGmtTimezone = config('app.gmtTimezone');
            $now = Carbon::now();
            $dateTimeString = $now->toDateTimeString();
            $fileName = "realtimeavailability_" . $dateTimeString . '.xlsx';
            $spreadsheet = new Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();
            $locationId = $this->payload['locationId'];

            $sheet->setCellValue('A1', '');
            $sheet->setCellValue('B1', '');
            $sheet->setCellValue('C1', '');

            $weekArray = [
                'D1:AA1',
                'AB1:AY1',
                'AZ1:BW1',
                'BX1:CU1',
                'CV1:DS1',
                'DT1:EQ1',
                'ER1:FO1',
                'FP1:GM1',
                'GN1:HK1',
                'HL1:II1',
                'IJ1:JG1',
                'JH1:KE1',
                'KF1:LC1',
                'LD1:MA1'
            ];
            $fullHoursHeaderArray = [
                ['D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA'],
                ['AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY'],
                ['AZ', 'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO', 'BP', 'BQ', 'BR', 'BS', 'BT', 'BU', 'BV', 'BW'],
                ['BX', 'BY', 'BZ', 'CA', 'CB', 'CC', 'CD', 'CE', 'CF', 'CG', 'CH', 'CI', 'CJ', 'CK', 'CL', 'CM', 'CN', 'CO', 'CP', 'CQ', 'CR', 'CS', 'CT', 'CU'],
                ['CV', 'CW', 'CX', 'CY', 'CZ', 'DA', 'DB', 'DC', 'DD', 'DE', 'DF', 'DG', 'DH', 'DI', 'DJ', 'DK', 'DL', 'DM', 'DN', 'DO', 'DP', 'DQ', 'DR', 'DS'],
                ['DT', 'DU', 'DV', 'DW', 'DX', 'DY', 'DZ', 'EA', 'EB', 'EC', 'ED', 'EE', 'EF', 'EG', 'EH', 'EI', 'EJ', 'EK', 'EL', 'EM', 'EN', 'EO', 'EP', 'EQ'],
                ['ER', 'ES', 'ET', 'EU', 'EV', 'EW', 'EX', 'EY', 'EZ', 'FA', 'FB', 'FC', 'FD', 'FE', 'FF', 'FG', 'FH', 'FI', 'FJ', 'FK', 'FL', 'FM', 'FN', 'FO'],
                ['FP', 'FQ', 'FR', 'FS', 'FT', 'FU', 'FV', 'FW', 'FX', 'FY', 'FZ', 'GA', 'GB', 'GC', 'GD', 'GE', 'GF', 'GG', 'GH', 'GI', 'GJ', 'GK', 'GL', 'GM'],
                ['GN', 'GO', 'GP', 'GQ', 'GR', 'GS', 'GT', 'GU', 'GV', 'GW', 'GX', 'GY', 'GZ', 'HA', 'HB', 'HC', 'HD', 'HE', 'HF', 'HG', 'HH', 'HI', 'HJ', 'HK'],
                ['HL', 'HM', 'HN', 'HO', 'HP', 'HQ', 'HR', 'HS', 'HT', 'HU', 'HV', 'HW', 'HX', 'HY', 'HZ', 'IA', 'IB', 'IC', 'ID', 'IE', 'IF', 'IG', 'IH', 'II'],
                ['IJ', 'IK', 'IL', 'IM', 'IN', 'IO', 'IP', 'IQ', 'IR', 'IS', 'IT', 'IU', 'IV', 'IW', 'IX', 'IY', 'IZ', 'JA', 'JB', 'JC', 'JD', 'JE', 'JF', 'JG'],
                ['JH', 'JI', 'JJ', 'JK', 'JL', 'JM', 'JN', 'JO', 'JP', 'JQ', 'JR', 'JS', 'JT', 'JU', 'JV', 'JW', 'JX', 'JY', 'JZ', 'KA', 'KB', 'KC', 'KD', 'KE'],
                ['KF', 'KG', 'KH', 'KI', 'KJ', 'KK', 'KL', 'KM', 'KN', 'KO', 'KP', 'KQ', 'KR', 'KS', 'KT', 'KU', 'KV', 'KW', 'KX', 'KY', 'KZ', 'LA', 'LB', 'LC'],
                ['LD', 'LE', 'LF', 'LG', 'LH', 'LI', 'LJ', 'LK', 'LL', 'LM', 'LN', 'LO', 'LP', 'LQ', 'LR', 'LS', 'LT', 'LU', 'LV', 'LW', 'LX', 'LY', 'LZ', 'MA']
            ];
            $rangeArray = [
                'D2:AA2',
                'AB2:AY2',
                'AZ2:BW2',
                'BX2:CU2',
                'CV2:DS2',
                'DT2:EQ2',
                'ER2:FO2',
                'FP2:GM2',
                'GN2:HK2',
                'HL2:II2',
                'IJ2:JG2',
                'JH2:KE2',
                'KF2:LC2',
                'LD2:MA2'
            ];

            $columnDimensional = 'MB';
            $cellStyle = 'MA';
            $currentDate = Carbon::now()->toDateString();
            $next7Days = Carbon::now()->addDay(13)->toDateString();
            $columnDimensional = 'MZ';
            $cellStyle = 'MY';
            $weekArray[] = 'MB1:MY1';
            $fullHoursHeaderArray[] = ['MB', 'MC', 'MD', 'ME', 'MF', 'MG', 'MH', 'MI', 'MJ', 'MK', 'ML', 'MM', 'MN', 'MO', 'MP', 'MQ', 'MR', 'MS', 'MT', 'MU', 'MV', 'MW', 'MX', 'MY'];
            $rangeArray[] = 'MB2:MY2';
            $currentDate = Carbon::now()->subDay(1)->toDateString();
            
            for ($i = 'D'; $i != $columnDimensional; $i++) {
                $sheet->getColumnDimension($i)->setWidth(3);
            }

            $sheet->getStyle('A:'.$cellStyle)->getAlignment()->setHorizontal('center');

            if (!is_null($locationId)) {
                $sheet->setCellValue('A2', 'Name');
                $sheet->setCellValue('B2', 'Location');
                $sheet->setCellValue('C2', 'Timezone');
            } else {
                $sheet->mergeCells('A2:C2')->getCell('A2')->setValue('Name');
            }

            $start = 0;
            for ($i = 'D'; $i != $columnDimensional; $i++) {
                if ($start > 23) {
                    $start = 0;
                }
                if (strlen($start) < 2) {
                    $start = '0' . $start;
                }
                $sheet->setCellValue($i . '2', $start);
                $start++;
            }
            
            $period = CarbonPeriod::create($currentDate, $next7Days);
            $weekDayArray = [];
            $fullColumnHeaderArray = [];
            $cellValueArray = [];
            // Iterate over the period
            foreach ($period as $key => $date) {
                $setKey = $date->format('d') . '-' . $date->format('m');
                $tempWeek = explode(':', $weekArray[$key]);
                $displayString = $date->format('l') . ' - ' . $date->format('d') . ' ' . $date->format('M');
                $sheet->mergeCells($weekArray[$key])->getCell($tempWeek[0])->setValue($displayString);
                $weekDayArray[$setKey][strtolower($date->format('D'))] = $weekArray[$key];
                $fullColumnHeaderArray[$setKey][strtolower($date->format('D'))] = $fullHoursHeaderArray[$key];
                $cellValueArray[$setKey][strtolower($date->format('D'))] = $sheet->rangeToArray($rangeArray[$key]);
            }

            // Get Availability based on company
            $company = Company::find($this->payload['companyId']);

            // Get Selection Location Timezone
            $locationTimezone = '';
            $locationName = '';
            if (!is_null($locationId)) {
                $location = $company->locations()->where('id', $locationId)->select('name', 'timezone')->first();
                $locationTimezone = !empty($location) ? $location->timezone : null;
                $locationName = !empty($location) ? $location->name : null;
            }

            // Get this week Start and End Date
            $index = 3;
            foreach ($this->wbsRecords as $value) {
                $tempArray = [
                    $value['name'],
                    $locationName,
                    $appGmtTimezone . $now->setTimezone($value['timezone'])->format('P')
                ];

                $wsUser    = User::where('id', $value['id'])->first();
                $wcDetails = $wsUser->wsuser()->first();
                if ($wcDetails->is_authenticate) {
                    $authentication = $wsUser->cronofyAuthenticate()->first();
                    if (!empty($authentication)) {
                        $cronofyRepository->refreshToken($authentication);
                    }
                }

                $param = [
                    'from' => Carbon::now()->toDateString(),
                    'to' => Carbon::parse($next7Days)->addDays(1)->toDateString(),
                    'include_userinfo' => true,
                    'localized_times' => true,
                    'tzid' => $value['timezone'],
                    'include_free' => true,
                    'include_managed' => true,
                ];
                $getFreeBuzy  = $cronofyRepository->getFreeBuzySlots($value['id'], $param);
                $bookedSlotArray = [];
                foreach ($getFreeBuzy as $freebusy) {
                    // If location is enabled then use Location timezone instand of GMT timezone
                    $convertionTimezone = (!is_null($locationId) && !is_null($locationTimezone)) ? $locationTimezone : $appGmtTimezone;
                    $time = Carbon::parse($freebusy['start']['time'], $freebusy['start']['tzid'])->setTimezone($convertionTimezone);
                    $startDay = strtolower($time->format('D'));
                    $setKey = $time->format('d') . '-' . $time->format('m');
                    $startTime = $time->format('H');
                    $bookedSlotArray[$setKey][$startDay][] = $startTime;
                }
                if (!is_null($locationId)) {
                    $sheet->fromArray($tempArray, null, 'A' . $index);
                } else {
                    $sheet->mergeCells('A' . $index . ':C' . $index)->getCell('A' . $index)->setValue($value['name']);
                }

                $sheet->getStyle('D' . $index . ':'.$cellStyle . $index)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->setColor(new Color('#000000'));
                $sheet->getStyle('D' . $index . ':'.$cellStyle . $index)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('FFFF0000');

                $userRecords = User::find($value['id']);
                $getAvailability = $userRecords->healthCocahSlots()->select('day', 'start_time', 'end_time', 'user_id')->get();

                // Iterate over the period
                foreach ($period as $key => $date) {
                    $setKey = $date->format('d') . '-' . $date->format('m');
                    $day = strtolower($date->format('D'));

                    if (!empty($getAvailability)) {
                        foreach ($getAvailability as $records) {
                            $cellStartArray = array_combine($cellValueArray[$setKey][$day][0], $fullColumnHeaderArray[$setKey][$day]);
                            $cellEndArray = array_combine($cellValueArray[$setKey][$day][0], $fullColumnHeaderArray[$setKey][$day]);
                            if ($records['day'] == $day) {
                                if (!is_null($locationId)) {
                                    $startTime = Carbon::parse($records['start_time'])->format('H');
                                    $endTime = Carbon::parse($records['end_time'])->format('H');
                                    
                                    if($date->isBefore(Carbon::now()->toDateString())) {
                                        break;
                                    }
                                    $tempStartTime = Carbon::parse(Carbon::parse($date)->toDateString() . ' ' . $records['start_time'], $wsUser['timezone'])->setTimezone($locationTimezone);
                                    $tempEndTime = Carbon::parse(Carbon::parse($date)->toDateString() . ' ' . $records['end_time'], $wsUser['timezone'])->setTimezone($locationTimezone);
                                    $startTime = $tempStartTime->format('H');
                                    $endTime = $tempEndTime->format('H');
                                    $setStartKey = $tempStartTime->format('d') . '-' . $tempStartTime->format('m');
                                    $setEndKey = $tempEndTime->format('d') . '-' . $tempEndTime->format('m');
                                    $startDay = strtolower($tempStartTime->format('D'));
                                    $endDay = strtolower($tempEndTime->format('D'));
                                    $cellStartArray = array_combine($cellValueArray[$setStartKey][$startDay][0], $fullColumnHeaderArray[$setStartKey][$startDay]);
                                    $cellEndArray = array_combine($cellValueArray[$setEndKey][$endDay][0], $fullColumnHeaderArray[$setEndKey][$endDay]);
                                } else {
                                    if($date->isBefore(Carbon::now()->toDateString())) {
                                        break;
                                    }
                                    $tempStartTime = Carbon::parse(Carbon::parse($date)->toDateString() . ' ' . $records['start_time'], $wsUser['timezone'])->setTimezone($appGmtTimezone);
                                    $tempEndTime = Carbon::parse(Carbon::parse($date)->toDateString() . ' ' . $records['end_time'], $wsUser['timezone'])->setTimezone($appGmtTimezone);
                                    $startTime = $tempStartTime->format('H');
                                    $endTime = $tempEndTime->format('H');
                                    $setStartKey = $tempStartTime->format('d') . '-' . $tempStartTime->format('m');
                                    $setEndKey = $tempEndTime->format('d') . '-' . $tempEndTime->format('m');
                                    $startDay = strtolower($tempStartTime->format('D'));
                                    $endDay = strtolower($tempEndTime->format('D'));
                                    $cellStartArray = array_combine($cellValueArray[$setStartKey][$startDay][0], $fullColumnHeaderArray[$setStartKey][$startDay]);
                                    $cellEndArray = array_combine($cellValueArray[$setEndKey][$endDay][0], $fullColumnHeaderArray[$setEndKey][$endDay]);
                                }
                                $sheet->getStyle($cellStartArray[$startTime] . $index . ':' . $cellEndArray[$endTime] . $index)->getFill()
                                    ->setFillType(Fill::FILL_SOLID)
                                    ->getStartColor()
                                    ->setARGB(Color::COLOR_GREEN);
                            }
                            if (!empty($bookedSlotArray) && array_key_exists($setKey, $bookedSlotArray) && array_key_exists($day, $bookedSlotArray[$setKey])) {
                                $bookedCellArray = array_combine($cellValueArray[$setKey][$day][0], $fullColumnHeaderArray[$setKey][$day]);
                                foreach ($bookedSlotArray[$setKey][$day] as $valueKey) {
                                    $sheet->getStyle($bookedCellArray[$valueKey] . $index . ':' . $bookedCellArray[$valueKey] . $index)->getFill()
                                        ->setFillType(Fill::FILL_SOLID)
                                        ->getStartColor()
                                        ->setARGB(Color::COLOR_YELLOW);
                                }
                            }
                        }
                    }
                }
                $index = $index + 1;
            }
            $writer = new Xlsx($spreadsheet);
            $temp_file = tempnam(sys_get_temp_dir(), $fileName);
            $source = fopen($temp_file, 'rb');
            $writer->save($temp_file);

            $root     = config("filesystems.disks.spaces.root");
            $foldername = config('zevolifesettings.realtimefolderpath');

            $uploaded = uploadFileToSpaces($source, "{$root}/{$foldername}/{$fileName}", "public");
            if (null != $uploaded && is_string($uploaded->get('ObjectURL'))) {
                $url      = $uploaded->get('ObjectURL');
                $uploaded = true;
            }

            event(new RealtimeAvailabilityEvent($this->user, $url, $this->payload, $fileName));
            return true;
        } catch (\Exception $exception) {
            dd($exception);
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}
