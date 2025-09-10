<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeleteOldNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete notifications created before 7 days.';

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
            DB::table('notification_user')
                ->whereNotNull('sent_on')
                ->where('sent_on', '<=', Carbon::now()->subDays(7)->toDateTimeString())
                ->delete();

            DB::table('notifications')
                ->where('scheduled_at', '<=', Carbon::now()->subDays(7)->toDateTimeString())
                ->delete();

            cronlog($cronData, 1);
        } catch (\Exception $exception) {
            \Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
            $cronData['is_exception'] = 1;
            $cronData['log_desc']     = $exception->getMessage();
            cronlog($cronData, 1);
        }
    }
}
