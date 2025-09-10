<?php

namespace App\Models;

use App\Events\SendEventCancelledEvent;
use App\Events\EventStatusChangeEvent;
use App\Jobs\ExportBookingReportCompanyWiseJob;
use App\Jobs\ExportBookingReportDetailJob;
use App\Jobs\ExportBookingsJob;
use App\Jobs\SendEventPushNotificationJob;
use App\Jobs\SendEventStatusChangeEmailJob;
use App\Models\Company;
use App\Models\Event;
use App\Models\EventBookingEmails;
use App\Models\EventInviteSequenceUserLog;
use App\Models\EventRegisteredUserLog;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Yajra\DataTables\Facades\DataTables;

class EventBookingLogs extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'event_booking_logs';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['event_id', 'scheduling_id', 'slot_id', 'company_id', 'presenter_user_id', 'description', 'booking_date', 'start_time', 'end_time', 'status', 'register_all_users', 'is_csat', 'notes', 'email_notes', 'meta', 'tomorrow_email_note_at', 'today_email_note_at', 'tomorrow_reminder_at', 'today_reminder_at', 'csat_at', 'is_complementary', 'add_to_story', 'company_type', 'video_link', 'capacity_log', 'registration_date', 'timezone', 'old_presenter_user_id'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'event_id'          => 'integer',
        'company_id'        => 'integer',
        'presenter_user_id' => 'integer',
        'notes'             => 'string',
        'meta'              => 'object',
        'is_csat'           => 'boolean',
        'is_complementary'  => 'boolean',
        'add_to_story'      => 'boolean',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'tomorrow_reminder_at',
        'today_reminder_at',
        'tomorrow_email_note_at',
        'today_email_note_at',
        'csat_at',
    ];

    /**
     * "BelongsTo" relation to `events` table
     * via `event_id` field.
     *
     * @return BelongsTo
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    /**
     * "BelongsTo" relation to `companies` table
     * via `company_id` field.
     *
     * @return BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * "BelongsTo" relation to `users` table
     * via `presenter_user_id` field.
     *
     * @return BelongsTo
     */
    public function presenter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'presenter_user_id');
    }

    /**
     * "BelongsToMany" relation to `event_registered_users_logs` table
     * via `user_id` field.
     *
     * @return BelongsTo
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, EventRegisteredUserLog::class, 'event_booking_log_id')
            ->withPivot('is_cancelled')
            ->withTimestamps();
    }

    /**
     * "BelongsToMany" relation to `event_csat_user_logs` table
     * via `event_booking_log_id` field.
     *
     * @return hasMany
     */
    public function csat(): BelongsToMany
    {
        return $this->belongsToMany(User::class, EventCsatLogs::class, 'event_booking_log_id')
            ->withPivot('feedback', 'feedback_type')
            ->withTimestamps();
    }

    /**
     * "BelongsToMany" relation to `event_invite_sequence_user_logs` table
     * via `event_booking_log_id` field.
     *
     * @return hasMany
     */
    public function inviteSequence(): BelongsToMany
    {
        return $this->belongsToMany(User::class, EventInviteSequenceUserLog::class, 'event_booking_log_id')
            ->withPivot('sequence');
    }

    /**
     * @return hasMany
     */
    public function eventBookingEmails(): hasMany
    {
        return $this->hasMany('App\Models\EventBookingEmails', 'event_booking_log_id', 'id');
    }

    /**
     * To cancel an event of specific company
     *
     * @param  array $payload
     * @return array
     */
    public function cancelEvent($payload)
    {
        $data        = ['cancelled' => false, 'message' => "Failed to cancel an event."];
        $user        = auth()->user();
        $appTimezone = config('app.timezone');
        $now         = now($appTimezone);
        $role        = getUserRole($user);
        $companyName = "Zevo";
        if ($role->group != "zevo") {
            $companyName = $user->company()->first()->name;
        }
        // update status of event to 3(cancelled) and meta
        $meta                    = $this->meta;
        $uid                     = (!empty($meta->uid) ? $meta->uid : date('Ymd') . 'T' . date('His') . '-' . rand() . '@zevo.app');
        $meta->uid               = $uid;
        $meta->cancelled_by      = $user->id;
        $meta->cancelled_by_name = $user->full_name;
        $meta->cancelled_on      = $now->toDateTimeString();
        $meta->cancel_reason     = $payload['cancel_reason'];
        $updated                 = $this->update([
            'status' => '3',
            'meta'   => $meta,
        ]);

        // data for ical generation
        $iCalStartTime = Carbon::parse("{$this->booking_date} {$this->start_time}", $appTimezone);
        $iCalEndTime   = Carbon::parse("{$this->booking_date} {$this->end_time}", $appTimezone);
        $duration      = Carbon::parse($this->end_time)->diffInMinutes($this->start_time);
        if ($updated) {
            $presenter = $this->presenter()
                ->select('users.id', 'users.first_name', 'users.last_name', 'users.email', 'users.timezone')
                ->first();
            $eventCompanyDetails   = $this->company()->select('companies.name', 'companies.id', 'companies.parent_id')->first();
            $event                 = $this->event()->select('id', 'description', 'name', 'deep_link_uri')->first();
            $timezone              = $this->company->locations()->select('timezone')->where('default', 1)->first();
            $bookingDate           = Carbon::parse("{$this->booking_date} {$this->start_time}", $appTimezone)
                ->setTimezone($timezone->timezone)->format('M d, Y h:i A');

            // delete events related notifications
            Notification::where('company_id', $this->company_id)
                ->where(function ($query) use ($event) {
                    $query
                        ->where('deep_link_uri', 'LIKE', $event->deep_link_uri . '/%')
                        ->orWhere('deep_link_uri', 'LIKE', $event->deep_link_uri);
                })
                ->delete();

            if (!empty($presenter)) {
                // get sequence number presenter for send a cancel event email
                $sequenceLog = $this->inviteSequence()->select('users.id')->where('user_id', $presenter->id)->first();
                $sequence    = 0;
                if (is_null($sequenceLog)) {
                    // record not exist adding
                    $this->inviteSequence()->attach($presenter);
                    $sequence = 0;
                } else {
                    // record exist updating sequence
                    $sequence = ($sequenceLog->pivot->sequence + 1);
                    $sequenceLog->pivot->update([
                        'sequence' => $sequence,
                    ]);
                }

                // send event cancelled email to presenter
                $presenterBookingDate = Carbon::parse("{$this->booking_date} {$this->start_time}", $appTimezone)
                    ->setTimezone($presenter->timezone);
                $getCronofyEmail = CronofyCalendar::select('profile_name')->where('user_id', $presenter->id)->where('primary',true)->first();
                $startPresenterTime             = Carbon::parse("{$this->booking_date} {$this->start_time}", config('app.timezone'));
                $endPresenterTime               = Carbon::parse("{$this->booking_date} {$this->end_time}", config('app.timezone'));
                
                $presenterEmailData['subject'] = "{$event->name} - Event Cancelled";
                $presenterEmailData['message'] = "Hi {$presenter->first_name},<br/><br/>This is to notify you that the {$event->name} event, with {$eventCompanyDetails->name} scheduled on {$presenterBookingDate->format('M d, Y')} at {$presenterBookingDate->format('h:i A')} has been cancelled.<br/><br/>More details will follow from Zevo Team.";

                if(!empty($getCronofyEmail) && $getCronofyEmail->profile_name != $presenter->email){
                    $presenterEmailData['iCal']  = generateiCal([
                        'uid'         => $uid,
                        'appName'     => config('app.name'),
                        'inviteTitle' => $event->name,
                        'description' => $event->description,
                        'timezone'    => $presenter->timezone,
                        'today'       => Carbon::parse(now(config('app.timezone')))->format('Ymd\THis\Z'),
                        'startTime'   => Carbon::parse($startPresenterTime)->format('Ymd\THis\Z'),
                        'endTime'     => Carbon::parse($endPresenterTime)->format('Ymd\THis\Z'),
                        'orgName'     => $presenter->full_name,
                        'orgEamil'    => $getCronofyEmail->profile_name,
                        'userEmail'   => $presenter->email,
                        'sequence'    => 1,
                    ],'cancelled');
                }
                event(new SendEventCancelledEvent($presenter, $presenterEmailData));

                /**************************************** */
                /*  SEND STATUS CHANGE MAIL TO STATIC MAIL ZENDESK AND CC EMAILS */
                /***************************************** */
                $ccEmails       = EventBookingEmails::select('email')->where('event_booking_log_id', $this->id)->whereNotNull('email')->get()->pluck('email')->toArray();
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
                // $statusChangeEmailData = [
                //     'company'        => (!empty($eventCompanyDetails->id) ? $eventCompanyDetails->id : null),
                //     'eventBookingId' => $this->id,
                //     'eventName'      => $event->name,
                //     'duration'       => $duration,
                //     'presenterName'  => $presenter->full_name,
                //     'messageType'    => 'booked',
                //     'bookingDate'    => Carbon::parse("{$this->booking_date} {$this->start_time}", $appTimezone)->format('M d, Y h:i A'),
                //     'companyName'    => $eventCompanyDetails->name,
                //     'eventStatus'    => 'Cancelled',
                // ];
                // dispatch(new SendEventStatusChangeEmailJob($ccEmails, $statusChangeEmailData));
            }

            if (!empty($presenter)) {
                // send event cancelled email to presenter the associated admins
                $moderators = $this->company->moderators()
                    ->select('users.id', 'users.first_name', 'users.last_name', 'users.email', 'users.timezone')
                    ->where('users.id', $user->id)
                    ->whereNotIn('users.id', [$presenter->id])
                    ->get();
                $moderatorIds            = $moderators->pluck('id')->toArray();
                $bookingDate             = $this->booking_date;
                $startTime               = $this->start_time;
                $endTime                 = $this->end_time;
                $eventBookingLogTimezone = $this->timezone;

                if ($role->slug != 'super_admin') {
                    $moderators->each(function ($moderator) use ($uid, $event, $bookingDate, $startTime, $endTime, $presenter, $eventBookingLogTimezone) {
                        $userData    = User::find($moderator->id);
                        $sequenceLog = $this->inviteSequence()->select('users.id')->where('user_id', $moderator->id)->first();
                        $sequence    = 0;
                        if (is_null($sequenceLog)) {
                            // record not exist adding
                            $this->inviteSequence()->attach([$moderator->id]);
                            $sequence = 0;
                        } else {
                            // record exist updating sequence
                            $sequence = ($sequenceLog->pivot->sequence + 1);
                            $sequenceLog->pivot->update([
                                'sequence' => $sequence,
                            ]);
                        }
                        $companyModeratorsDate = Carbon::parse("{$bookingDate} {$startTime}", config('app.timezone'))->setTimezone($eventBookingLogTimezone)->format('M d, Y h:i A');
                        $startTime             = Carbon::parse("{$bookingDate} {$startTime}", config('app.timezone'));
                        $endTime               = Carbon::parse("{$bookingDate} {$endTime}", config('app.timezone'));

                        event(new SendEventCancelledEvent($userData, [
                            "subject" => "{$event->name} - Event Cancelled",
                            "message" => "Hi {$moderator->first_name}, <br/><br/> This is to notify you that the planned {$event->name} Event on {$companyModeratorsDate} has been cancelled.",
                            'iCal'    => generateiCal([
                                'uid'         => $uid,
                                'appName'     => config('app.name'),
                                'inviteTitle' => $event->name,
                                'description' => $event->description,
                                'timezone'    => $moderator->timezone,
                                'today'       => Carbon::parse(now(config('app.timezone')))->format('Ymd\THis\Z'),
                                'startTime'   => Carbon::parse($startTime)->format('Ymd\THis\Z'),
                                'endTime'     => Carbon::parse($endTime)->format('Ymd\THis\Z'),
                                'orgName'     => $presenter->full_name,
                                'orgEamil'    => $presenter->email,
                                'userEmail'   => $moderator->email,
                                'sequence'    => $sequence,
                            ], 'cancelled'),
                        ]));
                    });
                }

                $companyModeratorsDate = Carbon::parse("{$bookingDate} {$startTime}", config('app.timezone'))->setTimezone($eventBookingLogTimezone)->format('M d, Y h:i A');
                $ccEmailsData = [
                    "subject"        => "{$event->name} - Event Cancelled",
                    'emailType'      => 'canceled',
                    'eventStatus'    => 'Cancelled',
                    'company'        => (!empty($eventCompanyDetails->id) ? $eventCompanyDetails->id : null),
                    'eventName'      => $event->name,
                    'duration'       => $duration,
                    'timezone'       => $eventBookingLogTimezone,
                    'presenterName'  => $presenter->full_name,
                    'companyName'    => $eventCompanyDetails->name,
                    'eventStatus'    => 'Cancelled',
                ];
                foreach ($ccEmails as $nUser) {
                    $ccEmailsData['email']         = $nUser;
                    $ccEmailsData['bookingDate']   = $companyModeratorsDate;
                    event(new EventStatusChangeEvent($ccEmailsData));
                }
                
                // send event cancelled event push notification to registered users
                $registeredUsers = $this->users()
                    ->select('users.id', 'users.first_name', 'users.last_name', 'users.email')
                    ->whereNotIn('users.id', $moderatorIds)
                    ->where('is_cancelled', 0)
                    ->get();
                if (!empty($registeredUsers)) {
                    // get users previous sequence to send an update event email
                    $registeredUserIds = $registeredUsers->pluck('id')->toArray();
                    $sequenceLogs      = $this->inviteSequence()->select('users.id')->whereIn('user_id', $registeredUserIds)->get();
                    $sequenceLogs      = $sequenceLogs->pluck('pivot.sequence', 'id')->toArray();

                    // prepare iCal invite array for registered users
                    $usersiCalData = [
                        'uid'         => $uid,
                        'appName'     => config('app.name'),
                        'inviteTitle' => $event->name,
                        'description' => "{$event->name} event has been cancelled.",
                        'timezone'    => $appTimezone,
                        'today'       => $now->format('Ymd\THis\Z'),
                        'startTime'   => $iCalStartTime->format('Ymd\THis\Z'),
                        'endTime'     => $iCalEndTime->format('Ymd\THis\Z'),
                        'orgName'     => (!empty($presenter) ? $presenter->full_name : (!empty($meta->presenter) ? $meta->presenter : 'Zevo')),
                        'orgEamil'    => (!empty($presenter) ? $presenter->email : 'admin@zevo.app'),
                        'sequence'    => 1,
                    ];

                    $bookingRecord = $this;
                    $registeredUsers->chunk(100)->each(function ($usersChunk) use ($event, $bookingDate, $bookingRecord, $usersiCalData, $sequenceLogs, $companyName) {
                        $usersChunk->each(function ($regUser) use ($event, $bookingDate, $bookingRecord, $usersiCalData, $sequenceLogs, $companyName) {
                            // update sequence
                            $usersiCalData['sequence'] = (($sequenceLogs[$regUser->id] ?? 0) + 1);

                            // send cancel email
                            event(new SendEventCancelledEvent($regUser, [
                                "subject" => "{$event->name} - Event Cancelled",
                                "message" => "Hi {$regUser->first_name}, <br/><br/>This is to notify you that the {$event->name} event, which was scheduled at {$bookingDate} has been cancelled by {$companyName}.<br/><br/>More details will follow from Zevo Health.",
                                "iCal"    => generateiCal($usersiCalData, 'cancelled'),
                            ]));

                            // update sequence in database
                            $bookingRecord->inviteSequence()->where('user_id', $regUser->id)->update([
                                'sequence' => $usersiCalData['sequence'],
                            ]);
                        });
                    });

                    // Send event cancelled push notification to registered users
                    dispatch(new SendEventPushNotificationJob($event, "removed", $registeredUsers, [
                        'company_id' => $bookingRecord->company_id,
                    ]));
                }
            }

            $data['cancelled']     = true;
            $data['message']       = "Event has been cancelled successfully.";
            $data['presenter_id']  = $presenter->id;
            $data['scheduling_id'] = $this->scheduling_id;
            if (isset($payload['referrer']) && $payload['referrer'] == "bookingPage") {
                \Session::put('message', [
                    'data'   => $data['message'],
                    'status' => $data['cancelled'],
                ]);
            }
        }

        return $data;
    }

    /**
     * Get cancelled event details
     *
     * @param  array $payload
     * @return array
     */
    public function cancelEventDetails()
    {
        $user        = auth()->user();
        $appTimezone = config('app.timezone');
        $timezone    = (!empty($user->timezone) ? $user->timezone : $appTimezone);

        return [
            'status'             => 1,
            'cancelled_by'       => ($this->meta->cancelled_by_name ?? "No data available"),
            'cancelled_at'       => ((!empty($this->meta->cancelled_on)) ? Carbon::parse($this->meta->cancelled_on, $appTimezone)->setTimeZone($timezone)->format('M d, Y h:i A') : "No data available"),
            'cancelation_reason' => ($this->meta->cancel_reason ?? "No data available"),
        ];
    }

    /**
     * Get booked events
     *
     * @param payload
     * @return dataTable
     */
    public function getBookedEvents($payload)
    {
        $user        = auth()->user();
        $role        = getUserRole($user);
        $appTimezone = config('app.timezone');
        $list        = $this->getBookedEventsRecords($payload);
        $dt          = DataTables::of($list['record'])
            ->skipPaging()
            ->addColumn('logo', function ($record) {
                return "<div class='table-img table-img-l'><img src='{$record->event->logo}'></div>";
            })
            ->addColumn('end_time', function ($record) use ($appTimezone) {
                return Carbon::parse("{$record->booking_date} {$record->end_time}", $appTimezone)
                    ->toDateTimeString();
            })
            ->addColumn('duration', function ($record) use ($appTimezone) {
                return Carbon::parse("{$record->booking_date} {$record->start_time}", $appTimezone)
                    ->toDateTimeString();
            })
            ->addColumn('eventStatus', function ($record) use ($role) {
                $allStatus = config('zevolifesettings.event-status-master');
                $status    = $allStatus[$record->eventStatus];
                if (((Carbon::parse("{$record->booking_date} {$record->start_time}", config('app.timezone'))->subMinutes(15)) <= now(config('app.timezone'))) && ((Carbon::parse("{$record->booking_date} {$record->end_time}", config('app.timezone'))) >= now(config('app.timezone'))) && $record->eventStatus == '4' && $record->location_type == 1 && $role->slug == 'wellbeing_specialist') {
                    return "<a class='btn btn-success' target='_blank' href='" . $record->video_link . "'>Join</a>";
                } elseif (((Carbon::parse("{$record->booking_date} {$record->end_time}", config('app.timezone'))) <= now(config('app.timezone')) || $record->eventStatus == '5') && $record->eventStatus != '3') {
                    return "<span class='text-success'>Completed</a>";
                } else {
                    return $status['text'];
                }
            })
            ->addColumn('actions', function ($record) use ($role, $appTimezone) {
                $todayDate = Carbon::now()->toDateTimeString();
                $allStatus = config('zevolifesettings.event-status-master');
                $status    = $allStatus[$record->eventStatus];
                $duration  = Carbon::parse("{$record->booking_date} {$record->start_time}", $appTimezone)
                    ->toDateTimeString();
                $advanceDate    = Carbon::now()->addHours(48)->toDateTimeString();
                $eventStartTime = Carbon::parse("{$record->booking_date} {$record->start_time}")->toDateTimeString();
    
                if (((Carbon::parse("{$record->booking_date} {$record->start_time}", config('app.timezone'))->subMinutes(15)) <= now(config('app.timezone'))) && ((Carbon::parse("{$record->booking_date} {$record->end_time}", config('app.timezone'))) >= now(config('app.timezone'))) && $record->eventStatus == '4' && $role->slug == 'wellbeing_specialist') {
                    $eventStatus = 'Online';
                } elseif (((Carbon::parse("{$record->booking_date} {$record->end_time}", config('app.timezone'))) <= now(config('app.timezone')) || $record->eventStatus == '5') && $record->eventStatus != '3') {
                    $eventStatus = 'Completed';
                } else {
                    $eventStatus = $status['text'];
                }
                return view('admin.booking.booked-tab-listaction', compact('record', 'role', 'eventStatus', 'todayDate', 'duration', 'advanceDate', 'eventStartTime'))->render();
            })
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ]);
        return $dt
            ->rawColumns(['logo', 'duration', 'actions', 'eventStatus'])
            ->make(true);
    }

    /**
     * Get booked events record list
     *
     * @param array $payload
     * @return array
     */
    public function getBookedEventsRecords($payload)
    {

        $user        = auth()->user();
        $appTimezone = config('app.timezone');
        $timezone    = (!empty($user->timezone) ? $user->timezone : $appTimezone);
        $role        = getUserRole($user);
        $company     = $user->company()->first();
        $now         = now($appTimezone)->toDateTimeString();
        $events      = $this
            ->select(
                'event_booking_logs.id',
                'event_booking_logs.event_id',
                'events.name AS event_name',
                'companies.name AS company_name',
                \DB::raw('IF(events.is_special = "1", events.special_event_category_title , sub_categories.name) AS subcategory_name'),
                \DB::raw("CONCAT(users.first_name,' ',users.last_name) AS presenter"),
                'event_booking_logs.booking_date',
                'event_booking_logs.registration_date',
                'event_booking_logs.start_time',
                'event_booking_logs.end_time',
                'event_booking_logs.status as eventStatus',
                'event_booking_logs.notes as additionalNotes',
                'event_booking_logs.video_link',
                'events.location_type',
            )
            ->selectRaw("? as timezone",[
                $timezone
            ])
            ->with(['event' => function ($query) {
                $query->select('id');
            }])
            ->withCount([
                'users' => function ($query) {
                    return $query->where('is_cancelled', false);
                },
            ]);

        $events = $events->join('events', 'events.id', '=', 'event_booking_logs.event_id')
            ->leftJoin('users', 'users.id', '=', 'event_booking_logs.presenter_user_id')
            ->leftJoin('companies', 'companies.id', '=', 'event_booking_logs.company_id')
            ->leftJoin('sub_categories', 'sub_categories.id', '=', 'events.subcategory_id')
            ->where('events.status', 2)
            ->where(function ($query) use ($role, $company, $user) {
                if ($role->group == 'zevo') {
                    $query->whereNull('events.company_id');
                    if ($role->slug == 'wellbeing_specialist') {
                        $query->where('event_booking_logs.presenter_user_id', $user->id);
                    }
                } elseif ($role->group == 'reseller') {
                    if ($company->is_reseller) {
                        $assigneeComapnies = Company::select('id')
                            ->where('parent_id', $company->id)
                            ->orWhere('id', $company->id)
                            ->get()->pluck('id')->toArray();
                        $query
                            ->whereIn('event_booking_logs.company_id', $assigneeComapnies)
                            ->where(function ($where) use ($company) {
                                $where
                                    ->whereNull('events.company_id')
                                    ->orWhere('events.company_id', $company->id);
                            });
                    } elseif (!is_null($company->parent_id)) {
                        $query->where('event_booking_logs.company_id', $company->id);
                    }
                }
            })
            ->when(($payload['name']), function ($when, $name) {
                $when->where('events.name', 'like', "%" . $name . "%");
            });
        if ($role->slug != 'wellbeing_specialist') {
            $events = $events->when($payload['presenter'], function ($query, $presenter) {
                $query->where('event_booking_logs.presenter_user_id', $presenter);
            });
        }
        if ($role->slug == 'wellbeing_specialist') {
            $events->addSelect(\DB::raw("CASE
            WHEN DATE_SUB(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time), INTERVAL 15 MINUTE )
            <= UTC_TIMESTAMP() AND CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.end_time) >= UTC_TIMESTAMP() AND events.location_type = 1 AND event_booking_logs.status = '4' then 1
            ELSE 2
            END AS displayorder"));
        }
        $events = $events->when($payload['company'], function ($query, $company) {
            $query->where('event_booking_logs.company_id', $company);
        })
            ->when($payload['category'], function ($query, $category) {
                $query->where('events.subcategory_id', $category);
            })
            ->where(function ($query) use ($payload, $now) {
                if ($payload['eventStatus'] != null) {

                    if ($payload['eventStatus'] == 5) {
                        $query->where(function ($query) use ($payload, $now) {
                            $query
                                ->where('event_booking_logs.status', '5')
                                ->orWhereRaw("CONCAT(event_booking_logs.booking_date,' ',event_booking_logs.end_time) <= ?", [$now]);

                        });
                        $query->where('event_booking_logs.status', '!=', '3');
                    } elseif ($payload['eventStatus'] == 6) {
                        // Paused filter
                        $query->where(function ($query) use ($now) {
                            $query
                                ->where('event_booking_logs.status', '6')
                                ->whereRaw("CONCAT(event_booking_logs.booking_date,' ',event_booking_logs.start_time) >= ?", [$now]);

                        });
                    } elseif ($payload['eventStatus'] == 3) {
                        $query->where('event_booking_logs.status', '3');
                    } elseif ($payload['eventStatus'] == 4) {
                        $query->where('event_booking_logs.status', '4');
                        $query->where(function ($query) use ($now) {
                            $query->whereRaw("DATE_SUB(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time), INTERVAL 15 MINUTE ) <= ?", [$now])
                                ->whereRaw("CONCAT(event_booking_logs.booking_date,' ',event_booking_logs.end_time) >= ?", [$now]);

                            $query->orWhere(function ($query) use ($now) {
                                $query->whereRaw("CONCAT(event_booking_logs.booking_date,' ',event_booking_logs.start_time) >= ?", [$now])
                                    ->whereRaw("CONCAT(event_booking_logs.booking_date,' ',event_booking_logs.end_time) >= ?", [$now]);
                            });
                        });
                    } else {
                        $query->where('event_booking_logs.status', $payload['eventStatus']);
                    }
                }
            });
            if ($role->slug != 'wellbeing_specialist') {
                $events->addSelect(\DB::raw("CASE
                    WHEN CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.end_time)
                    >= UTC_TIMESTAMP() AND event_booking_logs.status = '6' then 1
                    WHEN ( CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time)
                    >= UTC_TIMESTAMP() AND CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.end_time)
                    >= UTC_TIMESTAMP() )AND event_booking_logs.status = '4' then 2
                    WHEN event_booking_logs.status = '3' then 3
                    ELSE 4
                    END AS displayorder"));
            }
            $events = $events->groupBy('event_booking_logs.id');

        if (isset($payload['order'])) {
            $column = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order  = $payload['order'][0]['dir'];
            $events->orderBy($column, $order);
        } elseif ($role->slug == 'wellbeing_specialist') {
            $events->orderBy("displayorder", 'ASC')
                ->orderBy('event_booking_logs.updated_at', 'DESC');
        } else {
            $events->orderBy("displayorder", 'ASC')
            ->orderBy('event_booking_logs.updated_at', 'DESC');
        }

        return [
            'total'  => $events->get()->count(),
            'record' => $events->offset($payload['start'])->limit($payload['length'])->get(),
        ];
    }

    /**
     * Get detailed report of event booking
     *
     * @param array payload
     * @return dataTable
     */
    public function getDetailedReport($payload)
    {
        $appTimezone = config('app.timezone');
        $list        = $this->getDetailedReportRecords($payload);
        $status      = config('zevolifesettings.event-status-master');
        return DataTables::of($list['record'])
            ->skipPaging()
            ->addColumn('end_time', function ($record) use ($appTimezone) {
                return Carbon::parse("{$record->booking_date} {$record->end_time}", $appTimezone)
                    ->toDateTimeString();
            })
            ->addColumn('date_time', function ($record) use ($appTimezone) {
                return Carbon::parse("{$record->booking_date} {$record->start_time}", $appTimezone)
                    ->toDateTimeString();
            })
            ->addColumn('location_type', function ($record) {
                return config('zevolifesettings.event-location-type')[$record->location_type];
            })
            ->addColumn('is_complementary', function ($record) {
                return ($record->is_complementary) ? "Yes" : "No";
            })
            ->addColumn('status', function ($record) use ($status) {
                $recordStatus = $status[$record->status];
                return "<span class='{$recordStatus['class']}'>{$recordStatus['text']}</span>";
            })
            ->addColumn('actions', function ($record) {
                return view('admin.report.event.booking-report-listaction', compact('record'))->render();
            })
            ->addColumn('cancelled_on', function ($record) {
                if ($record->cancelled_on) {
                    return Carbon::parse($record->cancelled_on)->format(config('zevolifesettings.date_format.default_datetime_24hours'));
                } else {
                    return '';
                }
            })
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ])
            ->rawColumns(['date_time', 'status', 'actions'])
            ->make(true);
    }

    /**
     * Get detailed report records
     *
     * @param array $payload
     * @return array
     */
    public function getDetailedReportRecords($payload)
    {
        $user        = auth()->user();
        $appTimezone = config('app.timezone');
        $timezone    = (!empty($user->timezone) ? $user->timezone : $appTimezone);
        $role        = getUserRole($user);
        $company     = $user->company()->first();
        $events      = $this
            ->select(
                'event_booking_logs.id',
                'event_booking_logs.event_id',
                'events.name AS event_name',
                'sub_categories.name AS subcategory_name',
                \DB::raw('meta->>"$.presenter" AS presenter'),
                'event_booking_logs.booking_date',
                'event_booking_logs.start_time',
                'event_booking_logs.end_time',
                'event_booking_logs.status',
                'event_booking_logs.is_complementary',
                \DB::raw("IFNULL(creator_co.name, 'Zevo') AS created_by"),
                \DB::raw("assignee_co.name AS company_name"),
                'events.location_type',
                'events.fees',
                \DB::raw('IF(event_booking_logs.status = "3", meta->>"$.cancelled_by_name", null) AS cancelled_by_name'),
                \DB::raw('IF(event_booking_logs.status = "3", meta->>"$.cancel_reason", null) AS cancel_reason')
            )
            ->selectRaw("IF(event_booking_logs.status = '3', CONVERT_TZ(meta->>'$.cancelled_on', ?, ?), null) AS cancelled_on",[
                $appTimezone,$timezone
            ])
            ->selectRaw(" ? as timezone",[$timezone])
            ->selectRaw("CONVERT_TZ(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time), ?, ?) AS date_time",[
                $appTimezone,$timezone
            ])
            ->join('events', 'events.id', '=', 'event_booking_logs.event_id')
            ->join('event_companies', function ($join) {
                $join
                    ->on('event_companies.event_id', '=', 'events.id')
                    ->whereColumn('event_companies.company_id', 'event_booking_logs.company_id');
            })
            ->leftJoin('companies AS creator_co', 'creator_co.id', '=', 'events.company_id')
            ->join('companies AS assignee_co', 'assignee_co.id', '=', 'event_booking_logs.company_id')
            ->join('sub_categories', 'sub_categories.id', '=', 'events.subcategory_id')
            ->whereHas('event', function ($query) use ($role, $company) {
                $query
                    ->select('events.id')
                    ->where('events.status', 2);
                if ($role->group == 'zevo') {
                    $query->whereNull('events.company_id');
                } elseif ($role->group == 'company') {
                    $query->where('event_companies.company_id', $company->id);
                } elseif ($role->group == 'reseller') {
                    if ($company->is_reseller) {
                        $assigneeComapnies = Company::select('id')
                            ->where('parent_id', $company->id)
                            ->orWhere('id', $company->id)
                            ->get()->pluck('id')->toArray();
                        $query
                            ->whereIn('event_companies.company_id', $assigneeComapnies)
                            ->where(function ($where) use ($company) {
                                $where
                                    ->whereNull('events.company_id')
                                    ->orWhere('events.company_id', $company->id);
                            });
                    } elseif (!is_null($company->parent_id)) {
                        $query->where('event_companies.company_id', $company->id);
                    }
                }
            })
            ->when(($payload['name']), function ($when, $name) {
                $when->where('events.name', 'like', "%" . $name . "%");
            })
            ->when($payload['company'], function ($query, $company) {
                $query->where('event_booking_logs.company_id', $company);
            })
            ->when($payload['presenter'], function ($query, $presenter) {
                $query->where('event_booking_logs.presenter_user_id', $presenter);
            })
            ->when($payload['status'], function ($query, $status) {
                $query->where('event_booking_logs.status', $status);
            })
            ->when($payload['category'], function ($query, $category) {
                $query->where('events.subcategory_id', $category);
            })
            ->groupBy('event_booking_logs.id');

        if ($payload['complementary'] != "") {
            $events->where('event_booking_logs.is_complementary', $payload['complementary']);
        }

        if ((isset($payload['fromdate']) && !empty($payload['fromdate'] && strtotime($payload['fromdate']) !== false)) && (isset($payload['todate']) && !empty($payload['todate'] && strtotime($payload['todate']) !== false))) {
            $fromdate = Carbon::parse($payload['fromdate'], $timezone)->setTime(0, 0, 0, 0)->setTimezone($appTimezone)->toDateTimeString();
            $todate   = Carbon::parse($payload['todate'], $timezone)->setTime(23, 59, 59, 0)->setTimezone($appTimezone)->toDateTimeString();
            $events
                ->where(function ($where) use ($fromdate, $todate) {
                    $where
                        ->whereRaw("TIMESTAMP(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time)) BETWEEN ? AND ?", [$fromdate, $todate])
                        ->orWhereRaw("TIMESTAMP(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.end_time)) BETWEEN ? AND ?", [$fromdate, $todate]);
                });
        }

        if (isset($payload['order'])) {
            $column = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order  = $payload['order'][0]['dir'];
            $events->orderBy($column, $order);
        } else {
            $events->orderByDesc('event_booking_logs.updated_at');
        }

        $length = config('zevolifesettings.datatable.pagination.long');
        if (isset($payload['length'])) {
            $length = $payload['length'];
        }

        return [
            'total'  => count($events->get()),
            'record' => $events->offset($payload['start'])->limit($length)->get(),
        ];
    }

    /**
     * Get summary report of event booking
     *
     * @param array payload
     * @return dataTable
     */
    public function getSummaryReport($payload)
    {
        $list = $this->getSummaryReportRecords($payload);
        return DataTables::of($list['record'])
            ->skipPaging()
            ->addColumn('status', function ($record) {
                $allStatus = config('zevolifesettings.event-status-master');
                $status    = $allStatus[$record->status];
                return "<span class='{$status['class']}'>{$status['text']}</span>";
            })
            ->addColumn('billable', function ($record) {
                return $record->billable;
            })
            ->addColumn('actions', function ($record) {
                return view('admin.report.event.listaction', compact('record'))->render();
            })
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ])
            ->rawColumns(['billable', 'status', 'actions'])
            ->make(true);
    }

    /**
     * Get summary report records
     *
     * @param array $payload
     * @return array
     */
    public function getSummaryReportRecords($payload)
    {

        $user              = auth()->user();
        $company           = $user->company()->first();
        $creatorCoStmt     = $rsaWhereCond     = $searchWhere     = $statusWhere     = "";
        $assigneeComapnies = [];

        if (is_null($company)) {
            $creatorCoStmt = "AND `events`.`company_id` IS NULL";
        } elseif (!is_null($company) && $company->is_reseller) {
            $assigneeComapnies = Company::select('id')
                ->where('parent_id', $company->id)
                ->orWhere('id', $company->id)
                ->get()->pluck('id');
            $assigneeComapniesString = $assigneeComapnies->implode(',');
            $creatorCoStmt           = "AND (`events`.`company_id` IS NULL OR `events`.`company_id` = {$company->id}) AND `event_booking_logs`.`company_id` IN ({$assigneeComapniesString})";
            $rsaWhereCond            = "AND `companies`.`id` IN ({$assigneeComapniesString})";
        }

        if (isset($payload['company']) && !empty($payload['company']) && is_numeric($payload['company'])) {
            $searchWhere .= "AND `ebl`.`company_id` = {$payload['company']}";
        }

        if (isset($payload['status']) && !empty($payload['status']) && is_numeric($payload['status'])) {
            $statusWhere = "HAVING `status` = {$payload['status']}";
        }

        if (isset($payload['order']) && isset($payload['columns']) && in_array($payload['order'][0]['dir'], ['asc','desc']) && is_numeric($payload['columns'][$payload['order'][0]['column']]['data'])) {
            $column = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order  = $payload['order'][0]['dir'];
        } else {
            $column = '`records`.`max_updated_at`';
            $order  = 'DESC';
        }

        $companyWiseEventsQuery = "SELECT `records`.`company_id`,
            `records`.`company_name`,
            `records`.`total_events`,
            `records`.`booked_events`,
            `records`.`cancelled_events`,
            `records`.`billable`,
            (CASE
                WHEN (`records`.`booked_events` > 0) THEN 4
                ELSE 5
            END) AS `status`
            FROM (SELECT `ebl`.`company_id`,
                    `companies`.`name` AS company_name,
                    MAX(`ebl`.`updated_at`) AS max_updated_at,
                    (SELECT COUNT(`event_booking_logs`.`id`)
                        FROM `event_booking_logs`
                        INNER JOIN `events` ON (`events`.`id` = `event_booking_logs`.`event_id`)
                        WHERE `event_booking_logs`.`company_id` = `ebl`.`company_id` {$creatorCoStmt}
                    ) AS `total_events`,
                    (SELECT IFNULL(COUNT(`event_booking_logs`.`id`), 0)
                        FROM `event_booking_logs`
                        INNER JOIN `events` ON (`events`.`id` = `event_booking_logs`.`event_id`)
                        WHERE `event_booking_logs`.`company_id` = `ebl`.`company_id` AND `event_booking_logs`.`status` = '3' {$creatorCoStmt}
                    ) AS `cancelled_events`,
                    (SELECT IFNULL(COUNT(`event_booking_logs`.`id`), 0)
                        FROM `event_booking_logs`
                        INNER JOIN `events` ON (`events`.`id` = `event_booking_logs`.`event_id`)
                        WHERE `event_booking_logs`.`company_id` = `ebl`.`company_id` AND `event_booking_logs`.`status` = '4' {$creatorCoStmt}
                    ) AS `booked_events`,
                    (SELECT IFNULL(COUNT(`event_booking_logs`.`id`), 0)
                        FROM `event_booking_logs`
                        INNER JOIN `events` ON (`events`.`id` = `event_booking_logs`.`event_id`)
                        WHERE `event_booking_logs`.`company_id` = `ebl`.`company_id` AND `event_booking_logs`.`status` = '5' {$creatorCoStmt}
                    ) AS `completed_events`,
                    ((SELECT IFNULL(SUM(`events`.`fees`), 0)
                        FROM `event_booking_logs`
                        INNER JOIN `events` ON (`events`.`id` = `event_booking_logs`.`event_id`)
                        WHERE `event_booking_logs`.`company_id` = `ebl`.`company_id` AND `event_booking_logs`.`status` IN ('4', '5') {$creatorCoStmt}
                    ) - (SELECT IFNULL(SUM(`events`.`fees`), 0)
                        FROM `event_booking_logs`
                        INNER JOIN `events` ON (`events`.`id` = `event_booking_logs`.`event_id`)
                        WHERE `event_booking_logs`.`company_id` = `ebl`.`company_id` AND `event_booking_logs`.`status` = '3' {$creatorCoStmt}
                    )) AS `billable`
                FROM `event_booking_logs` AS `ebl`
                INNER JOIN `companies` ON `companies`.`id` = `ebl`.`company_id`
                WHERE 1 = 1 {$rsaWhereCond} {$searchWhere}
                GROUP BY `ebl`.`company_id`
                HAVING `total_events` > 0
            ) AS `records`
            {$statusWhere}
            ORDER BY {$column} {$order}";

        $length = config('zevolifesettings.datatable.pagination.long');
        $start  = 0;
        if (isset($payload['length']) && is_numeric($payload['length'])) {
            $length = $payload['length'];
        }
        if (isset($payload['start']) && is_numeric($payload['start'])) {
            $start = $payload['start'];
        }

        return [
            'total'  => count(\DB::select($companyWiseEventsQuery)),
            'record' => \DB::select($companyWiseEventsQuery . " LIMIT {$length} offset {$start}"),
        ];
    }

    /**
     * Get company wise booking report
     *
     * @param Company $company
     * @param array payload
     * @return dataTable
     */
    public function getBookingReportComapnyWise($company, $payload)
    {
        $appTimezone = config('app.timezone');
        $list        = $this->getBookingReportComapnyWiseRecords($company, $payload);
        $status      = config('zevolifesettings.event-status-master');
        return DataTables::of($list['record'])
            ->skipPaging()
            ->addColumn('end_time', function ($record) use ($appTimezone) {
                return Carbon::parse("{$record->booking_date} {$record->end_time}", $appTimezone)
                    ->toDateTimeString();
            })
            ->addColumn('date_time', function ($record) use ($appTimezone) {
                return Carbon::parse("{$record->booking_date} {$record->start_time}", $appTimezone)
                    ->toDateTimeString();
            })
            ->addColumn('participants', function ($record) {
                return $record->participants ?? 0;
            })
            ->addColumn('fees', function ($record) {
                return $record->fees;
            })
            ->addColumn('is_complementary', function ($record) {
                return ($record->is_complementary) ? "Yes" : "No";
            })
            ->addColumn('location_type', function ($record) {
                return config('zevolifesettings.event-location-type')[$record->location_type];
            })
            ->addColumn('status', function ($record) use ($status) {
                $recordStatus = $status[$record->status];
                return "<span class='{$recordStatus['class']}'>{$recordStatus['text']}</span>";
            })
            ->addColumn('actions', function ($record) {
                return view('admin.report.event.booking-report-listaction', compact('record'))->render();
            })
            ->addColumn('cancelled_on', function ($record) {
                if ($record->cancelled_on) {
                    return Carbon::parse("{$record->cancelled_on}")->format(config('zevolifesettings.date_format.default_datetime_24hours'));
                } else {
                    return '';
                }
            })
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ])
            ->rawColumns(['date_time', 'fees', 'status', 'actions'])
            ->make(true);
    }

    /**
     * Get company wise booking report
     *
     * @param Company $reqCompany
     * @param array $payload
     * @return array
     */
    public function getBookingReportComapnyWiseRecords($reqCompany, $payload)
    {
        $user        = auth()->user();
        $appTimezone = config('app.timezone');
        $timezone    = (!empty($user->timezone) ? $user->timezone : $appTimezone);
        $role        = getUserRole($user);
        $company     = $user->company()->first();
        $events      = $this
            ->select(
                'event_booking_logs.id',
                'event_booking_logs.event_id',
                'events.name AS event_name',
                \DB::raw('meta->>"$.presenter" AS presenter'),
                'event_booking_logs.booking_date',
                'event_booking_logs.start_time',
                'event_booking_logs.end_time',
                'event_booking_logs.is_complementary',
                'event_booking_logs.status',
                \DB::raw("IFNULL(companies.name, 'Zevo') AS created_by"),
                'events.location_type',
                'events.fees',
                \DB::raw('IF(event_booking_logs.status = "3", meta->>"$.cancelled_by_name", null) AS cancelled_by_name'),
                \DB::raw('IF(event_booking_logs.status = "3", meta->>"$.cancel_reason", null) AS cancel_reason')
            )
            ->selectRaw("IF(event_booking_logs.status = '3', CONVERT_TZ(meta->>'$.cancelled_on', ?, ?), null) AS cancelled_on",[
                $appTimezone,$timezone
            ])
            ->selectRaw("? as timezone",[$timezone])
            ->selectRaw("CONVERT_TZ(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time), '{$appTimezone}', '{$timezone}') AS date_time")
            ->withCount(['users AS participants' => function ($countQuery) {
                $countQuery->where('event_registered_users_logs.is_cancelled', 0);
            }])
            ->join('events', 'events.id', '=', 'event_booking_logs.event_id')
            ->join('event_companies', function ($join) {
                $join
                    ->on('event_companies.event_id', '=', 'events.id')
                    ->whereColumn('event_companies.company_id', 'event_booking_logs.company_id');
            })
            ->leftJoin('companies', 'companies.id', '=', 'events.company_id')
            ->where('event_booking_logs.company_id', $reqCompany->id)
            ->whereHas('event', function ($query) use ($role, $company) {
                $query
                    ->select('events.id')
                    ->where('events.status', 2);
                if ($role->group == 'zevo') {
                    $query->whereNull('events.company_id');
                } elseif ($role->group == 'reseller') {
                    if ($company->is_reseller) {
                        $assigneeComapnies = Company::select('id')
                            ->where('parent_id', $company->id)
                            ->orWhere('id', $company->id)
                            ->get()->pluck('id')->toArray();
                        $query
                            ->whereIn('event_companies.company_id', $assigneeComapnies)
                            ->where(function ($where) use ($company) {
                                $where
                                    ->whereNull('events.company_id')
                                    ->orWhere('events.company_id', $company->id);
                            });
                    } elseif (!is_null($company->parent_id)) {
                        $query->where('event_companies.company_id', $company->id);
                    }
                }
            })
            ->when($payload['presenter'], function ($query, $presenter) {
                $query->where('event_booking_logs.presenter_user_id', $presenter);
            })
            ->when($payload['status'], function ($query, $status) {
                $query->where('event_booking_logs.status', $status);
            })
            ->groupBy('event_booking_logs.id');

        if ($payload['complementary'] != "") {
            $events->where('event_booking_logs.is_complementary', $payload['complementary']);
        }

        if ((isset($payload['fromdate']) && !empty($payload['fromdate'] && strtotime($payload['fromdate']) !== false)) && (isset($payload['todate']) && !empty($payload['todate'] && strtotime($payload['todate']) !== false))) {
            $fromdate = Carbon::parse($payload['fromdate'], $timezone)->setTime(0, 0, 0, 0)->setTimezone($appTimezone)->toDateTimeString();
            $todate   = Carbon::parse($payload['todate'], $timezone)->setTime(23, 59, 59, 0)->setTimezone($appTimezone)->toDateTimeString();
            $events
                ->where(function ($where) use ($fromdate, $todate) {
                    $where
                        ->whereRaw("TIMESTAMP(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time)) BETWEEN ? AND ?", [$fromdate, $todate])
                        ->orWhereRaw("TIMESTAMP(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.end_time)) BETWEEN ? AND ?", [$fromdate, $todate]);
                });
        }

        if (isset($payload['order'])) {
            $column = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order  = $payload['order'][0]['dir'];
            $events->orderBy($column, $order);
        } else {
            $events->orderByDesc('event_booking_logs.updated_at');
        }

        return [
            'total'  => count($events->get()),
            'record' => $events->offset($payload['start'])->limit($payload['length'])->get(),
        ];
    }

    /**
     * To send Event CSAT(Feedback) notificaion to registered users after 12 hours of event get complete
     *
     * @return void
     */
    public function sendCsatNotificaion()
    {
        // get company details
        $company = $this->company()
            ->select('companies.id', 'companies.enable_event', 'companies.is_reseller', 'companies.parent_id')
            ->first();

        // validate send notification or not
        $sendNotifaction = (
            $company->is_reseller ||
            (!$company->is_reseller && !is_null($company->parent_id)) ||
            (!$company->is_reseller && is_null($company->parent_id) && $company->enable_event)
        );

        if (!$sendNotifaction) {
            return;
        }

        // get event details
        $event = $this->event()
            ->select('events.id', 'events.name', 'events.deep_link_uri', 'events.company_id', 'events.creator_id')
            ->first();

        // get registered users of event
        $registeredUsers = $this->users()
            ->select('users.id')
            ->where('event_registered_users_logs.is_cancelled', 0)
            ->get();

        if (!empty($registeredUsers)) {
            // Check company plan access
            $checkEventAccess = getCompanyPlanAccess([], 'event', $company);
            if ($checkEventAccess) {
                // send event CSAT(Feedback) notification to registered users
                dispatch(new SendEventPushNotificationJob($event, "csat", $registeredUsers, [
                    'company_id' => $company->id,
                    'booking_id' => $this->id,
                ]));
            }
        }

        // update `csat_at` field of booking log
        $this->update([
            'csat_at' => now(config('app.timezone')),
        ]);
    }

    /**
     * To get participated users of the event
     *
     * @param array payload
     * @return dataTable
     */
    public function getEventRegisteredUsers($payload)
    {
        $list        = $this->getEventRegisteredUsersRecords($payload);
        return DataTables::of($list['record'])
            ->skipPaging()
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ])
            ->rawColumns([])
            ->make(true);
    }

    /**
     * To get participated users of the event
     *
     * @param Company $reqCompany
     * @param array $payload
     * @return array
     */
    public function getEventRegisteredUsersRecords($payload)
    {
        $users       = $this
            ->users()
            ->select(
                'users.id',
                \DB::raw("CONCAT(users.first_name, ' ', users.last_name) as username"),
                'users.email',
                'event_registered_users_logs.created_at'
            )
            ->where('event_registered_users_logs.is_cancelled', false)
            ->when($payload['name'], function ($query, $name) {
                $query->whereRaw("CONCAT(users.first_name, ' ', users.last_name) like ?", "%{$name}%");
            })
            ->when($payload['email'], function ($query, $email) {
                $query->where('users.email', 'like', "%{$email}%");
            });

        if (isset($payload['order'])) {
            $column = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order  = $payload['order'][0]['dir'];
            $users->orderBy($column, $order);
        } else {
            $users->orderByDesc('event_registered_users_logs.id');
        }

        return [
            'total'  => count($users->get()),
            'record' => $users->offset($payload['start'])->limit($payload['length'])->get(),
        ];
    }

    /**
     * Get calender report of event records
     *
     * @param array payload
     * @return dataTable
     */
    public function getCalenderReport($payload)
    {
        $user        = auth()->user();
        $appTimezone = config('app.timezone');
        $timezone    = (!empty($user->timezone) ? $user->timezone : $appTimezone);
        $list        = $this->getCalenderReportRecords($payload);
        $collection  = collect($list);
        $status      = config('zevolifesettings.event-status-master');

        $collection->transform(function ($item) use ($appTimezone, $status, $timezone) {
            $expertise   = [];
            $statusKey   = [];
            $toolTipHtml = "";
            if (!is_null($item->presenter_user_id)) {
                $presenterUsers  = User::find($item->presenter_user_id);
                $image           = $presenterUsers->getMediaData('logo', ['w' => 320, 'h' => 320])['url'];
                $expertise       = HealthCoachExpertise::where('user_id', $item->presenter_user_id)->select('expertise_id')->get()->pluck('expertise_id')->toArray();
                $presenterUserId = $item->presenter_user_id;
                $presenterName   = $item->presenter;
            } else {
                $presenterName   = !empty($item->presenter) ? $item->presenter : null;
                $presenterUserId = 0;
                $image           = config('zevolifesettings.fallback_image_url.user.logo');
            }
            $startTime   = Carbon::parse("{$item->booking_date} {$item->start_time}", $appTimezone)->setTimezone($timezone)->format('h:i A');
            $endTime     = Carbon::parse("{$item->booking_date} {$item->end_time}", $appTimezone)->setTimezone($timezone)->format('h:i A');
            $eventStatus = $status[$item->status];
            array_push($statusKey, $item->status);
            $url = route('admin.reports.calendar-booking-details', $item->id);
            $toolTipHtml .= '<span><strong>Presenter Name:</strong></span> ' . $presenterName . '<br/>';
            $toolTipHtml .= '<span><strong>Start Time:</strong></span> ' . $startTime . '<br/>';
            $toolTipHtml .= '<span><strong>End Time:</strong></span> ' . $endTime . '<br/>';
            $toolTipHtml .= '<span><strong>Status:</strong></span>' . $eventStatus['text'] . '<br/>';
            $toolTipHtml .= '<span><strong>Expertise:</strong></span>' . $item['subcategory_name'] . '<br/>';
            $toolTipHtml .= '<span></span><a href="' . $url . '" target="_blank"> View more </a>';
           
            return [
                'title'         => '<img src="' . $image . '" style="width: 24px; height: 24px;margin-right:5px;" /> <small>' . $item->event_name . '</small>',
                'start'         => Carbon::parse("{$item->booking_date} {$item->start_time}", $appTimezone)->setTimezone($timezone)->toDateTimeString(),
                'end'           => Carbon::parse("{$item->booking_date} {$item->end_time}", $appTimezone)->setTimezone($timezone)->toDateTimeString(),
                'presenterName' => $item->presenter,
                'eventName'     => $item->event_name,
                'presenterId'   => $presenterUserId,
                'toolTipHtml'   => $toolTipHtml,
                'status'        => $item->status,
                'expertise'     => $expertise,
                'expertiseName' => $item['subcategory_name'],
                'statuskey'     => $statusKey,
            ];
        });

        return $collection->all();
    }

    /**
     * Get calender report of event records
     *
     * @param array payload
     * @return dataTable
     */
    public function getCalenderReportRecords($payload)
    {
        $user        = auth()->user();
        $appTimezone = config('app.timezone');
        $timezone    = (!empty($user->timezone) ? $user->timezone : $appTimezone);
        $role        = getUserRole($user);
        $company     = $user->company()->first();
        $events      = $this
            ->select(
                'event_booking_logs.id',
                'event_booking_logs.event_id',
                'events.name AS event_name',
                'sub_categories.name AS subcategory_name',
                'event_booking_logs.presenter_user_id',
                \DB::raw('meta->>"$.presenter" AS presenter'),
                'event_booking_logs.booking_date',
                'event_booking_logs.start_time',
                'event_booking_logs.end_time',
                'event_booking_logs.status',
                'event_booking_logs.is_complementary',
                \DB::raw("IFNULL(creator_co.name, 'Zevo') AS created_by"),
                \DB::raw("assignee_co.name AS company_name"),
                'events.location_type',
                'events.fees',
                \DB::raw("IFNULL(event_booking_logs.presenter_user_id, event_booking_logs.id) AS presenteruserId")
            )
            ->join('events', 'events.id', '=', 'event_booking_logs.event_id')
            ->join('event_companies', function ($join) {
                $join
                    ->on('event_companies.event_id', '=', 'events.id')
                    ->whereColumn('event_companies.company_id', 'event_booking_logs.company_id');
            })
            ->leftJoin('companies AS creator_co', 'creator_co.id', '=', 'events.company_id')
            ->join('companies AS assignee_co', 'assignee_co.id', '=', 'event_booking_logs.company_id')
            ->join('sub_categories', 'sub_categories.id', '=', 'events.subcategory_id')
            ->whereHas('event', function ($query) use ($role, $company) {
                $query
                    ->select('events.id')
                    ->where('events.status', 2);
                if ($role->group == 'zevo') {
                    $query->whereNull('events.company_id');
                } elseif ($role->group == 'company') {
                    $query->where('event_companies.company_id', $company->id);
                } elseif ($role->group == 'reseller') {
                    if ($company->is_reseller) {
                        $assigneeComapnies = Company::select('id')
                            ->where('parent_id', $company->id)
                            ->orWhere('id', $company->id)
                            ->get()->pluck('id')->toArray();
                        $query
                            ->whereIn('event_companies.company_id', $assigneeComapnies)
                            ->where(function ($where) use ($company) {
                                $where
                                    ->whereNull('events.company_id')
                                    ->orWhere('events.company_id', $company->id);
                            });
                    } elseif (!is_null($company->parent_id)) {
                        $query->where('event_companies.company_id', $company->id);
                    }
                }
            });
        if ((isset($payload['start']) && !empty($payload['start'] && strtotime($payload['start']) !== false)) && (isset($payload['end']) && !empty($payload['end'] && strtotime($payload['end']) !== false))) {
            $fromdate = Carbon::parse($payload['start'], $timezone)->setTime(0, 0, 0, 0)->setTimezone($appTimezone)->toDateTimeString();
            $todate   = Carbon::parse($payload['end'], $timezone)->setTime(23, 59, 59, 0)->setTimezone($appTimezone)->toDateTimeString();
            $events
                ->where(function ($where) use ($fromdate, $todate) {
                    $where
                        ->whereRaw("TIMESTAMP(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time)) BETWEEN ? AND ?", [$fromdate, $todate])
                        ->orWhereRaw("TIMESTAMP(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.end_time)) BETWEEN ? AND ?", [$fromdate, $todate]);
                });
        }

        $events = $events->get();

        return $events;
    }

    /**
     * To update an booked event for company
     *
     * @param EventBookingLogs $bookingLog
     * @param array $payload
     * @return boolean
     */
    public function updateBookedEntity($payload)
    {
        $loginUser   = auth()->user();
        $appTimezone = config('app.timezone');
        $data        = [
            'description'       => $payload['description'],
            'notes'             => $payload['notes'],
            'email_notes'       => $payload['email_notes'],
            'registration_date' => Carbon::parse($payload['registrationdate'], $loginUser->timezone)->setTimezone($appTimezone)->toDateTimeString(),
            'is_complementary'  => (!empty($payload['is_complementary']) ? true : false),
            'add_to_story'      => (!empty($payload['add_to_story']) ? true : false),
        ];
        $bookingRecord = $this->update($data);

        $customEmailData = [];
        if (!empty($payload['email'])) {
            foreach ($payload['email'] as $emailIndex => $emailValue) {
                $customEmailData[$emailIndex]['email'] = $emailValue;
            }

            $oldEmails = EventBookingEmails::
                where('event_booking_log_id', $this->id)
                ->count();

            if ($oldEmails >= 0) {
                EventBookingEmails::where('event_booking_log_id', $this->id)
                    ->delete();
            }
            foreach ($customEmailData as $customEmail) {
                EventBookingEmails::create([
                    'email'                => $customEmail['email'],
                    'event_booking_log_id' => $this->id,
                ]);
            }
        }
        return $bookingRecord;
    }

    public function exportBookingReportDataEntity($payload)
    {
        $user     = auth()->user();
        return \dispatch(new ExportBookingReportDetailJob($payload, $user));
    }

    public function exportBookingReportCompanyWiseDataEntity($payload, $company)
    {
        $user     = auth()->user();
        return \dispatch(new ExportBookingReportCompanyWiseJob($payload, $user, $company));
    }

    /**
     * Export Bookings
     * @param array $payload
     * @return json
     */
    public function exportBookings($payload)
    {
        $user     = auth()->user();
        return \dispatch(new ExportBookingsJob($payload, $user));
    }
}
