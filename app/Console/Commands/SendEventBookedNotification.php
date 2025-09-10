<?php

namespace App\Console\Commands;

use App\Events\SendEventEmailNotesEvent;
use App\Events\SendEventReminderEvent;
use App\Events\EventBookedEvent;
use App\Events\EventStatusChangeEvent;
use App\Jobs\SendEventPushNotificationJob;
use App\Jobs\SendEventStatusChangeEmailJob;
use App\Jobs\SendEventBookedEamilJob;
use App\Models\EventBookingLogs;
use App\Models\EventBookingEmails;
use App\Models\UserTeam;
use App\Models\Event;
use App\Models\Company;
use App\Models\User;
use App\Models\CompanyLocation;
use App\Models\CompanyWiseCredit;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Log;

class SendEventBookedNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'event:booked';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will update event status from pending to booked and send notifications and emails to registered users before 12 hours of event start time also this will send email notes emails to registered users when event starts.';

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
            $appTimezone = config('app.timezone');
            $now         = now($appTimezone)->toDateTimeString();
            $pendingEventBookingRecords = $this->eventBookingLogs
                ->select(
                    'event_booking_logs.id',
                    'event_booking_logs.company_id',
                    'event_booking_logs.event_id',
                    'event_booking_logs.presenter_user_id',
                    'event_booking_logs.status',
                    'event_booking_logs.timezone as cronofyTimezone',
                    'users.timezone', 
                    'events.description', 
                    'events.duration', 
                    'events.name as eventName', 
                    'users.email', 
                    'users.timezone', 
                    \DB::raw("CONCAT(users.first_name,' ',users.last_name)  as presenterName"), 
                    'companies.name as companyName', 
                    \DB::raw("DATE_FORMAT(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time), '%M %d, %Y %h:%i %p') AS bookingDate"), 
                )
                ->leftJoin('events', 'events.id', '=', 'event_booking_logs.event_id')
                ->leftJoin('users', 'users.id', '=', 'event_booking_logs.presenter_user_id')
                ->leftJoin('companies', 'companies.id', '=', 'event_booking_logs.company_id')
                ->where('event_booking_logs.status', '6')
                ->where("event_booking_logs.registration_date", "<=", $now)
                ->orderBy('event_booking_logs.id')
                ->get();

            if (!empty($pendingEventBookingRecords)) {
                $pendingEventBookingRecords->each(function ($bookingLog) {
                    $ccEmails    = EventBookingEmails::select('email')->where('event_booking_log_id', $bookingLog->id)->whereNotNull('email')->get()->pluck('email')->toArray();
                    /**************************************** */
                    /*  SEND STATUS CHANGE MAIL TO STATIC MAIL ZENDESK AND CC EMAILS */
                    /***************************************** */
                    $appEnvironment = app()->environment();
                    if ($appEnvironment == 'production') {
                        $zendeskEmail = config('zevolifesettings.mail-zendesk-event.production.email');
                    } elseif ($appEnvironment == 'uat') {
                        $zendeskEmail = config('zevolifesettings.mail-zendesk-event.uat.email');
                    } elseif ($appEnvironment == 'local') {
                        $zendeskEmail = config('zevolifesettings.mail-zendesk-event.local.email');
                    } elseif ($appEnvironment == 'dev') {
                        $zendeskEmail = config('zevolifesettings.mail-zendesk-event.dev.email');
                    } else {
                        $zendeskEmail = config('zevolifesettings.mail-zendesk-event.qa.email');
                    }
                    array_push($ccEmails, $zendeskEmail);
                    $ccEmailsData = [
                        'company'        => (!empty($bookingLog->company_id) ? $bookingLog->company_id : null),
                        //'eventBookingId' => $bookingLog->id,
                        'eventName'      => $bookingLog->eventName,
                        'duration'       => $bookingLog->duration,
                        'presenterName'  => $bookingLog->presenterName,
                        'messageType'    => 'booked',
                        'emailType'      => 'booked',
                        'companyName'    => $bookingLog->companyName,
                        'eventStatus'    => 'Booked',
                        'timezone'       => $bookingLog->cronofyTimezone,
                    ];
                    foreach ($ccEmails as $nUser) {
                        $companyModeratorsDate         = Carbon::parse("{$bookingLog->bookingDate}", config('app.timezone'))->setTimezone($bookingLog->cronofyTimezone)->format('M d, Y h:i A');
                        $ccEmailsData['email']         = $nUser;
                        $ccEmailsData['bookingDate']   = $companyModeratorsDate;
                        event(new EventStatusChangeEvent($ccEmailsData));
                    }
                    //dispatch(new SendEventStatusChangeEmailJob($ccEmails, $statusChangeEmailData));

                    // Send users to notifications
                    $bookingCompany = Company::find($bookingLog->company_id);
                    $events    = Event::find($bookingLog->event_id);
                    $coUsersId = UserTeam::select('user_team.user_id')
                        ->where('user_team.company_id', $bookingLog->company_id)
                        ->whereNotIn('user_team.user_id', [$bookingLog->presenter_user_id])
                        ->get()->pluck('user_id')->toArray();

                    $notificationUser = User::select('users.id', 'users.email')
                        ->whereIn('users.id', $coUsersId)
                        ->where("users.is_blocked", false)
                        ->where(function ($query) use ($bookingCompany) {
                            if ($bookingCompany->is_reseller || (!$bookingCompany->is_reseller && !is_null($bookingCompany->parent_id))) {
                                $query->where("users.can_access_portal", true);
                            } elseif (!$bookingCompany->is_reseller && is_null($bookingCompany->parent_id)) {
                                $query->where("users.can_access_app", true);
                            }
                        })
                        ->get();
                    dispatch(new SendEventPushNotificationJob($events, "added", $notificationUser, [
                        'company_id' => $bookingLog->company_id,
                        'booking_id' => $bookingLog->id,
                    ]));
                    
                    // Update event status from pending to booked
                    $bookingLog->status = '4';
                    $bookingLog->save();

                    $bookingCompany = Company::find($bookingLog->company_id);
                   
                    $creditData     = [
                        'on_hold_credits' => ($bookingCompany->on_hold_credits > 0) ? $bookingCompany->on_hold_credits - 1 : 0,
                    ];
                    $bookingCompany->update($creditData);

                    $creditLogData  = [
                            'company_id'        => $bookingLog->company_id,
                            'user_name'         => 'Zevo Admin',
                            'credits'           => 1,
                            'notes'             => "Event " . $bookingLog->eventName." registered",
                            'type'              => 'Remove',
                            'available_credits' => $bookingCompany->credits,
                        ];
                
                    CompanyWiseCredit::insert($creditLogData);
                });
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
