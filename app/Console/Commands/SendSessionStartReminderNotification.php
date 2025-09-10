<?php

namespace App\Console\Commands;

use App\Models\CronofySchedule;
use Illuminate\Console\Command;
use DB;

class SendSessionStartReminderNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cronofy:sessionstartreminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notifications about session start reminder';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(): void
    {
        $cronData = [
            'cron_name'  => class_basename(__CLASS__),
            'unique_key' => generateProcessKey(),
        ];

        cronlog($cronData);

        try {
            // Code to send session reminder notification before 15 minutes of start time
            $now = \now(config('app.timezone'))->toDateTimeString();
            $diffArray = [
                0,
                (15 * 60), // 15 minutes
            ];
            CronofySchedule::select(
                'id',
                'user_id',
                'ws_id',
                'topic_id',
                'start_time',
                'created_by',
                'is_group'
            )->selectRaw(
                "TIMESTAMPDIFF(SECOND, ?, start_time) AS now_diff"
            ,[$now])
            ->whereNotIn('status', ['canceled', 'rescheduled', 'open'])
            ->where('start_time', '>', $now)
            ->havingRaw('(now_diff BETWEEN ? AND ? )', $diffArray)
            ->get()
            ->each
            ->sendSessionStartReminderNotification();

            cronlog($cronData, 1);
        } catch (\Exception $exception) {
            \Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
            $cronData['is_exception'] = 1;
            $cronData['log_desc']     = $exception->getMessage();
            cronlog($cronData, 1);
        }
    }
}
