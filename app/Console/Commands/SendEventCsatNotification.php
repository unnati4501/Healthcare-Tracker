<?php

namespace App\Console\Commands;

use App\Models\EventBookingLogs;
use Illuminate\Console\Command;

class SendEventCsatNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'event:csat';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will send Event CSAT(Feedback) notification to registered users after 12 hours of event get completed.';

    /**
     * EventBookingLogs model object
     *
     * @var EventBookingLogs $eventBookingLogs
     */
    protected $eventBookingLogs;

    /**
     * Create a new command instance.
     * @param EventBookingLogs $eventBookingLogs
     * @return void
     */
    public function __construct(EventBookingLogs $eventBookingLogs)
    {
        parent::__construct();
        $this->eventBookingLogs = $eventBookingLogs;
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
            $diffSeconds = (3600 * 12); // 12 Hours in seconds
            $appTimezone = config('app.timezone');
            $now         = now($appTimezone)->toDateTimeString();
            $this->eventBookingLogs
                ->select(
                    'event_booking_logs.id',
                    'event_booking_logs.event_id',
                    'event_booking_logs.company_id',
                )
                ->join('events', 'events.id', '=', 'event_booking_logs.event_id')
                ->where('event_booking_logs.status', '5')
                ->where('event_booking_logs.is_csat', true)
                ->whereNull('event_booking_logs.csat_at')
                ->whereRaw("TIMESTAMPDIFF(SECOND, ADDTIME(TIMESTAMP(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time)), events.duration), ?) >= ?", [$now, $diffSeconds])
                ->get()
                ->each
                ->sendCsatNotificaion();
            cronlog($cronData, 1);
        } catch (\Exception $exception) {
            \Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
            $cronData['is_exception'] = 1;
            $cronData['log_desc']     = $exception->getMessage();
            cronlog($cronData, 1);
        }
    }
}
