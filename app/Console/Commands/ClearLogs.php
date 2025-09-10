<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ClearLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:logs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear older entries from log tables';

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
            $createdAt      = "created_at";
            $then           = \now(config('app.timezone'))->subDays(7);
            $forTrackerLogs = \now(config('app.timezone'))->subDays(15);

            Schema::disableForeignKeyConstraints();
            DB::table('cron_logs')
                ->where($createdAt, '<', $then)
                ->delete();

            DB::table('api_logs')
                ->where($createdAt, '<', $then)
                ->delete();

            DB::table('tracker_logs')
                ->where($createdAt, '<', $forTrackerLogs)
                ->delete();

            DB::table('telescope_entries')->delete();

            DB::table('telescope_entries_tags')->delete();

            DB::table('telescope_monitoring')->delete();

            DB::table('failed_jobs')->delete();

            Schema::enableForeignKeyConstraints();

            cronlog($cronData, 1);
        } catch (\Exception $exception) {
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
            $cronData['is_exception'] = 1;
            $cronData['log_desc']     = $exception->getMessage();
            cronlog($cronData, 1);
        }
    }
}
