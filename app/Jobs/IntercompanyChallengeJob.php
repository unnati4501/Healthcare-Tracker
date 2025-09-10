<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\ChallengeExportHistory;
use App\Events\IntercompanyChallengeExportEvent;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use DB;

class IntercompanyChallengeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $records;
    public $challenge;
    public $targetType;
    public $payload;
    public $user;
    public $columnName;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($records, $challenge, $targetType, $payload, $user, $columnName)
    {
        $this->queue                  = 'mail';
        $this->records                = $records;
        $this->challenge              = $challenge;
        $this->targetType             = $targetType;
        $this->payload                = $payload;
        $this->user                   = $user;
        $this->columnName             = $columnName;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $timezone    = !empty($this->user->timezone) ? $this->user->timezone : config('app.timezone');

            $startDate = $this->payload['start_date'] . '00:00';
            $endDate   = $this->payload['end_date'] . '23:59:59';

            $startDate = Carbon::parse($startDate, $timezone)->setTimeZone(config('app.timezone'))->toDateTimeString();
            $endDate   = Carbon::parse($endDate, $timezone)->setTimeZone(config('app.timezone'))->toDateTimeString();

            $challengeTargetType = $this->targetType;

            $challengeColumnName = $this->columnName;

            $challengeRecords = \DB::table('user_step')
                ->select(
                    DB::raw('concat(users.first_name," ",users.last_name) as user_name'),
                    'users.email',
                    'user_profile.birth_date',
                    'user_profile.gender',
                    'user_profile.height',
                    DB::raw('(SELECT weight FROM user_weight WHERE user_id = users.id ORDER BY id DESC LIMIT 1) AS weight'),
                    'companies.name as companies_name',
                    'companies.size as companies_size',
                    DB::raw('(SELECT name FROM industries WHERE id = companies.industry_id) AS industry'),
                    'teams.name',
                    'user_step.tracker',
                    'user_step.log_date'
                )
                ->selectRaw("? as targetType",[
                    $challengeTargetType
                ])
                ->selectRaw("IFNULL(?, '0') as stepsCount",[
                    $challengeColumnName
                ])
                ->leftJoin('user_profile', 'user_profile.user_id', '=', 'user_step.user_id')
                ->leftJoin('users', 'users.id', '=', 'user_profile.user_id')
                ->leftJoin('user_team', function ($join) {
                    $join
                        ->on('user_team.user_id', '=', 'user_step.user_id');
                })
                ->leftJoin('teams', 'teams.id', '=', 'user_team.team_id')
                ->leftJoin('companies', 'companies.id', '=', 'teams.company_id')
                ->whereBetween('user_step.log_date', [$startDate, $endDate])
                ->whereIn('user_step.user_id', $this->records)
                ->orderBy('user_step.log_date', 'DESC')
                ->get()
                ->chunk(100);

            $dateTimeString = Carbon::now()->toDateTimeString();

            $sheetTitle = [
                'User Name',
                'Email',
                'DOB',
                'Gender',
                'Height',
                'Weight',
                'Company Name',
                'Company Size',
                'Industry',
                'Team name',
                'Tracker Name',
                'Target Type',
                'Count',
                'Log Date UTC',
            ];

            $challengeTitle = str_replace(' ', '-', $this->challenge->title);

            $fileName = $challengeTitle . '_' . $dateTimeString . '.xlsx';

            $spreadsheet = new Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();
            $sheet->fromArray($sheetTitle, null, 'A1');

            $index = 2;
            foreach ($challengeRecords as $value) {
                $data = (count($value) > 0) ? json_decode(json_encode($value), true) : [];
                $sheet->fromArray($data, null, 'A'.$index);
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

            $challengeExportHistory = [
                'challenge_id'       => $this->challenge->id,
                'user_id'            => $this->user->id,
                'status'             => '1',
                'process_started_at' => $dateTimeString,
                'created_at'         => $dateTimeString,
                'updated_at'         => $dateTimeString,
            ];

            $challengeExportHistoryRecords = ChallengeExportHistory::create($challengeExportHistory);

            if ($challengeExportHistoryRecords) {
                event(new IntercompanyChallengeExportEvent($this->user, $this->challenge, $url, $this->payload, $challengeExportHistory, $fileName));
                return true;
            }
            return false;
        } catch (\Exception $exception) {
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}
