<?php

namespace App\Http\Controllers\Admin;

use App\Events\SendSessionBookedEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CronofyAvailabilityRequest;
use App\Jobs\SendGroupSessionPushNotification;
use App\Jobs\AdminAlertJob;
use App\Models\Company;
use App\Models\AdminAlert;
use App\Models\CronofyCalendar;
use App\Models\CronofySchedule;
use App\Models\User;
use App\Models\WsClientNote;
use App\Jobs\SendConsentPushNotification;
use App\Repositories\CronofyRepository;
use Breadcrumbs;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class CronofyController
 *
 * @package App\Http\Controllers\Admin
 */
class CronofyController extends Controller
{
    /**
     * variable to store the Cronofy calendar model object
     * @var CronofyCalendar $cronofyCalendar
     */
    private $cronofyCalendar;

    /**
     * variable to store the Cronofy Repository Repository object
     * @var CronofyRepository $cronofyRepository
     */
    private $cronofyRepository;

    /**
     * variable to store the Cronofy schedule model object
     * @var CronofySchedule $cronofySchedule
     */
    private $cronofySchedule;

    /**
     * variable to store the Ws client note schedule model object
     * @var WsClientNote $wsClientNote
     */
    private $wsClientNote;

    /**
     * contructor to initialize model object
     */
    public function __construct(CronofyRepository $cronofyRepository, CronofyCalendar $cronofyCalendar, CronofySchedule $cronofySchedule, WsClientNote $wsClientNote)
    {
        $this->cronofyRepository = $cronofyRepository;
        $this->cronofyCalendar   = $cronofyCalendar;
        $this->cronofySchedule   = $cronofySchedule;
        $this->wsClientNote      = $wsClientNote;
        $this->bindBreadcrumbs();
    }

    /**
     * bind breadcrumbs of course module
     */
    private function bindBreadcrumbs()
    {
        Breadcrumbs::for('cronofy.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Authenticate');
        });
        Breadcrumbs::for('cronofy.availability', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Availability');
        });
        Breadcrumbs::for('cronofy.clientlist.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Clients');
        });
        Breadcrumbs::for('cronofy.sessionlist.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Sessions');
        });
        Breadcrumbs::for('cronofy.clientlist.details', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('Cronofy.client_list.title.index'), route('admin.cronofy.clientlist.index'));
            $trail->push(trans('Cronofy.client_list.title.details'));
        });
        Breadcrumbs::for('cronofy.sessions.details', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('Cronofy.session_list.title.manage'), route('admin.cronofy.sessions.index'));
            $trail->push(trans('Cronofy.session_list.title.details'));
        });
    }

    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        if (!access()->allow('authenticate')) {
            abort(403);
        }
        try {
            $user                   = Auth::user();
            $data                   = array();
            $data['ga_title']       = trans('page_title.cronofy.authenticate');
            $data['calendarCount']  = $user->cronofyCalendar()->count();
            $data['wsDetails']      = $user->wsuser()->first();
            $data['wcDetails']      = $user->healthCoachUser()->first();

            return \view('admin.cronofy.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function authenticate(Request $request)
    {
        if (!access()->allow('authenticate')) {
            abort(403);
        }
        try {
            $result = $this->cronofyRepository->authenticate();
            return redirect($result);
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function callback(Request $request)
    {
        if (!access()->allow('authenticate')) {
            abort(403);
        }
        try {
            $result = $this->cronofyRepository->callback($request->code);
            return \view('admin.cronofy.callback', $result);
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function getCalendar(Request $request)
    {
        if (!access()->allow('authenticate')) {
            return response()->json([
                'message' => trans('cronofy.message.unauthorized_access'),
            ], 422);
        }
        try {
            return $this->cronofyCalendar->getTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function linkCalendar(Request $request)
    {
        if (!access()->allow('authenticate')) {
            abort(403);
        }
        try {
            $result = $this->cronofyRepository->linkCalendar();
            return redirect($result);
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function unlinkCalendar(Request $request)
    {
        if (!access()->allow('authenticate')) {
            abort(403);
        }
        try {
            return $this->cronofyRepository->unlinkCalendar($request->profileId);
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * @param Request $request
     * @param CronofyCalendar $cronofyCalendar
     * @return View
     */
    public function primaryCalendar(Request $request, CronofyCalendar $cronofyCalendar)
    {
        if (!access()->allow('authenticate')) {
            return response()->json([
                'message' => trans('cronofy.message.unauthorized_access'),
            ], 422);
        }
        try {
            $user = auth()->user();
            CronofyCalendar::where('user_id', $user->id)->update(['primary' => false]);
            $primaryResponse = $cronofyCalendar->update(['primary' => true]);

            if ($primaryResponse) {
                return array('primary' => 'true');
            }
            return array('primary' => 'false');
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function availability(Request $request)
    {
        if (!access()->allow('availability')) {
            abort(403);
        }
        try {
            $user               = Auth::user();
            $role               = getUserRole($user);
            if ($role->slug == 'wellbeing_specialist') {
                $wsDetails          = $user->wsuser()->first();
            }

            $data                         = array();
            $data['userId']               = $user->id;
            $data['ga_title']             = trans('page_title.cronofy.availability');
            $data['calendarCount']        = $user->cronofyCalendar()->count();
            $data['hc_availability_days'] = config('zevolifesettings.hc_availability_days');
            $data['wsDetails']            = $user->wsuser()->first();
            $data['wcDetails']            = $user->healthCoachUser()->first();
            $data['responsibilities']     = $wsDetails->responsibilities;
            $data['userSlots']            = [];
            $data['presenterSlots']       = [];

            $userSlots    = $user->healthCocahSlots();
            $daywiseSlots = [];
            if ($userSlots->count() > 0) {
                foreach ($userSlots->get() as $slots) {
                    $daywiseSlots[$slots->day][] = [
                        'id'         => $slots->id,
                        'start_time' => Carbon::createFromFormat('H:i:s', $slots->start_time, $user->timezone),
                        'end_time'   => Carbon::createFromFormat('H:i:s', $slots->end_time, $user->timezone),
                    ];
                }
            }
            $data['userSlots'] = $daywiseSlots;

            $presenterSlots        = $user->eventPresenterSlots();
            $daywisePresenterSlots = [];
            if ($presenterSlots->count() > 0) {
                foreach ($presenterSlots->get() as $slots) {
                    $daywisePresenterSlots[$slots->day][] = [
                        'id'         => $slots->id,
                        'start_time' => Carbon::createFromFormat('H:i:s', $slots->start_time, $user->timezone),
                        'end_time'   => Carbon::createFromFormat('H:i:s', $slots->end_time, $user->timezone),
                    ];
                }
            }
            $data['presenterSlots'] = $daywisePresenterSlots;
           
            return \view('admin.cronofy.availability', $data);
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * @param Request $request
     * @return array
     */
    public function storeAvailability(CronofyAvailabilityRequest $request, User $user)
    {
        if (!access()->allow('availability')) {
            return response()->json([
                'message' => trans('cronofy.message.unauthorized_access'),
            ], 422);
        }
        try {
            $user     = Auth::user();
            $role     = getUserRole($user);
            if ($role->slug == 'wellbeing_specialist') {
                $wsDetails = $user->wsuser()->first();
            }


            // 1:1 Presenter and both
            if(in_array($wsDetails->responsibilities, [1, 3])) {
                $response = $user->updateSlot($request->all());
            }
            
            // Event Presenter and both
            if(in_array($wsDetails->responsibilities, [2, 3])) {
                $response = $user->updatePresenterSlot($request->all());
            }
            
            if ($role->slug == 'wellbeing_specialist') {
                $user->wsuser()->update([
                    'is_availability' => true,
                ]);
            } else if ($role->slug == 'health_coach') {
                $user->healthCoachUser()->update([
                    'is_availability' => true,
                ]);
            }

            return $response;
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * @param Request $request
     * @return array
     */
    public function callbackScheduling(Request $request)
    {
        try {
            $eventDetails       = $request->event;
            $eventId            = $eventDetails['event_id'];
            $bookingTimeZone    = $eventDetails['start']['tzid'];
            $startTime          = date("Y-m-d H:i:s", strtotime($eventDetails['start']['time']));
            $endTime            = date("Y-m-d H:i:s", strtotime($eventDetails['end']['time']));
            $nowInUTC           = now(config('app.timezone'))->todatetimeString();
            $newScheduleDetails = CronofySchedule::where('event_id', $eventId)->select('id', 'created_by', 'user_id', 'company_id', 'ws_id', 'name', 'location', 'is_group', 'meta')->first();
            $utcNow             = now(config('app.timezone'))->todatetimeString();

            $meta = [];
            if (!empty($newScheduleDetails)) {
                $meta                = $newScheduleDetails->meta;
                $lastScheduleDetails = CronofySchedule::where('user_id', $newScheduleDetails->user_id)
                    ->where('ws_id', $newScheduleDetails->ws_id)
                    ->where('end_time', '>=', $nowInUTC)
                    ->where('is_group', $newScheduleDetails->is_group)
                    ->where('status', 'booked')
                    ->orderBy('id', 'DESC')
                    ->first();
                $isRescheduled = false;
                if (!empty($lastScheduleDetails) && !$newScheduleDetails->is_group) {
                    $lastScheduleDetails->update([
                        'status'       => 'rescheduled',
                        'cancelled_at' => $nowInUTC,
                        'updated_at'   => $nowInUTC,
                    ]);
                    $isRescheduled = true;
                    $meta          = $lastScheduleDetails->meta;
                }
                $nowInUTC        = now(config('app.timezone'))->todatetimeString();
                $scheduleDetails = CronofySchedule::where('user_id', $newScheduleDetails->user_id)
                    ->where('ws_id', $newScheduleDetails->ws_id)
                    ->where('event_id', $eventId)
                    ->where('status', 'open')
                    ->orderBy('id', 'DESC')
                    ->first();

                //Send session booked email to User
                if (!empty($newScheduleDetails->user)) {
                    $company                = $newScheduleDetails->user->company->first();
                    $companyDigitalTherapy  = $company->digitalTherapy()->first();
                    $userTimeZone           = $newScheduleDetails->user->timezone;
                } else {
                    $company                = Company::where('id', $newScheduleDetails->company_id)->first();
                    $companyDigitalTherapy  = $company->digitalTherapy()->first();
                    $userTimeZone           = $newScheduleDetails->wellbeingSpecialist->timezone;
                }
                $eventDate = Carbon::parse("{$startTime}", config('app.timezone'))->setTimezone($userTimeZone)->format('M d, Y');
                $eventTime = Carbon::parse("{$startTime}", config('app.timezone'))->setTimezone($userTimeZone)->format('h:i A');
                $duration    = Carbon::parse($endTime)->diffInMinutes($startTime);
                $appName     = config('app.name');
                $inviteTitle = trans('Cronofy.ical.title', [
                    'user_name' => $newScheduleDetails->user->full_name,
                    'wbs_name'  => $newScheduleDetails->wellbeingSpecialist->full_name,
                ]);
                $uid         = (!empty($meta) ? $meta->uid : date('Ymd') . 'T' . date('His') . '-' . rand() . '@zevo.app');
                $meta        = [
                    "wellbeing_specialist" => $newScheduleDetails->ws_id,
                    "timezone"             => $userTimeZone,
                    "uid"                  => $uid,
                ];

                $scheduleDetails->update([
                    'start_time' => $startTime,
                    'end_time'   => $endTime,
                    'timezone'   => ((isset($bookingTimeZone) && !empty($bookingTimeZone)) ? $bookingTimeZone : $userTimeZone),
                    'meta'       => $meta,
                    'status'     => 'booked',
                    'updated_at' => $nowInUTC,
                ]);

                if (!$newScheduleDetails->is_group) {
                    $sequence    = 0;
                    $sessionData = [
                        'company'       => (!empty($company) ? $company->id : null),
                        'email'         => $newScheduleDetails->user->email,
                        'userFirstName' => $newScheduleDetails->user->first_name,
                        'userName'      => $newScheduleDetails->user->full_name,
                        'wsFirstName'   => $newScheduleDetails->wellbeingSpecialist->first_name,
                        'wsName'        => $newScheduleDetails->wellbeingSpecialist->full_name,
                        'serviceName'   => $newScheduleDetails->name,
                        'eventDate'     => $eventDate,
                        'eventTime'     => $eventTime,
                        'duration'      => $duration,
                        'location'      => $newScheduleDetails->location,
                        'to'            => 'user',
                        'isGroup'       => $newScheduleDetails->is_group,
                        'isRescheduled' => $isRescheduled,
                        'isOnline'      => (!empty($companyDigitalTherapy) && $companyDigitalTherapy->dt_is_online),
                        'iCal'          => generateiCal([
                            'uid'         => $uid,
                            'appName'     => $appName,
                            'inviteTitle' => $inviteTitle,
                            'description' => trans('Cronofy.ical.description', [
                                'service_name' => $newScheduleDetails->name,
                                'wbs_name'     => $newScheduleDetails->wellbeingSpecialist->full_name,
                                'session_date' => $eventDate,
                                'session_time' => $eventTime,
                                'whereby_link' => $newScheduleDetails->location,
                            ]),
                            'timezone'    => $userTimeZone,
                            'today'       => Carbon::parse($utcNow)->format('Ymd\THis\Z'),
                            'startTime'   => Carbon::parse($startTime)->format('Ymd\THis\Z'),
                            'endTime'     => Carbon::parse($endTime)->format('Ymd\THis\Z'),
                            'orgName'     => $newScheduleDetails->wellbeingSpecialist->full_name,
                            'orgEamil'    => $newScheduleDetails->wellbeingSpecialist->email,
                            'userEmail'   => $newScheduleDetails->user->email,
                            'sequence'    => $sequence,
                        ]),
                    ];
                    event(new SendSessionBookedEvent($sessionData));
                } else {
                    $notificationUser = User::select('users.*', 'user_notification_settings.flag AS notification_flag')
                        ->leftJoin('user_notification_settings', function ($join) {
                            $join->on('user_notification_settings.user_id', '=', 'users.id')
                                ->where('user_notification_settings.flag', '=', 1)
                                ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['digital-therapy', 'all']);
                        })
                        ->whereRaw(\DB::raw('users.id IN ( SELECT user_id FROM `session_group_users` WHERE session_id = ? )'),[
                            $newScheduleDetails->id
                        ])
                        ->where('is_blocked', false)
                        ->groupBy('users.id')
                        ->get()
                        ->toArray();

                    // dispatch job to send push notification to all user when group session created
                    \dispatch(new SendGroupSessionPushNotification($newScheduleDetails, "group-session-invite", $notificationUser, ''));
                }

                //Send session booked email to Ws
                $user      = User::find($newScheduleDetails->created_by);
                $wsDetails = User::find($newScheduleDetails->ws_id);
                $wsEventDate = Carbon::parse("{$startTime}", config('app.timezone'))->setTimezone($wsDetails->timezone)->format('M d, Y');
                $wsEventTime = Carbon::parse("{$startTime}", config('app.timezone'))->setTimezone($wsDetails->timezone)->format('h:i A');
                
                $role = getUserRole($user);
                if (!$newScheduleDetails->is_group) {
                    $sendTo = 'user';
                } elseif ($newScheduleDetails->is_group && $role->group == 'company') {
                    $sendTo = 'zca';
                } else {
                    $sendTo = null;
                }
                $sessionData = [
                    'company'       => (!empty($company) ? $company->id : null),
                    'email'         => $newScheduleDetails->wellbeingSpecialist->email,
                    'userFirstName' => (!empty($newScheduleDetails) && !empty($newScheduleDetails->user) && isset($newScheduleDetails->user->first_name) ? $newScheduleDetails->user->first_name : null),
                    'userName'      => (!empty($newScheduleDetails) && !empty($newScheduleDetails->user) && isset($newScheduleDetails->user->full_name) ? $newScheduleDetails->user->full_name : null),
                    'wsFirstName'   => $newScheduleDetails->wellbeingSpecialist->first_name,
                    'wsName'        => $newScheduleDetails->wellbeingSpecialist->full_name,
                    'serviceName'   => $newScheduleDetails->name,
                    'eventDate'     => $wsEventDate,
                    'eventTime'     => $wsEventTime,
                    'duration'      => $duration,
                    'companyName'   => (!empty($company) ? $company->name : null),
                    'location'      => $newScheduleDetails->location,
                    'isGroup'       => $newScheduleDetails->is_group,
                    'sessionId'     => $newScheduleDetails->id,
                    'to'            => ((!$newScheduleDetails->is_group) ? 'wellbeing_specialist' : 'zca'),
                    'isRescheduled' => $isRescheduled,
                    'isOnline'      => (!empty($companyDigitalTherapy) && $companyDigitalTherapy->dt_is_online),
                ];

                if (!empty($sendTo)) {
                    event(new SendSessionBookedEvent($sessionData));
                }

                //Send Consent form to user when user is fist time booking session
                if(!$isRescheduled){
                    $notificationUserForConsent = User::select('users.*', 'user_notification_settings.flag AS notification_flag')
                    ->leftJoin('user_notification_settings', function ($join) {
                        $join->on('user_notification_settings.user_id', '=', 'users.id')
                            ->where('user_notification_settings.flag', '=', 1)
                            ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['digital-therapy', 'all']);
                    })
                    ->where('user_id', $newScheduleDetails->user->id)
                    ->where('is_blocked', false)
                    ->first();
                    
                    // dispatch job to send push notification to all user when group session created
                    \dispatch(new SendConsentPushNotification($newScheduleDetails, "consent-form-receive", $notificationUserForConsent, 'api'));
                }
            }
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * @param Request $request
     * @return array
     */
    public function callbackRescheduling(Request $request)
    {
        try {
            $eventDetails       = $request->event;
            $eventId            = $eventDetails['event_id'];
            $bookingTimeZone    = $eventDetails['start']['tzid'];
            $startTime          = date("Y-m-d H:i:s", strtotime($eventDetails['start']['time']));
            $endTime            = date("Y-m-d H:i:s", strtotime($eventDetails['end']['time']));
            $nowInUTC           = now(config('app.timezone'))->todatetimeString();
            $newScheduleDetails = CronofySchedule::where('event_id', $eventId)->select('id', 'created_by', 'user_id', 'company_id', 'ws_id', 'name', 'location', 'is_group', 'meta')->first();
            $utcNow             = now(config('app.timezone'))->todatetimeString();
            $meta               = [];
            if (!empty($newScheduleDetails)) {
                
                if (!empty($newScheduleDetails->user)) {
                    $company                = $newScheduleDetails->user->company->first();
                    $companyDigitalTherapy  = $company->digitalTherapy()->first();
                    $userTimeZone           = $newScheduleDetails->user->timezone;
                } else {
                    $company                = Company::where('id', $newScheduleDetails->company_id)->first();
                    $companyDigitalTherapy  = $company->digitalTherapy()->first();
                    $userTimeZone           = $newScheduleDetails->wellbeingSpecialist->timezone;
                }
                $eventDate = Carbon::parse("{$startTime}", config('app.timezone'))->setTimezone($userTimeZone)->format('M d, Y');
                $eventTime = Carbon::parse("{$startTime}", config('app.timezone'))->setTimezone($userTimeZone)->format('h:i A');

                $lastScheduleDetails = CronofySchedule::where('user_id', $newScheduleDetails->user_id)
                    ->where('ws_id', $newScheduleDetails->ws_id)
                    ->where('end_time', '>=', $nowInUTC)
                    ->where('is_group', $newScheduleDetails->is_group)
                    ->where('status', 'booked')
                    ->orderBy('id', 'DESC')
                    ->first();
                $meta = $lastScheduleDetails->meta;
                $isRescheduled = false;
                $sequence = 0;
                if (!empty($lastScheduleDetails)) {
                    $lastScheduleDetails->update([
                        'status'       => 'rescheduled',
                        'cancelled_at' => $nowInUTC,
                        'updated_at'   => $nowInUTC,
                    ]);
                    $isRescheduled = true;
                }
                $sequenceLog = $newScheduleDetails->inviteSequence()->select('users.id')->where('user_id', $newScheduleDetails->user_id)->first();

                if (is_null($sequenceLog)) {
                    // record not exist adding
                    $newScheduleDetails->inviteSequence()->attach([$newScheduleDetails->user_id]);
                    $sequence = 0;
                } else {
                    // record exist updating sequence
                    $sequence = ($sequenceLog->pivot->sequence + 1);
                    $sequenceLog->pivot->update([
                        'sequence' => $sequence,
                    ]);
                }
                $nowInUTC        = now(config('app.timezone'))->todatetimeString();
                $scheduleDetails = CronofySchedule::where('user_id', $newScheduleDetails->user_id)
                    ->where('ws_id', $newScheduleDetails->ws_id)
                    ->where('event_id', $eventId)
                    ->where('status', 'open')
                    ->orderBy('id', 'DESC')
                    ->first();

                $uid  = (!empty($meta) ? $meta->uid : date('Ymd') . 'T' . date('His') . '-' . rand() . '@zevo.app');
                $meta = [
                    "wellbeing_specialist" => $newScheduleDetails->ws_id,
                    "timezone"             => $userTimeZone,
                    "uid"                  => $uid,
                ];
                $scheduleDetails->update([
                    'start_time' => $startTime,
                    'end_time'   => $endTime,
                    'timezone'   => ((isset($bookingTimeZone) && !empty($bookingTimeZone)) ? $bookingTimeZone : $userTimeZone),
                    'meta'       => $meta,
                    'status'     => 'booked',
                    'updated_at' => $nowInUTC,
                ]);

                //Send session rescheduled email to user
                $duration    = Carbon::parse($endTime)->diffInMinutes($startTime);
                $uid         = date('Ymd') . 'T' . date('His') . '-' . rand() . '@zevo.app';
                $appName     = config('app.name');
                $inviteTitle = trans('Cronofy.ical.title', [
                    'user_name' => $newScheduleDetails->user->full_name,
                    'wbs_name'  => $newScheduleDetails->wellbeingSpecialist->full_name,
                ]);
                $user        = User::find($newScheduleDetails->created_by);
                $role        = getUserRole($user);

                if (!$newScheduleDetails->is_group) {
                    $sendTo = 'user';
                } elseif ($newScheduleDetails->is_group && $role->group == 'company') {
                    $sendTo = 'zca';
                } else {
                    $sendTo = null;
                }
                $sessionData = [
                    'company'       => (!empty($company) ? $company->id : null),
                    'email'         => ((!$newScheduleDetails->is_group) ? $newScheduleDetails->user->email : $newScheduleDetails->wellbeingSpecialist->email),
                    'userFirstName' => (!empty($newScheduleDetails) && !empty($newScheduleDetails->user) && isset($newScheduleDetails->user->first_name) ? $newScheduleDetails->user->first_name : null),
                    'userName'      => (!empty($newScheduleDetails) && !empty($newScheduleDetails->user) && isset($newScheduleDetails->user->full_name) ? $newScheduleDetails->user->full_name : null),
                    'wsFirstName'   => $newScheduleDetails->wellbeingSpecialist->first_name,
                    'wsName'        => $newScheduleDetails->wellbeingSpecialist->full_name,
                    'serviceName'   => $newScheduleDetails->name,
                    'eventDate'     => $eventDate,
                    'eventTime'     => $eventTime,
                    'duration'      => $duration,
                    'location'      => $newScheduleDetails->location,
                    'to'            => ((!$newScheduleDetails->is_group) ? 'user' : 'zca'),
                    'sessionId'     => $newScheduleDetails->id,
                    'isGroup'       => $newScheduleDetails->is_group,
                    'companyName'   => (!empty($company) ? $company->name : null),
                    'isRescheduled' => $isRescheduled,
                    'isOnline'      => (!empty($companyDigitalTherapy) && $companyDigitalTherapy->dt_is_online),
                    'iCal'          => generateiCal([
                        'uid'         => $uid,
                        'appName'     => $appName,
                        'inviteTitle' => $inviteTitle,
                        'description' => trans('Cronofy.ical.description', [
                            'service_name' => $newScheduleDetails->name,
                            'wbs_name'     => $newScheduleDetails->wellbeingSpecialist->full_name,
                            'session_date' => $eventDate,
                            'session_time' => $eventTime,
                            'whereby_link' => $newScheduleDetails->location,
                        ]),
                        'timezone'    => $userTimeZone,
                        'today'       => Carbon::parse($utcNow, config('app.timezone'))->setTimezone($userTimeZone)->format('Ymd\THis\Z'),
                        'startTime'   => Carbon::parse($startTime, config('app.timezone'))->setTimezone($userTimeZone)->format('Ymd\THis\Z'),
                        'endTime'     => Carbon::parse($endTime, config('app.timezone'))->setTimezone($userTimeZone)->format('Ymd\THis\Z'),
                        'orgName'     => $newScheduleDetails->wellbeingSpecialist->full_name,
                        'orgEamil'    => (!empty($newScheduleDetails) && !empty($newScheduleDetails->wellbeingSpecialist) && isset($newScheduleDetails->wellbeingSpecialist->email) ? $newScheduleDetails->wellbeingSpecialist->email : ''),
                        'userEmail'   => ((!$newScheduleDetails->is_group) ? $newScheduleDetails->user->email : $newScheduleDetails->wellbeingSpecialist->email),
                        'sequence'    => $sequence,
                    ]),
                ];
                if (!empty($sendTo)) {
                    event(new SendSessionBookedEvent($sessionData));
                }

                if ($newScheduleDetails->is_group) {
                    $notificationUser = User::select('users.*', 'user_notification_settings.flag AS notification_flag')
                        ->leftJoin('user_notification_settings', function ($join) {
                            $join->on('user_notification_settings.user_id', '=', 'users.id')
                                ->where('user_notification_settings.flag', '=', 1)
                                ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['digital-therapy', 'all']);
                        })
                        ->whereRaw(\DB::raw('users.id IN ( SELECT user_id FROM `session_group_users` WHERE session_id = ? )'),[
                            $newScheduleDetails->id
                        ])
                        ->where('is_blocked', false)
                        ->groupBy('users.id')
                        ->get()
                        ->toArray();

                    // dispatch job to send push notification to all user when group session created
                    \dispatch(new SendGroupSessionPushNotification($newScheduleDetails, "group-session-reschedule", $notificationUser, ''));
                }
            }
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * @param Request $request
     * @return array
     */
    public function updateDashboad(Request $request)
    {
        try {
            $user = Auth::user();
            $role = getUserRole($user);

            if ($role->slug == 'wellbeing_specialist') {
                $alertUsers = AdminAlert::leftJoin('admin_alert_users', 'admin_alert_users.alert_id', '=', 'admin_alerts.id')
                ->select('admin_alert_users.user_email','admin_alert_users.user_name')->where('admin_alerts.title','Wellbeing Specialist Profile Completion')->pluck('user_email','user_name')->toArray();
                
                if (!empty($alertUsers)) {
                    foreach ($alertUsers as $alertName => $alertEmail) {
                        $data = [
                            'email'        => $user->email,
                            'name'         => $user->full_name,
                            'alertEmails'  => $alertEmail,
                            'alertName'    => $alertName,
                            'ws_name'      => $user->full_name,
                            'ws_email'     => $user->email,
                            'subject'      => config('zevolifesettings.admin_alert_subject.wbs_profiled_verified'),
                            'action'       => 'wbs_profile_verification'
                        ];
                        dispatch(new AdminAlertJob($data));
                    }
                }
                $user->wsuser()->update([
                    'is_cronofy' => true,
                ]);
            } elseif ($role->slug == 'health_coach') {
                $user->healthCoachUser()->update([
                    'is_cronofy' => true,
                ]);
            }
            $messageData = [
                'data'   => "Your dashboard details set successfully!",
                'status' => 1,
            ];
            return \Redirect::route('dashboard')->with('message', $messageData);
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }
}
