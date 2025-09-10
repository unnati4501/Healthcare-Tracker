<?php

namespace App\Console\Commands;

use App\Models\Calendly;
use Illuminate\Console\Command;

class SendFeedbackEapComplete extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'eap:complete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will send notification to user after 1 hour of eap session completed.';
    /**
     * Calendly model object
     *
     * @var Calendly $calendly
     */
    protected $calendly;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Calendly $calendly)
    {
        parent::__construct();
        $this->calendly = $calendly;
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

            $this->calendly->select('id', 'eap_calendly.name', 'eap_calendly.updated_at', 'eap_calendly.user_id', 'eap_calendly.status')
                ->whereRaw("DATE_ADD(eap_calendly.end_time, INTERVAL '1' HOUR) <= ?", $currentTime)
                ->where('eap_calendly.status', '!=', 'canceled')
                ->where('eap_calendly.status', '!=', 'rescheduled')
                ->get()
                ->each
                ->sendEapCompleteNotificaion();

            cronlog($cronData, 1);
        } catch (\Exception $exception) {
            \Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
            $cronData['is_exception'] = 1;
            $cronData['log_desc']     = $exception->getMessage();
            cronlog($cronData, 1);
        }
    }
}
