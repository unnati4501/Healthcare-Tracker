<?php

namespace App\Console\Commands;

use App\Models\EventBookingLogs;
use App\Models\EventInviteSequenceUserLog;
use App\Models\EventBookingEmails;
use App\Jobs\SendEventExpiredEamilJob;
use App\Jobs\SendEventStatusChangeEmailJob;
use Illuminate\Console\Command;

class EventExpired extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'event:expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will change the event status to expired after the 36 hours passed from created date';

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
            $appTimezone    = config('app.timezone');
            $now = now($appTimezone)->toDateTimeString();

            // get event bookings that need to be completed
            $logs = $this->eventBookingLogs
                ->select('event_booking_logs.id', 'event_booking_logs.company_id as companyId', 'events.duration', 'events.name as eventName', 'users.email', 'users.first_name as presenterName', 'companies.name as companyName')
                ->selectRaw(
                    "DATE_FORMAT(CONVERT_TZ(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time), ? ,`users`.`timezone`), '%Y-%m-%d %h:%i %p') AS bookingDate"
                ,[$appTimezone])
                ->join('events', 'events.id', '=', 'event_booking_logs.event_id')
                ->join('users', 'users.id', '=', 'event_booking_logs.presenter_user_id')
                ->join('companies', 'companies.id', '=', 'event_booking_logs.company_id')
                ->where('event_booking_logs.status', '6')
                ->whereRaw("DATE_ADD(event_booking_logs.created_at, INTERVAL '36' HOUR) <= ?", $now)
                ->whereRaw("CONCAT(event_booking_logs.booking_date,' ',event_booking_logs.start_time) >= ?", $now)
                ->get()
                ->toArray();
            if (!empty($logs)) {
                // update status of bookings to expired
                foreach ($logs as $key => $chunk) {
                    $this->eventBookingLogs->where('id', $chunk['id'])->update(['event_booking_logs.status' => '7', 'created_at'=>$now]);
                    dispatch(new SendEventExpiredEamilJob($chunk));
                    
                    /****************************************************************** */
                    /*   SEND STATUS CHANGE MAIL TO STATIC MAIL ZENDESK AND CC EMAILS */
                    /****************************************************************** */
                    $statusChangeEmailData[$key] = [
                        'company'       => (!empty($chunk['companyId']) ? $chunk['companyId'] : null),
                        'eventBookingId'=> $chunk['id'],
                        'eventName'     => $chunk['eventName'],
                        'duration'      => $chunk['duration'],
                        'presenterName' => $chunk['presenterName'],
                        'type'          => 'presenter',
                        'bookingDate'   => $chunk['bookingDate'],
                        'companyName'   => $chunk['companyName'],
                        'eventStatus'   => 'Expired'
                    ];
                    $appEnvironment = app()->environment();
                    if ($appEnvironment == 'production') {
                        $zendeskEmail = config('zevolifesettings.mail-zendesk-event.production.email');
                    } elseif ($appEnvironment == 'uat') {
                        $zendeskEmail =  config('zevolifesettings.mail-zendesk-event.uat.email');
                    } elseif ($appEnvironment == 'local') {
                        $zendeskEmail =  config('zevolifesettings.mail-zendesk-event.local.email');
                    } elseif ($appEnvironment == 'dev') {
                        $zendeskEmail =  config('zevolifesettings.mail-zendesk-event.dev.email');
                    } else {
                        $zendeskEmail =  config('zevolifesettings.mail-zendesk-event.qa.email');
                    }
                    $ccEmails = EventBookingEmails::select('email')->where('event_booking_log_id', $chunk['id'])->whereNotNull('email')->get()->pluck('email')->toArray();
                    array_push($ccEmails, $zendeskEmail);
                    dispatch(new SendEventStatusChangeEmailJob($ccEmails, $statusChangeEmailData[$key]));
                }
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
