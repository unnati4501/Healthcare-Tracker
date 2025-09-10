<?php

namespace App\Console\Commands;

use App\Models\CronofySchedule;
use Illuminate\Console\Command;

class SendUserConsentEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'session:consent';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will send email content once session completed.';

    /**
     * Cronofy Schedule model object
     *
     * @var CronofySchedule $cronofySchedule
     */
    protected $cronofySchedule;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(CronofySchedule $cronofySchedule)
    {
        parent::__construct();
        $this->cronofySchedule = $cronofySchedule;
    }

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
            $appTimezone       = config('app.timezone');
            $currentTime       = now($appTimezone)->todatetimeString();

            $this->cronofySchedule->select('id', 'cronofy_schedule.name', 'cronofy_schedule.updated_at', 'cronofy_schedule.user_id', 'cronofy_schedule.ws_id', 'cronofy_schedule.status')
                ->where("cronofy_schedule.end_time", "<=", $currentTime)
                ->where('cronofy_schedule.status', 'booked')
                ->where('cronofy_schedule.is_consent', false)
                ->where('cronofy_schedule.is_group', false)
                ->get()
                ->each
                ->sendSessionCompleteEmailConsent();

            cronlog($cronData, 1);
        } catch (\Exception $exception) {
            \Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
            $cronData['is_exception'] = 1;
            $cronData['log_desc']     = $exception->getMessage();
            cronlog($cronData, 1);
        }
    }
}
