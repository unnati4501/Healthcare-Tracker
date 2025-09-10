<?php

namespace App\Console\Commands;

use App\Models\Challenge;
use Illuminate\Console\Command;

class SendFinishReminderNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'challenge:finishreminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notifications about challenge end reminder';

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

            Challenge::where('end_date', '>=', $now)
                ->whereRaw(
                    "TIMESTAMPDIFF(HOUR,?, challenges.end_date) <= 24"
                ,[$now])
                ->whereRaw(
                    "TIMESTAMPDIFF(HOUR, ?, challenges.end_date) >= 0"
                ,[$now])
                ->where('cancelled', 0)
                ->where('finished', 0)
                ->get()
                ->each
                ->sendFinishReminderNotification();

            cronlog($cronData, 1);
        } catch (\Exception $exception) {
            \Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
            $cronData['is_exception'] = 1;
            $cronData['log_desc']     = $exception->getMessage();
            cronlog($cronData, 1);
        }
    }
}
