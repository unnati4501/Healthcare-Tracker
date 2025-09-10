<?php

namespace App\Console\Commands;

use App\Models\UsersStepsAuthenticatorAvg;
use App\Models\UserStep;
use Carbon\Carbon;
use Illuminate\Console\Command;
use DB;

class UserFindAvgSteps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:findavgsteps';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find average last 15 day user steps.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $cronData = [
            'cron_name'  => class_basename(__CLASS__),
            'unique_key' => generateProcessKey(),
        ];

        cronlog($cronData);

        try {
            $todayDate    = Carbon::now()->toDateString();
            $procedureData = [
                $todayDate
            ];

            DB::select('CALL sp_calculate_user_step_avg(?)', $procedureData);
            cronlog($cronData, 1);
        } catch (\Exception $exception) {
            \Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
            $cronData['is_exception'] = 1;
            $cronData['log_desc']     = $exception->getMessage();
            cronlog($cronData, 1);
        }
    }
}
