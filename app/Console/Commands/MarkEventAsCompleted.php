<?php

namespace App\Console\Commands;

use App\Models\EventBookingLogs;
use App\Models\EventInviteSequenceUserLog;
use Illuminate\Console\Command;

class MarkEventAsCompleted extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'event:markcompleted';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will mark event as completed once date and time is passed';

    /**
     * EventBookingLogs model object
     *
     * @var EventBookingLogs $eventBookingLogs
     */
    protected $eventBookingLogs;

    /**
     * EventInviteSequenceUserLog model object
     *
     * @var EventInviteSequenceUserLog $eventInviteSequenceUserLog
     */
    protected $eventInviteSequenceUserLog;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(EventBookingLogs $eventBookingLogs, EventInviteSequenceUserLog $eventInviteSequenceUserLog)
    {
        parent::__construct();
        $this->eventBookingLogs           = $eventBookingLogs;
        $this->eventInviteSequenceUserLog = $eventInviteSequenceUserLog;
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
            $now = now(config('app.timezone'))->toDateTimeString();

            // get event bookings that need to be completed
            $logs = $this->eventBookingLogs
                ->select('event_booking_logs.id')
                ->join('events', 'events.id', '=', 'event_booking_logs.event_id')
                ->where('event_booking_logs.status', '4')
                ->whereRaw("ADDTIME(TIMESTAMP(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time)), events.duration) <= ?",[$now])
                ->get()
                ->pluck('id')
                ->toArray();
            
            if (!empty($logs)) {
                // update status of bookings to completed
                $this->eventBookingLogs->whereIn('id', $logs)->update(['event_booking_logs.status' => '5']);

                // remove sequence entries from event_invite_sequence_user_logs table
                $this->eventInviteSequenceUserLog->whereIn('event_booking_log_id', $logs)->delete();
            }
            cronlog($cronData, 1);
        } catch (\Exception $exception) {
            \Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
            $cronData['is_exception'] = 1;
            $cronData['log_desc']     = $exception->getMessage();
            cronlog($cronData, 1);
        }
    }
}
