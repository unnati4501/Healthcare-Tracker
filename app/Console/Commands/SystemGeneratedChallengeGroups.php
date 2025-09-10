<?php

namespace App\Console\Commands;

use App\Models\Challenge;
use Illuminate\Console\Command;

class SystemGeneratedChallengeGroups extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:groups';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cron to generate and archive challenge based groups';

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
            $now = \now(config('app.timezone'))->toDateTimeString();

            Challenge::where('end_date', '<', $now)
                ->whereRaw("TIMEDIFF(UTC_TIMESTAMP(),end_date) >= ?",['47:00:00'])
                ->whereRaw("TIMEDIFF(UTC_TIMESTAMP(),end_date) <= ?",['49:00:00'])
                ->where('cancelled', 0)
                ->where('finished', 1)
                ->where('recurring', 0)
                ->get()
                ->each
                ->archiveAutoGenerateGroups();

            Challenge::where('end_date', '<', $now)
                ->where('recurring', 1)
                ->where('recurring_completed', 1)
                ->get()
                ->each
                ->archiveAutoGenerateGroups();

            cronlog($cronData, 1);
        } catch (\Exception $exception) {
            \Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
            $cronData['is_exception'] = 1;
            $cronData['log_desc']     = $exception->getMessage();
            cronlog($cronData, 1);
        }
    }
}
