<?php

namespace App\Console\Commands;

use App\Events\SendEventEmailNotesEvent;
use App\Events\SendEventReminderEvent;
use App\Jobs\SendEventPushNotificationJob;
use App\Models\EventBookingLogs;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendEventReminderNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'event:reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will send reminder notifications and emails to registered users before 12 hours of event start time also this will send email notes emails to registered users before 12 and 24 hours of event start time.';

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
            // code to send email notes before 24 and 12 hours of event start time
            $diffArray = [
                0,
                (3600 * 12), // 12 hours
                ((3600 * 12) + 1), // 12+ hours
                (3600 * 24), // 24 hours
            ];
            $emailNoteRecords = $this->eventBookingLogs
                ->select(
                    'event_booking_logs.id',
                    'event_booking_logs.company_id',
                    'event_booking_logs.event_id',
                    'event_booking_logs.booking_date',
                    'event_booking_logs.start_time',
                    'event_booking_logs.email_notes',
                    'event_booking_logs.today_email_note_at',
                    'event_booking_logs.tomorrow_email_note_at'
                )->selectRaw(
                    "TIMESTAMPDIFF(SECOND, ?, CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time)) AS now_diff"
                ,[$now])
                ->with(['event' => function ($with) {
                    $with->select('events.id', 'events.company_id', 'events.name', 'events.creator_id');
                }])
                ->where('event_booking_logs.status', '4')
                ->whereNotNull('event_booking_logs.email_notes')
                ->havingRaw('((now_diff BETWEEN ? AND ? AND event_booking_logs.today_email_note_at IS NULL) OR (now_diff BETWEEN ? AND ? AND event_booking_logs.tomorrow_email_note_at IS NULL))', $diffArray)
                ->orderBy('event_booking_logs.id')
                ->get();
            if (!empty($emailNoteRecords)) {
                $emailNoteRecords->each(function ($bookingLog) use ($now) {
                    $company = $bookingLog->company()
                        ->select('companies.id', 'companies.enable_event', 'companies.is_reseller', 'companies.parent_id')
                        ->first();

                    // send notification if company is RSA/RCA or ZCA with enable_event as true
                    $sendNotifaction = (
                        $company->is_reseller ||
                        (!$company->is_reseller && !is_null($company->parent_id)) ||
                        (!$company->is_reseller && is_null($company->parent_id) && $company->enable_event)
                    );

                    if ($sendNotifaction) {
                        // Get registered users of event
                        $registeredUsers = $bookingLog->users()
                            ->select('users.id', 'users.first_name', 'users.last_name', 'users.email')
                            ->where('is_cancelled', 0)
                            ->get();

                        // set message and notification type
                        if (!empty($registeredUsers)) {
                            $columnName = (($bookingLog->now_diff >= 0 && $bookingLog->now_diff <= (3600 * 12)) ? "today_email_note_at" : "tomorrow_email_note_at");
                            $registeredUsers->chunk(1000)->each(function ($usersChunk) use ($bookingLog) {
                                // Send email notes email to registered users
                                $usersChunk->each(function ($user) use ($bookingLog) {
                                    event(new SendEventEmailNotesEvent($user, [
                                        "subject"    => "{$bookingLog->event->name} - Event Updated",
                                        "message"    => "Hi {$user->first_name},<br/><br/> This is to notify you that details of {$bookingLog->event->name} has been updated. Please make note of the changes below.",
                                        "emailNotes" => "{$bookingLog->email_notes}"
                                    ]));
                                });
                            });

                            // update `today_email_note_at` || `tomorrow_email_note_at` column.
                            $bookingLog->$columnName = $now;
                            $bookingLog->save();
                        }
                    }
                });
            }

            // code to send reminder notification before 30 minutes of event start time
            $diffArray = [
                0,
                1800, // 30 minutes
            ];
            $reminderRecords = $this->eventBookingLogs
                ->select(
                    'event_booking_logs.id',
                    'event_booking_logs.company_id',
                    'event_booking_logs.event_id',
                    'event_booking_logs.presenter_user_id',
                    'event_booking_logs.booking_date',
                    'event_booking_logs.start_time',
                    'event_booking_logs.end_time',
                    'event_booking_logs.today_reminder_at',
                    'event_booking_logs.tomorrow_reminder_at',
                    'event_booking_logs.email_notes',
                    'event_booking_logs.timezone'
                )->selectRaw(
                    "TIMESTAMPDIFF(SECOND, ?, CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time)) AS now_diff"
                ,[$now])
                ->with(['event' => function ($with) {
                    $with->select('events.id', 'events.deep_link_uri', 'events.company_id', 'events.name', 'events.creator_id');
                }])
                ->where('event_booking_logs.status', '4')
                ->where(function ($query) {
                    $query
                        ->whereNull('event_booking_logs.tomorrow_reminder_at')
                        ->orWhereNull('event_booking_logs.today_reminder_at');
                })
                ->havingRaw('(now_diff BETWEEN ? AND ? AND event_booking_logs.today_reminder_at IS NULL)', $diffArray)
                ->orderBy('event_booking_logs.id')
                ->get();

                if (!empty($reminderRecords)) {
                $reminderRecords->each(function ($bookingLog) use ($appTimezone, $now) {
                    $company = $bookingLog->company()
                        ->select('companies.id', 'companies.name', 'companies.enable_event', 'companies.is_reseller', 'companies.parent_id')
                        ->first();

                    // send notification if company is RSA/RCA or ZCA with enable_event as true
                    $sendNotifaction = (
                        $company->is_reseller ||
                        (!$company->is_reseller && !is_null($company->parent_id)) ||
                        (!$company->is_reseller && is_null($company->parent_id) && $company->enable_event)
                    );

                    if ($sendNotifaction) {
                        // Get registered users of event
                        $registeredUsers = $bookingLog->users()
                            ->select('users.id', 'users.first_name', 'users.last_name', 'users.email', 'users.timezone')
                            ->where('is_cancelled', 0)
                            ->get();

                        // set message and notification type
                        if (!empty($registeredUsers)) {
                            $timezone  = $company->locations()->select('timezone')->where('default', 1)->first();
                            $timezone  = (!empty($timezone->timezone) ? $timezone->timezone : $appTimezone);
                            $eventTime = Carbon::parse("{$bookingLog->booking_date} {$bookingLog->start_time}", $appTimezone)
                                ->setTimezone($bookingLog->timezone)->format("F d, h:i A");
                            
                            /******************* */
                            $fromTime      = Carbon::parse("{$bookingLog->booking_date} {$bookingLog->start_time}", $appTimezone)->setTimezone($timezone);
                            $endTime       = Carbon::parse("{$bookingLog->booking_date} {$bookingLog->end_time}", $appTimezone)->setTimezone($timezone);
                            $bookingDate   = $bookingLog->booking_date;
                            $endTime1      = strtotime($bookingDate. " " . $endTime->toTimeString());
                            $fromTime1     = strtotime($bookingDate. " " . $fromTime->toTimeString());
                            $duration      = round(abs($endTime1 - $fromTime1) / 60, 2) . " minute(s)";
                            $presenterName = $bookingLog->presenter()->select('users.first_name', 'users.last_name', 'users.email')->first();
                            $parentCompanyEmailData = [
                                'eventName'      => $bookingLog->event->name,
                                'companyName'    => $company->name,
                                'bookingDate'    => date("d-F-Y", strtotime($bookingDate)),
                                'eventStartTime' => $fromTime->toTimeString(),
                                'duration'       => $duration,
                                'presenterName'  => (!empty($presenterName->full_name)? $presenterName->full_name : null),
                                'emailNotes'     => (!empty($bookingLog->email_notes)? $bookingLog->email_notes : null)
                            ];
                            
                            /***********************/
                            if ($bookingLog->now_diff >= 0 && $bookingLog->now_diff <= 1800) {
                                $notificationType = "reminder-today";
                                $emailMessage     = "Hi :user_name,";
                                $columnName       = "today_reminder_at";
                            } else {
                                $notificationType = "reminder-tomorrow";
                                $emailMessage     = "Hi :user_name,";
                                $columnName       = "tomorrow_reminder_at";
                            }

                            // Check company plan access
                            $checkEventAccess = getCompanyPlanAccess([], 'event', $company);

                            if ($checkEventAccess) {
                                $registeredUsers->chunk(1000)->each(function ($usersChunk) use ($bookingLog, $eventTime, $notificationType, $emailMessage, $parentCompanyEmailData) {
                                    // Send reminder push notification to registered users
                                    dispatch(new SendEventPushNotificationJob($bookingLog->event, $notificationType, $usersChunk, [
                                        'company_id' => $bookingLog->company_id,
                                        'event_time' => $eventTime,
                                        'booking_id' => $bookingLog->id,
                                    ]));

                                    // Send reminder email to registered users
                                    $usersChunk->each(function ($user) use ($bookingLog, $emailMessage, $parentCompanyEmailData) {
                                        $bookingDateTime = Carbon::parse("{$bookingLog->booking_date} {$bookingLog->start_time}", config('app.timezone'))->setTimezone($bookingLog->timezone)->format('M d, Y h:i A');
                                        event(new SendEventReminderEvent($user, [
                                            "subject"           => "{$bookingLog->event->name} - Event Reminder",
                                            "message"           => __($emailMessage, ['user_name' => $user->first_name]),
                                            "companyName"       => $parentCompanyEmailData['companyName'],
                                            "bookingDate"       => $bookingDateTime,
                                            "timezone"          => $bookingLog->timezone,
                                            "duration"          => $parentCompanyEmailData['duration'],
                                            "eventName"         => $bookingLog->event->name,
                                            "presenterName"     => $parentCompanyEmailData['presenterName'],
                                            "emailNotes"        => (!empty($parentCompanyEmailData['emailNotes'])? $parentCompanyEmailData['emailNotes'] : null)
                                        ]));
                                    });
                                });
                            }

                            // update `tomorrow_reminder_at` || `today_reminder_at` column.
                            $bookingLog->$columnName = $now;
                            $bookingLog->save();
                        }
                    }
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
