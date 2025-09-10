<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Events\LocationExportEvent;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use DB;

class ExportLocationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $targetType;
    public $payload;
    /**
     * @var User $user
     */
    protected $user;
    public $columnName;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($payload, User $user)
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
            $timezone        = !empty($this->user->timezone) ? $this->user->timezone : config('app.timezone');
            $queryStr        = json_decode($this->payload['queryString'], true);
            $searchCountry   = !empty($queryStr['country']) ? $queryStr['country'] : $this->payload['country'];
            $searchTimeZone  = !empty($queryStr['timezone']) ? $queryStr['timezone'] : $this->payload['timezone'];
            
            if (!empty($this->payload['start_date'])) {
                $startDate = $this->payload['start_date'] . '00:00';
                $startDate = Carbon::parse($startDate, $timezone)->setTimeZone(config('app.timezone'))->toDateTimeString();
            }
            if (!empty($this->payload['end_date'])) {
                $endDate   = $this->payload['end_date'] . '23:59:59';
                $endDate   = Carbon::parse($endDate, $timezone)->setTimeZone(config('app.timezone'))->toDateTimeString();
            }

            $role    = getUserRole($this->user);
            $company = $this->user->company()->first();
            $query = \DB::table('company_locations')
            ->select(
                'companies.name AS company_name',
                'company_locations.name as location_name',
                'countries.name AS country_name',
                'states.name AS state_name',
                'company_locations.timezone as timezone',
                \DB::raw("CONCAT(company_locations.address_line1, ' ', IFNULL(company_locations.address_line2, '')) AS address")
            )
            ->join('companies', function ($join) {
                $join->on('companies.id', '=', 'company_locations.company_id');
            })
            ->join('countries', function ($join) {
                $join->on('countries.id', '=', 'company_locations.country_id');
            })
            ->join('states', function ($join) {
                $join->on('states.id', '=', 'company_locations.state_id');
            });

            if ($role->group == 'reseller') {
                $query
                ->where(function ($where) use ($company) {
                    $where
                        ->where('company_locations.company_id', $company->id)
                        ->orWhere('companies.parent_id', $company->id);
                });
            } elseif ($role->group == 'company') {
                $query->where('company_locations.company_id', $company->id);
            }

            if (!empty($queryStr['locationName'])) {
                $query->where('company_locations.name', 'like', '%' . $queryStr['locationName'] . '%');
            }

            if (!empty($searchCountry)) {
                $query->where('company_locations.country_id', $searchCountry);
            }

            if (!empty($this->payload['county'])) {
                $query->where('company_locations.state_id', $this->payload['county']);
            }

            if (!empty($searchTimeZone)) {
                $query->where('company_locations.timezone', 'like', '%' . $searchTimeZone . '%');
            }

            if (!empty($startDate) && !empty($endDate)) {
                $query->whereBetween('company_locations.created_at', [$startDate, $endDate]);
            } elseif (!empty($startDate) && empty($endDate)) {
                $query->where('company_locations.created_at', '>=', $startDate);
            } elseif (empty($startDate) && !empty($endDate)) {
                $query->where('company_locations.created_at', '<=', $endDate);
            }
            $query->orderByDesc('company_locations.updated_at');
            

            $userNPSDataRecords = $query->get()->chunk(100);
            if ($query->get()->count() == 0) {
                $messageData = [
                    'data'   => trans('challenges.messages.export_error'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.locations.index')->with('message', $messageData);
            }
            $dateTimeString = Carbon::now()->toDateTimeString();

            $sheetTitle = [
                'Company',
                'Location',
                'Country',
                'County',
                'Time/Zone',
                'Address'
            ];

            $fileName = "Location_".$dateTimeString.'.xlsx';
            $spreadsheet = new Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();
            $sheet->fromArray($sheetTitle, null, 'A1');
            
            $index = 2;
            foreach ($userNPSDataRecords as $value) {
                $records = (count($value) > 0) ? json_decode(json_encode($value), true) : [];
                $sheet->fromArray($records, null, 'A'.$index);
                $index = $index + count($value);
            }
            
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

            event(new LocationExportEvent($this->user, $url, $this->payload, $fileName));
            
        } catch (\Exception $exception) {
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}
