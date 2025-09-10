<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SendMoodCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:moodSurvey';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notifications to get moods of user';

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
            User::where('can_access_app', 1)
                ->where('is_blocked', 0)
                ->get()
                ->each
                ->sendMoodSurvey();

            cronlog($cronData, 1);
        } catch (\Exception $exception) {
            \Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
            $cronData['is_exception'] = 1;
            $cronData['log_desc']     = $exception->getMessage();
            cronlog($cronData, 1);
        }
    }
}
