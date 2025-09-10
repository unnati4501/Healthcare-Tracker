<?php

namespace App\Console\Commands;

use App\Models\Challenge;
use Illuminate\Console\Command;

class SendStartReminderNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'challenge:startreminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notifications about challenge start reminder';

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
            // fetch all notifications which are pending to send
            $now = \now(config('app.timezone'))->toDateTimeString();

            Challenge::where('start_date', '>', $now)
                ->whereRaw('DATEDIFF(start_date,CURDATE()) <= ?',[1])
                ->where('cancelled', 0)
                ->where('finished', 0)
                ->get()
                ->each
                ->sendStartReminderNotification();

            cronlog($cronData, 1);
        } catch (\Exception $exception) {
            \Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
            $cronData['is_exception'] = 1;
            $cronData['log_desc']     = $exception->getMessage();
            cronlog($cronData, 1);
        }
    }
}
