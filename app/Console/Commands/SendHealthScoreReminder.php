<?php

namespace App\Console\Commands;

use App\Jobs\SendHealthScoreReminderJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendHealthScoreReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:surveyReminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will send survey reminder if they asked to remind later.';

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
            $getUsers = DB::table('users')
                ->where("can_access_app", true)
                ->where("is_blocked", false)
                ->where('hs_remind_survey', true)
                ->get();

            foreach ($getUsers as $value) {
                if (isset($value) && $value->hs_reminded_at < now()->toDateTimeString()) {
                    \dispatch(new SendHealthScoreReminderJob($value));
                }
            }

            cronlog($cronData, 1);
        } catch (\Exception $exception) {
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
            $cronData['is_exception'] = 1;
            $cronData['log_desc']     = $exception->getMessage();
            cronlog($cronData, 1);
        }
    }
}
