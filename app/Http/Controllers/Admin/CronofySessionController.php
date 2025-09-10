<?php

namespace App\Http\Controllers\Admin;

use App\Events\DigitaltherapyExceptionHandlingEvent;
use App\Events\SendSessionBookedEvent;
use App\Events\SendSessionCancelledEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AddBulkSessionAttachmentsRequest;
use App\Http\Requests\Admin\CreateGroupSessionRequest;
use App\Http\Requests\Admin\EditSessionRequest;
use App\Http\Requests\Admin\UpdateGroupSessionRequest;
use App\Jobs\SendGroupSessionPushNotification;
use App\Models\Company;
use App\Models\CompanyLocation;
use App\Models\CronofyAuthenticate;
use App\Models\CronofyCalendar;
use App\Models\CronofySchedule;
use App\Models\CronofySessionAttachments;
use App\Models\CronofySessionEmailLogs;
use App\Models\DepartmentLocation;
use App\Models\DigitalTherapyService;
use App\Models\ScheduleUsers;
use App\Models\Service;
use App\Models\ServiceSubCategory;
use App\Models\TeamLocation;
use App\Models\User;
use App\Models\WsClientNote;
use App\Repositories\CronofyRepository;
use Breadcrumbs;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Class CronofySessionController
 *
 * @package App\Http\Controllers\Admin
 */
class CronofySessionController extends Controller
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
     * variable to store the Cronofy Authenticate model object
     * @var CronofyAuthenticate $authenticateModel
     */
    protected $authenticateModel;

    /**
     * variable to store the Cronofy session emails model object
     * @var CronofySessionEmailLogs $cronofySessionEmailLogs
     */
    private $cronofySessionEmailLogs;

    /**
     * variable to store the Cronofy session emails model object
     * @var CronofySessionAttachments $sessionAttachment
     */
    private $sessionAttachment;

    /**
     * contructor to initialize model object
     */
    public function __construct(CronofyRepository $cronofyRepository, CronofyCalendar $cronofyCalendar, CronofySchedule $cronofySchedule, WsClientNote $wsClientNote, CronofyAuthenticate $authenticateModel, CronofySessionEmailLogs $cronofySessionEmailLogs, CronofySessionAttachments $sessionAttachment)
    {
        $this->cronofyRepository       = $cronofyRepository;
        $this->cronofyCalendar         = $cronofyCalendar;
        $this->cronofySchedule         = $cronofySchedule;
        $this->wsClientNote            = $wsClientNote;
        $this->authenticateModel       = $authenticateModel;
        $this->cronofySessionEmailLogs = $cronofySessionEmailLogs;
        $this->sessionAttachment       = $sessionAttachment;
        $this->bindBreadcrumbs();
    }

    /**
     * bind breadcrumbs of course module
     */
    private function bindBreadcrumbs()
    {
        Breadcrumbs::for ('cronofy.sessionlist.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Sessions');
        });
        Breadcrumbs::for ('cronofy.sessions.details', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('Cronofy.session_list.title.manage'), route('admin.cronofy.sessions.index'));
            $trail->push(trans('Cronofy.session_list.title.details'));
        });
        Breadcrumbs::for ('cronofy.groupsession.addsession', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('Cronofy.group_session.title.add_group_session'));
        });
        Breadcrumbs::for ('cronofy.groupsession.editsession', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('Cronofy.group_session.title.edit_group_session'));
        });
    }

    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $user              = auth::user();
        $role              = getUserRole($user);
        if ($role->slug == 'wellbeing_specialist') {
            $wsDetails          = $user->wsuser()->first();
        }
        if (!access()->allow('manage-sessions') || ($role->slug == 'wellbeing_specialist' && (!empty($wsDetails) && $wsDetails->is_cronofy && $wsDetails->responsibilities == 2))) {
            abort(403);
        }
        try {
            $data                           = array();
            $data['pagination']             = config('zevolifesettings.datatable.pagination.short');
            $data['ga_title']               = trans('page_title.calendly.index');
            $data['subcategories']          = [];
            $data['getWellbeingSpecialist'] = [];
            if ($role->group == 'company' || $role->group == 'reseller') {
                $company         = $user->company()->first();
                $data['company'] = $company;
            }
            if ($role->group == 'company' || $role->slug == 'super_admin' || $role->slug == 'wellbeing_team_lead' || $role->slug == 'wellbeing_specialist' || $role->group == 'reseller') {
                $company          = $user->company()->first();
                $checkEAPRestrict = getCompanyPlanAccess($user, 'eap');
                $checkDTAccess    = getDTAccessForParentsChildCompany($user, 'digital-therapy');
                // validate if access from company plan
                if (!empty($company) && (($role->group == 'company' && !$checkEAPRestrict) || ($role->group == 'reseller' && !$checkDTAccess))) {
                    return view('errors.401');
                }
                $service = Service::join('service_sub_categories', 'service_sub_categories.service_id', '=', 'services.id')
                    ->select('services.id', 'services.name')
                    ->join('digital_therapy_services', 'digital_therapy_services.service_id', '=', 'services.id');
                if ($role->group == 'company') {
                    $service = $service->where('digital_therapy_services.company_id', $company->id);
                }
                if ($role->group == 'reseller' && is_null($company->parent_id)) {
                    $childCompanies = Company::select('id')->where('id', $company->id)->orWhere('parent_id', $company->id)->pluck('id')->toArray();
                    $service        = $service->whereIn('digital_therapy_services.company_id', $childCompanies);
                }
                $service         = $service->distinct()->get()->pluck('name', 'id')->toArray();
                $data['service'] = $service;
            } else {
                $data['service'] = $user->userservices()->join('services', 'services.id', '=', 'service_sub_categories.id')->select('services.id', 'services.name')->get()->pluck('name', 'id')->toArray();
            }

            if ($role->group == 'company') {
                $data['companies'] = [];
            }
            if ($role->slug == 'super_admin' || $role->slug == 'wellbeing_team_lead' || $role->slug == 'wellbeing_specialist' || ($role->group == 'reseller' && is_null($company->parent_id))) {
                $companies = Company::select('companies.id', 'companies.name')->leftJoin('cp_company_plans', 'companies.id', '=', 'cp_company_plans.company_id')
                    ->leftJoin('cp_plan', 'cp_plan.id', '=', 'cp_company_plans.plan_id')
                    ->leftJoin('cp_plan_features', 'cp_plan_features.plan_id', '=', 'cp_plan.id')
                    ->leftJoin('cp_features', 'cp_features.id', '=', 'cp_plan_features.feature_id');
                $companies = $companies->where(function ($q) {
                    $q->where('cp_features.slug', 'digital-therapy')
                        ->orWhere('cp_features.slug', 'eap');
                })->groupBy('companies.id');
                if ($role->group == 'reseller' && $company->parent_id == null) {
                    $companies = $companies->where(function ($q) use ($company) {
                        $q->where('companies.id', $company->id)
                            ->orWhere('companies.parent_id', $company->id);
                    });
                }

                $companies         = $companies->pluck('name', 'id')->toArray();
                $data['companies'] = $companies;
            } else {
                $data['companies'] = DigitalTherapyService::join('companies', 'companies.id', '=', 'digital_therapy_services.company_id')
                    ->where('digital_therapy_services.ws_id', $user->id)
                    ->select('companies.id', 'companies.name')
                    ->get()
                    ->pluck('name', 'id')
                    ->toArray();
            }

            if (isset($request->service) && !empty($request->service)) {
                if ($role->group == 'company' || $role->slug == 'super_admin' || $role->slug == 'wellbeing_team_lead' || $role->group == 'reseller') {
                    $subcategories = ServiceSubCategory::select('service_sub_categories.name', 'service_sub_categories.id')
                        ->join('services', 'services.id', '=', 'service_sub_categories.service_id')
                        ->leftJoin('digital_therapy_services', 'digital_therapy_services.service_id', '=', 'services.id')
                        ->leftJoin('users_services', 'users_services.user_id', '=', 'digital_therapy_services.ws_id');
                    if ($role->group == 'company') {
                        $subcategories = $subcategories->where('digital_therapy_services.company_id', $company->id);
                    }
                    if ($role->group == 'reseller' && is_null($company->parent_id)) {
                        $childCompanies = Company::select('id')->where('id', $company->id)->orWhere('parent_id', $company->id)->pluck('id')->toArray();
                        $subcategories  = $subcategories->whereIn('digital_therapy_services.company_id', $childCompanies);
                    }
                    $subcategories = $subcategories->where('services.id', $request->service)
                        ->get()
                        ->pluck('name', 'id')->toArray();
                    $data['subcategories'] = $subcategories;
                } else {
                    $data['subcategories'] = $user->userservices()->select('service_sub_categories.name', 'service_sub_categories.id')->where('service_sub_categories.service_id', $request->service)->get()->pluck('name', 'id')->toArray();
                }
            }

            if ($role->group == 'company' || $role->slug == 'super_admin' || $role->group == 'reseller' || $role->slug == 'wellbeing_team_lead') {
                $getWellbeingSpecialist = User::select(\DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS name"), 'users.id')
                    ->leftJoin('user_profile', 'user_profile.user_id', '=', 'users.id')
                    ->leftJoin('ws_user', 'ws_user.user_id', '=', 'users.id')
                    ->leftJoin('digital_therapy_services', 'digital_therapy_services.ws_id', '=', 'users.id')
                    ->leftJoin('users_services', 'users_services.user_id', '=', 'users.id')
                    ->where('ws_user.is_cronofy', true);
                if ($role->group == 'company') {
                    $getWellbeingSpecialist = $getWellbeingSpecialist->where('digital_therapy_services.company_id', $company->id);
                }
                if ($role->group == 'reseller' && is_null($company->parent_id)) {
                    $childCompanies         = Company::select('id')->where('id', $company->id)->orWhere('parent_id', $company->id)->pluck('id')->toArray();
                    $getWellbeingSpecialist = $getWellbeingSpecialist->whereIn('digital_therapy_services.company_id', $childCompanies);
                }
                $getWellbeingSpecialist         = $getWellbeingSpecialist->distinct()->get()->pluck('name', 'id')->toArray();
                $data['getWellbeingSpecialist'] = $getWellbeingSpecialist;
            }

            $data['duration']               = config('zevolifesettings.dt_duration');
            $data['status']                 = config('zevolifesettings.dt_status');
            $data['role']                   = $role;
            $data['company_col_visibility'] = $role->slug == 'super_admin';
            return \view('admin.cronofy.sessionlist.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('Cronofy.session_list.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.cronofy.sessions')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function getSessions(Request $request)
    {
        if (!access()->allow('manage-sessions')) {
            return response()->json([
                'message' => trans('Cronofy.client_list.messages.unauthorized_access'),
            ], 422);
        }
        try {
            return $this->cronofySchedule->getSessionData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('Cronofy.session_list.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function markAsCompleted(CronofySchedule $cronofySchedule)
    {
        if (!access()->allow('manage-sessions')) {
            abort(403);
        }
        try {
            if ($cronofySchedule->update(['status' => 'completed'])) {
                return array('completed' => 'true');
            }
            return array('completed' => 'error');
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('Cronofy.session_list.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @param  Calendly $calendly
     * @return view
     */
    public function show(CronofySchedule $cronofySchedule)
    {
        $role = getUserRole();
        if (!access()->allow('view-sessions') || ($role->slug == 'wellbeing_specialist' && $cronofySchedule->ws_id != \Auth::user()->id)) {
            abort(403);
        }
        for ($i = 0; $i <= 10; $i++) {
          $scoreData[$i] = $i;
        }
        try {
            $data                        = $this->cronofySchedule->getSessionDetails($cronofySchedule);
            $data['role']                = $role;
            $data['scoreData']           = $scoreData;
            $data['ga_title']            = trans('page_title.calendly.view');

            return \view('admin.cronofy.sessionlist.details', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('Cronofy.session_list.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.cronofy.sessions.index')->with('message', $messageData);
        }
    }

    /**
     * @param CronofySchedule $cronofySchedule
     * @param EditSessionRequest $request
     * @return JsonResponse
     */
    public function updateSession(CronofySchedule $cronofySchedule, EditSessionRequest $request)
    {
        if (!access()->allow('view-sessions')) {
            return redirect()->back()->with('message', [
                'data'   => trans('Cronofy.client_list.messages.unauthorized_access'),
                'status' => 0,
            ]);
        }
        try {
            \DB::beginTransaction();
            $updateData = [];
            if (!empty($request['no_show']) && $request['no_show'] != null) {
                $updateData['no_show'] = $request['no_show'];
            }
            if (!empty($request['notes']) && $request['notes'] != null) {
                $updateData['notes'] = $request['notes'];
            }

            if (!empty($request['score'])) {
                $updateData['score'] = $request['score'];
            }

            $data = $cronofySchedule->update($updateData);
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('Cronofy.session_list.messages.session_update_success'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.cronofy.sessions.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('Cronofy.session_list.messages.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.cronofy.sessions.index')->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            return response()->json([
                'status'  => 0,
                'message' => trans('labels.common_title.something_wrong'),
            ], 500);
        }
    }

    /**
     * @param CronofySchedule $cronofySchedule
     * @param Request $request
     * @return JsonResponse
     */
    public function cancelSession(CronofySchedule $cronofySchedule, Request $request)
    {
        if (!access()->allow('manage-sessions')) {
            return redirect()->back()->with('message', [
                'data'   => trans('Cronofy.client_list.messages.unauthorized_access'),
                'status' => 0,
            ]);
        }
        try {
            $meta        = $cronofySchedule->meta;
            $uid         = (!empty($meta->uid) ? $meta->uid : date('Ymd') . 'T' . date('His') . '-' . rand() . '@zevo.app');
            $user        = auth()->user();
            $role        = getUserRole($user);
            $nowInUTC    = now(config('app.timezone'))->todatetimeString();
            $updateArray = [
                'cancelled_reason' => $request['cancelled_reason'],
                'cancelled_by'     => $user->id,
                'cancelled_at'     => $nowInUTC,
                'status'           => 'canceled',
            ];
            $data = $cronofySchedule->update($updateArray);
            if ($data) {
                // Cancel session from cronofy
                $this->cronofyRepository->cancelEvent($cronofySchedule->ws_id, $cronofySchedule->event_id);
                //Send session cancel email to User
                if (!empty($cronofySchedule->user)) {
                    $compnay      = $cronofySchedule->user->company->first();
                    $userTimeZone = $cronofySchedule->user->timezone;
                } else {
                    $compnay      = company::where('id', $cronofySchedule->company_id)->first();
                    $userTimeZone = $cronofySchedule->wellbeingSpecialist->timezone;
                }
                $eventDate = Carbon::parse("{$cronofySchedule->start_time}", config('app.timezone'))->setTimezone($userTimeZone)->format('M d, Y');
                $eventTime = Carbon::parse("{$cronofySchedule->start_time}", config('app.timezone'))->setTimezone($userTimeZone)->format('h:i A');
                $duration  = Carbon::parse($cronofySchedule->end_time)->diffInMinutes($cronofySchedule->start_time);

                $sessionCancelledBy = 'wellbeing_specialist';
                if ($role->slug == 'company_admin' && $role->group == 'company') {
                    $sessionCancelledBy = 'company_admin';
                }

                $sessionData = [
                    'company'         => (!empty($compnay->id) ? $compnay->id : null),
                    'email'           => ((!$cronofySchedule->is_group) ? $cronofySchedule->user->email : $cronofySchedule->wellbeingSpecialist->email),
                    'userName'        => ((!$cronofySchedule->is_group) ? $cronofySchedule->user->full_name : null),
                    'wsName'          => $cronofySchedule->wellbeingSpecialist->full_name,
                    'userFirstName'   => ((!$cronofySchedule->is_group) ? $cronofySchedule->user->first_name : null),
                    'wsFirstName'     => $cronofySchedule->wellbeingSpecialist->first_name,
                    'serviceName'     => $cronofySchedule->name,
                    'cancelledReason' => (!empty($request['cancelled_reason']) ? $request['cancelled_reason'] : ''),
                    'eventDate'       => $eventDate,
                    'isGroup'         => $cronofySchedule->is_group,
                    'companyName'     => $compnay->name,
                    'sessionId'       => $cronofySchedule->id,
                    'eventTime'       => $eventTime,
                    'duration'        => $duration,
                    'cancelledBy'     => ((!$cronofySchedule->is_group) ? 'wellbeing_specialist' : 'zca'),
                    'iCal'            => generateiCal([
                        'uid'         => $uid,
                        'appName'     => config('app.name'),
                        'inviteTitle' => trans('Cronofy.ical.title', [
                            'user_name' => ((!$cronofySchedule->is_group) ? $cronofySchedule->user->full_name : $sessionCancelledBy),
                            'wbs_name'  => $cronofySchedule->wellbeingSpecialist->full_name,
                        ]),
                        'description' => "{$cronofySchedule->name} event has been cancelled.",
                        'timezone'    => $userTimeZone,
                        'today'       => Carbon::parse($nowInUTC)->format('Ymd\THis\Z'),
                        'startTime'   => Carbon::parse($cronofySchedule->start_time)->format('Ymd\THis\Z'),
                        'endTime'     => Carbon::parse($cronofySchedule->end_time)->format('Ymd\THis\Z'),
                        'orgName'     => $cronofySchedule->wellbeingSpecialist->full_name,
                        'orgEamil'    => $cronofySchedule->wellbeingSpecialist->email,
                        'userEmail'   => ((!$cronofySchedule->is_group) ? $cronofySchedule->user->email : $cronofySchedule->wellbeingSpecialist->email),
                        'sequence'    => 0,
                    ], 'cancelled'),
                ];
                event(new SendSessionCancelledEvent($sessionData));
                $messageData = [
                    'status' => 1,
                ];

                if ($cronofySchedule->is_group) {
                    $notificationUser = User::select('users.*', 'user_notification_settings.flag AS notification_flag')
                        ->leftJoin('user_notification_settings', function ($join) {
                            $join->on('user_notification_settings.user_id', '=', 'users.id')
                                ->where('user_notification_settings.flag', '=', 1)
                                ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['digital-therapy', 'all']);
                        })
                        ->whereRaw('users.id IN ( SELECT user_id FROM `session_group_users` WHERE session_id = ? )', [$cronofySchedule->id])
                        ->where('is_blocked', false)
                        ->groupBy('users.id')
                        ->get()
                        ->toArray();

                    // dispatch job to send push notification to all user when group session created
                    \dispatch(new SendGroupSessionPushNotification($cronofySchedule, "group-session-cancel", $notificationUser, ''));

                    foreach ($notificationUser as $nUser) {
                        $eventDate = Carbon::parse("{$cronofySchedule->start_time}", config('app.timezone'))->setTimezone($nUser['timezone'])->format('M d, Y');
                        $eventTime = Carbon::parse("{$cronofySchedule->start_time}", config('app.timezone'))->setTimezone($nUser['timezone'])->format('h:i A');

                        $sessionDataToUsers = [
                            'company'         => (!empty($compnay->id) ? $compnay->id : null),
                            'wsName'          => $cronofySchedule->wellbeingSpecialist->full_name,
                            'wsFirstName'     => $cronofySchedule->wellbeingSpecialist->first_name,
                            'serviceName'     => $cronofySchedule->name,
                            'cancelledReason' => (!empty($request['cancelled_reason']) ? $request['cancelled_reason'] : ''),
                            'eventDate'       => $eventDate,
                            'isGroup'         => $cronofySchedule->is_group,
                            'companyName'     => $compnay->name,
                            'sessionId'       => $cronofySchedule->id,
                            'eventTime'       => $eventTime,
                            'duration'        => $duration,
                            'to'              => 'user',
                            'cancelledBy'     => $sessionCancelledBy,
                        ];

                        $sessionDataToUsers['email']         = $nUser['email'];
                        $sessionDataToUsers['userName']      = $nUser['first_name'] . ' ' . $nUser['last_name'];
                        $sessionDataToUsers['userFirstName'] = $nUser['first_name'];
                        $sessionDataToUsers['iCal']          = generateiCal([
                            'uid'         => $uid,
                            'appName'     => config('app.name'),
                            'inviteTitle' => trans('Cronofy.ical.title', [
                                'user_name' => $nUser['first_name'] . ' ' . $nUser['last_name'],
                                'wbs_name'  => $cronofySchedule->wellbeingSpecialist->full_name,
                            ]),
                            'description' => "{$cronofySchedule->name} event has been cancelled.",
                            'timezone'    => $nUser['timezone'],
                            'today'       => Carbon::parse($nowInUTC)->format('Ymd\THis\Z'),
                            'startTime'   => Carbon::parse($cronofySchedule->start_time)->format('Ymd\THis\Z'),
                            'endTime'     => Carbon::parse($cronofySchedule->end_time)->format('Ymd\THis\Z'),
                            'orgName'     => $cronofySchedule->wellbeingSpecialist->full_name,
                            'orgEamil'    => $cronofySchedule->wellbeingSpecialist->email,
                            'userEmail'   => $nUser['email'],
                            'sequence'    => 0,
                        ], 'cancelled');

                        event(new SendSessionCancelledEvent($sessionDataToUsers));
                    }
                }
            } else {
                $messageData = [
                    'status' => 0,
                ];
            }
            return $messageData;

        } catch (\Exception $exception) {
            $user = $company = [];
            if (!empty($cronofySchedule) && !empty($cronofySchedule->user)) {
                $company      = $cronofySchedule->user->company->first();
                $userTimeZone = $cronofySchedule->user;
            } else {
                $company = company::where('id', $cronofySchedule->company_id)->first();
                $user    = $cronofySchedule->wellbeingSpecialist;
            }
            // Send email when trow error while digital therapy any operation
            event(new DigitaltherapyExceptionHandlingEvent([
                'type'         => 'Cancellation',
                'message'      => (string) trans('labels.common_title.something_wrong'),
                'company'      => $company,
                'wsDetails'    => $user,
                'errorDetails' => json_encode($exception->error_details()),
            ]));
        }
    }

    /**
     * @param CronofySchedule $cronofySchedule
     * @param Request $request
     * @return JsonResponse
     */
    public function rescheduleSession(CronofySchedule $cronofySchedule, Request $request)
    {
        if (!access()->allow('manage-sessions')) {
            return redirect()->back()->with('message', [
                'data'   => trans('Cronofy.client_list.messages.unauthorized_access'),
                'status' => 0,
            ]);
        }
        try {
            $appTimezone            = config('app.timezone');
            $healthCoachUnavailable = [];
            if (!$cronofySchedule->is_group) {
                $loginUser = User::where('id', $cronofySchedule->user_id)->first();
                $company   = $loginUser->company()->select('companies.id', 'companies.eap_tab')->first();
            } else {
                $loginUser = auth::user();
                $company   = Company::where('id', $cronofySchedule->company_id)->first();
            }

            $timezone = (!empty($loginUser->timezone) ? $loginUser->timezone : $appTimezone);

            if (!empty($cronofySchedule)) {
                \DB::beginTransaction();
                $combinedAvailability  = [];
                $featureBooking        = config('cronofy.feature_booking');
                $advanceBooking        = config('cronofy.advanceBooking');
                $services              = Service::where('id', $cronofySchedule->service_id)->select('services.name')->first();
                $duration              = config('cronofy.schedule_duration');
                $digitalTherapyDetails = $company->digitalTherapy()->first();
                $serviceName           = (!empty($services)) ? $services->name : config('cronofy.serviceName');
                if (!empty($digitalTherapyDetails)) {
                    $advanceBooking    = $digitalTherapyDetails->dt_advanced_booking;
                    $featureBooking    = $digitalTherapyDetails->dt_future_booking;
                    $duration          = ($serviceName == 'Counselling') ? $digitalTherapyDetails->dt_counselling_duration : $digitalTherapyDetails->dt_coaching_duration;
                    $wsUser            = User::where('id', $cronofySchedule->ws_id)->first();
                    $setHoursBy        = $digitalTherapyDetails->set_hours_by;
                    $setAvailabilityBy = $digitalTherapyDetails->set_availability_by;
                    $wsDetails         = $wsUser->wsuser()->first();
                    $this->cronofyRepository->availabilityRuleRemove($wsUser);

                    // Check company availability type and get company availability
                    $digitalTherapySlot = $company->setLocationWiseDTAvailability($cronofySchedule->location_id, $wsUser, $setHoursBy, $setAvailabilityBy);
                    $startTime          = Carbon::now()->setTimezone($appTimezone)->addHour($advanceBooking)->toDateTimeString();
                    $endTime            = Carbon::now()->setTimezone($appTimezone)->addDays($featureBooking)->toDateTimeString();
                    $services           = Service::where('id', $cronofySchedule->service_id)->select('services.name', 'services.session_duration')->first();
                    $serviceName        = (!empty($services)) ? $services->name : config('cronofy.serviceName');
                    $duration           = (!empty($services)) ? $services->session_duration : config('cronofy.schedule_duration');

                    // When Wellbeing specialist set leave so leave days set on session
                    if ($wsUser->availability_status == 2) {
                        $healthCoachUnavailable = $wsUser->healthCocahAvailability()->select(
                            'from_date',
                            'to_date'
                        )->get()->toArray();
                    }
                    if (($setHoursBy == 2 && $setAvailabilityBy == 1) || ($setHoursBy == 1 && $setAvailabilityBy == 1)) {
                        $wsSlot               = $wsUser->healthCocahSlots()->select('day', 'start_time', 'end_time')->get()->toArray();
                        $combinedAvailability = alignedAvailability($digitalTherapySlot['data'], $wsSlot);
                        $this->cronofyRepository->updateAvailability($combinedAvailability, $wsUser, $digitalTherapySlot['timezone'], false, $appTimezone, $duration);
                        $queryPeriod          = generateQueryPeriod($combinedAvailability, $startTime, $endTime, $appTimezone, $digitalTherapySlot['timezone'], $healthCoachUnavailable, $duration);
                    } else if (($setHoursBy == 2 && $setAvailabilityBy == 2) || ($setHoursBy == 1 && $setAvailabilityBy == 2)) {
                        $queryPeriod = generalSpecificQueryPeriod($digitalTherapySlot, $appTimezone, $duration);
                    }

                    $tokens      = $this->authenticateModel->getTokens($cronofySchedule->ws_id);
                    $subId       = $tokens['subId'];
                    $response    = $this->cronofyRepository->dateTimePicker($cronofySchedule->ws_id);
                    $startTime   = date("Y-m-d\TH:i:s.000\Z", strtotime($startTime));
                    $endTime     = date("Y-m-d\TH:i:s.000\Z", strtotime($endTime));
                    $serviceId   = $cronofySchedule->service_id;
                    $topicId     = $cronofySchedule->topic_id;
                    $getUsers    = ScheduleUsers::where('session_id', $cronofySchedule->id)->get()->toArray();
                    $response    = $this->cronofyRepository->dateTimePicker($cronofySchedule->ws_id);

                    $insertData                  = array();
                    $insertData['event_id']      = $cronofySchedule->event_id;
                    $insertData['scheduling_id'] = $cronofySchedule->scheduling_id;
                    $insertData['name']          = $cronofySchedule->name;
                    if (!$cronofySchedule->is_group) {
                        $insertData['user_id'] = $loginUser->id;
                    }
                    $insertData['ws_id']            = $wsUser->id;
                    $insertData['created_by']       = $loginUser->id;
                    $insertData['service_id']       = $serviceId;
                    $insertData['is_group']         = $cronofySchedule->is_group;
                    $insertData['company_id']       = $company->id;
                    $insertData['topic_id']         = $topicId;
                    $insertData['token']            = !(empty($response)) ? $response['element_token']['token'] : null;
                    $insertData['location']         = $wsDetails->video_link;
                    $insertData['location_id']      = $cronofySchedule->location_id;
                    $insertData['event_created_at'] = \now(config('app.timezone'))->toDateTimeString();
                    $insertData['status']           = 'open';
                    $insertData['created_at']       = \now(config('app.timezone'))->toDateTimeString();
                    $insertData['updated_at']       = \now(config('app.timezone'))->toDateTimeString();

                    $record = CronofySchedule::create($insertData);

                    if (!empty($record)) {
                        $data               = array();
                        $data               = $insertData;
                        $data['scheduleId'] = $record->id;
                        foreach ($getUsers as $user) {
                            $scheduleUsers[] = [
                                'session_id' => $record->id,
                                'user_id'    => $user['user_id'],
                                'created_at' => Carbon::now(),
                            ];
                        }
                        ScheduleUsers::insert($scheduleUsers);
                        \DB::commit();
                    }

                    $data['duration']    = $duration;
                    $data['timezone']    = $timezone;
                    $data['subId']       = $subId;
                    $data['startTime']   = $startTime;
                    $data['endTime']     = $endTime;
                    $data['queryPeriod'] = $queryPeriod;
                    $data['reschedule']  = true;

                    return \view('admin.cronofy.groupsession.cronofy-ui-element', $data);
                }
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('Cronofy.session_list.messages.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.cronofy.sessions.index')->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            report($exception);
            return \view('admin.cronofy.groupsession.callback');
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function sessionCallback(Request $request)
    {
        if (!access()->allow('manage-sessions')) {
            abort(403);
        }
        try {
            return \view('admin.cronofy.sessionlist.callback');
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function createSession(Request $request)
    {
        $user              = auth::user();
        $role              = getUserRole($user);
        if ($role->slug == 'wellbeing_specialist') {
            $wsDetails          = $user->wsuser()->first();
        }
        if (!access()->allow('create-sessions') || ($role->slug == 'wellbeing_specialist' && (!empty($wsDetails) && $wsDetails->is_cronofy && $wsDetails->responsibilities == 2))) {
            abort(403);
        }
        try {
            if ($request->type == 2) {
                $messageData = [
                    'data'   => trans('Cronofy.session_list.messages.disabled_group_session'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.cronofy.sessions.index')->with('message', $messageData);
            }

            $company          = $user->company->first();
            $appTimeZone      = config('app.timezone');
            $now              = now($appTimeZone)->toDateTimeString();
            $checkEAPRestrict = getCompanyPlanAccess($user, 'eap');
            // validate if access from company plan
            if (!empty($company) && !$checkEAPRestrict) {
                return view('errors.401');
            }
            $data            = array();
            $data['edit']    = false;
            $data['company'] = [];
            $serviceType     = $request->type;
            if ($role->group == 'company') {
                $serviceRecords = Service::join('service_sub_categories', 'service_sub_categories.service_id', '=', 'services.id')
                    ->join('digital_therapy_services', 'digital_therapy_services.service_id', '=', 'services.id')
                    ->where('digital_therapy_services.company_id', $company->id);
                if ($serviceType == 2) {
                    $serviceRecords->where('services.is_counselling', '=', false);
                }
                $serviceRecords = $serviceRecords->select('services.id', 'services.name')
                    ->distinct()
                    ->get()->pluck('name', 'id')->toArray();
            } else {
                $serviceRecords = Service::select(
                    'services.id',
                    'services.name'
                )
                    ->leftJoin('service_sub_categories', 'service_sub_categories.service_id', '=', 'services.id')
                    ->leftJoin('users_services', 'users_services.service_id', '=', 'service_sub_categories.id')
                    ->leftJoin('users', 'users.id', '=', 'users_services.user_id')
                    ->where('users_services.user_id', $user->id);
                if ($serviceType == 2) {
                    $serviceRecords->where('services.is_counselling', '=', false);
                }
                $serviceRecords = $serviceRecords->distinct()
                    ->get()->pluck('name', 'id')->toArray();
            }
            $data['service']       = $serviceRecords;
            $data['subcategories'] = [];
            $data['companies']     = DigitalTherapyService::join('companies', 'companies.id', '=', 'digital_therapy_services.company_id')
                ->where('digital_therapy_services.ws_id', $user->id)
                ->select('companies.id', 'companies.name')
                ->where('companies.subscription_start_date', '<=', $now)
                ->where('companies.subscription_end_date', '>=', $now)
                ->get()
                ->pluck('name', 'id')
                ->toArray();
            $data['role']        = $role;
            $data['sessionType'] = $request->type;

            if (!empty($company)) {
                $data['company'] = $this->getAllCompaniesGroupType($company);
            }

            return \view('admin.cronofy.groupsession.create', $data);
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * @param CreateGroupSessionRequest $request
     * @return View
     */
    public function storeGroupSession(CreateGroupSessionRequest $request)
    {
        if (!access()->allow('create-sessions')) {
            abort(403);
        }
        try {
            \DB::beginTransaction();

            // Input hidden field value and url value don't match then redirect again to create session page.
            if ((int) $request->sessionType != (int) $request->type) {
                $messageData = [
                    'data'   => trans('Cronofy.group_session.message.something_wrong'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.cronofy.sessions.create', $request->type)->with('message', $messageData);
            }

            if ($request->sessionType == 2) {
                $messageData = [
                    'data'   => trans('Cronofy.session_list.messages.disabled_group_session'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.cronofy.sessions.create', $request->type)->with('message', $messageData);
            }

            if ($request->type == 2) {
                $messageData = [
                    'data'   => trans('Cronofy.session_list.messages.disabled_group_session'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.cronofy.sessions.create', $request->type)->with('message', $messageData);
            }

            if (count($request->members_selected) > 1) {
                $messageData = [
                    'data'   => trans('Cronofy.session_list.messages.disabled_group_session'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.cronofy.sessions.create', $request->type)->with('message', $messageData);
            }

            $user                   = Auth::user();
            $appTimezone            = config('app.timezone');
            $timezone               = (!empty($user->timezone) ? $user->timezone : $appTimezone);
            $role                   = getUserRole($user);
            $combinedAvailability   = array();
            $healthCoachUnavailable = [];
            $combinedAvailability   = [];
            if ($role->slug == 'wellbeing_specialist') {
                $wsUser  = $user;
                $company = Company::where('id', $request->company)->first();
            } else {
                $wsUser  = User::where('id', $request->ws_user)->first();
                $company = $user->company()->first();
            }
            $duration              = config('cronofy.schedule_duration');
            $wsDetails             = $wsUser->wsuser()->first();
            $featureBooking        = config('cronofy.feature_booking');
            $advanceBooking        = config('cronofy.advanceBooking');
            $digitalTherapyDetails = $company->digitalTherapy()->first();
            $duration              = config('cronofy.schedule_duration');

            if (!empty($digitalTherapyDetails)) {
                $setHoursBy        = $digitalTherapyDetails->set_hours_by;
                $setAvailabilityBy = $digitalTherapyDetails->set_availability_by;

                $this->cronofyRepository->availabilityRuleRemove($wsUser);
                // Check company availability type and get company availability
                $digitalTherapySlot = $company->setLocationWiseDTAvailability($request->location, $wsUser, $setHoursBy, $setAvailabilityBy);
                $services           = Service::where('id', $request->service)->select('services.name', 'services.session_duration')->first();
                $serviceName        = (!empty($services)) ? $services->name : config('cronofy.serviceName');

                // When Wellbeing specialist set leave so leave days set on session
                if ($wsUser->availability_status == 2) {
                    $healthCoachUnavailable = $wsUser->healthCocahAvailability()->select(
                        'from_date',
                        'to_date'
                    )->get()->toArray();
                }
                $advanceBooking = $digitalTherapyDetails->dt_advanced_booking;
                $featureBooking = $digitalTherapyDetails->dt_future_booking;
                $duration       = (!empty($services)) ? $services->session_duration : config('cronofy.schedule_duration');

                $startTime = Carbon::now()->setTimezone($appTimezone)->addHour($advanceBooking)->toDateTimeString();
                $endTime   = Carbon::now()->setTimezone($appTimezone)->addDays($featureBooking)->toDateTimeString();

                if (($setHoursBy == 2 && $setAvailabilityBy == 1) || ($setHoursBy == 1 && $setAvailabilityBy == 1)) {
                    $wsSlot               = $wsUser->healthCocahSlots()->select('day', 'start_time', 'end_time')->get()->toArray();
                    $combinedAvailability = alignedAvailability($digitalTherapySlot['data'], $wsSlot);
                    $this->cronofyRepository->updateAvailability($combinedAvailability, $wsUser, $digitalTherapySlot['timezone'], false, $appTimezone, $duration);
                    $queryPeriod          = generateQueryPeriod($combinedAvailability, $startTime, $endTime, $appTimezone, $digitalTherapySlot['timezone'], $healthCoachUnavailable, $duration);
                } else if (($setHoursBy == 2 && $setAvailabilityBy == 2) || ($setHoursBy == 1 && $setAvailabilityBy == 2)) {
                    $queryPeriod = generalSpecificQueryPeriod($digitalTherapySlot, $appTimezone, $duration);
                }

                $startTime                      = Carbon::now()->setTimezone($appTimezone)->addHour($advanceBooking)->toDateTimeString();
                $endTime                        = Carbon::now()->setTimezone($appTimezone)->addDays($featureBooking)->toDateTimeString();
                $date                           = Carbon::now();
                $eventId                        = 'zevolife_dt_' . (string) $date->valueOf();
                $realTimeScheduleId             = 'sch_' . (string) Str::uuid();
                $tokens                         = $this->authenticateModel->getTokens($wsUser->id);
                $subId                          = $tokens['subId'];
                $response                       = $this->cronofyRepository->dateTimePicker($wsUser->id);
                $startTime                      = date("Y-m-d\TH:i:s.000\Z", strtotime($startTime));
                $endTime                        = date("Y-m-d\TH:i:s.000\Z", strtotime($endTime));
                $insertData                     = array();
                $insertData['event_id']         = $eventId;
                $insertData['scheduling_id']    = $realTimeScheduleId;
                $insertData['name']             = $serviceName;
                $insertData['created_by']       = $user->id;
                $insertData['ws_id']            = ($role->slug == 'wellbeing_specialist') ? $user->id : $request->ws_user;
                $insertData['user_id']          = ($request->sessionType == 1 && !empty($request->members_selected)) ? $request->members_selected[0] : null;
                $insertData['service_id']       = $request->service;
                $insertData['topic_id']         = $request->sub_category;
                $insertData['company_id']       = ($role->group == 'company') ? $company->id : $request->company;
                $insertData['is_group']         = ($request->sessionType == 2);
                $insertData['notes']            = $request->notes;
                $insertData['event_identifier'] = null;
                $insertData['location']         = $wsDetails->video_link;
                $insertData['location_id']      = $request->location;
                $insertData['token']            = !(empty($response)) ? $response['element_token']['token'] : null;
                $insertData['event_created_at'] = \now(config('app.timezone'))->toDateTimeString();
                $insertData['status']           = 'open';
                $insertData['created_at']       = \now(config('app.timezone'))->toDateTimeString();
                $insertData['updated_at']       = \now(config('app.timezone'))->toDateTimeString();

                $record = CronofySchedule::create($insertData);
                $data   = array();
                if ($record) {
                    $data               = $insertData;
                    $data['scheduleId'] = $record->id;
                    foreach ($request->members_selected as $value) {
                        $scheduleUsers[] = [
                            'session_id' => $record->id,
                            'user_id'    => $value,
                            'created_at' => Carbon::now(),
                        ];
                    }
                    ScheduleUsers::insert($scheduleUsers);
                    \DB::commit();
                }

                $data['duration']    = $duration;
                $data['timezone']    = $timezone;
                $data['subId']       = $subId;
                $data['startTime']   = $startTime;
                $data['endTime']     = $endTime;
                $data['queryPeriod'] = $queryPeriod;
                $data['reschedule']  = false;
                return \view('admin.cronofy.groupsession.cronofy-ui-element', $data);
            }
        } catch (\Exception $exception) {
            report($exception);
            return \view('admin.cronofy.groupsession.callback');
        }
    }

    /**
     * @param Request $request
     * @param cronofySchedule $cronofySchedule
     * @return View
     */
    public function editSession(Request $request, CronofySchedule $cronofySchedule)
    {
        $user             = auth()->user();
        $role             = getUserRole($user);
        if (!access()->allow('edit-sessions') || ($role->slug == 'wellbeing_specialist' && $cronofySchedule->ws_id != \Auth::user()->id)) {
            abort(403);
        }
        try {
            $data             = array();
            $data['edit']     = true;
            $locations        = [];
            $company          = Company::where('id', $cronofySchedule->company_id)->first();
            $checkEAPRestrict = getCompanyPlanAccess($user, 'eap');
            // validate if access from company plan
            if (!empty($company) && !$checkEAPRestrict) {
                return view('errors.401');
            }
            $data['cronofySchedule'] = $cronofySchedule;
            $data['startTime']       = Carbon::parse($cronofySchedule->start_time)->setTimezone($user->timezone)->format('M d, Y H:i A');
            $data['selectedUsers']   = ScheduleUsers::where('session_id', $cronofySchedule->id)->select('user_id')->get()->pluck('user_id')->toArray();
            if ($role->group == 'company') {
                $data['service'] = Service::join('service_sub_categories', 'service_sub_categories.service_id', '=', 'services.id')
                    ->join('digital_therapy_services', 'digital_therapy_services.service_id', '=', 'services.id')
                    ->where('digital_therapy_services.company_id', $company->id)
                    ->where('services.name', '!=', 'Counselling')
                    ->select('services.id', 'services.name')
                    ->distinct()
                    ->get()->pluck('name', 'id')->toArray();
                $data['subcategories'] = ServiceSubCategory::join('services', 'services.id', '=', 'service_sub_categories.service_id')
                    ->leftJoin('digital_therapy_services', 'digital_therapy_services.service_id', '=', 'services.id')
                    ->leftJoin('users_services', 'users_services.user_id', '=', 'digital_therapy_services.ws_id')
                    ->where('digital_therapy_services.company_id', $company->id)
                    ->where('services.id', $cronofySchedule->service_id)
                    ->select('service_sub_categories.name', 'service_sub_categories.id')
                    ->get()
                    ->pluck('name', 'id')->toArray();
                $data['companies']              = [];
                $data['getWellbeingSpecialist'] = User::select(\DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS name"), 'users.id')
                    ->leftJoin('user_profile', 'user_profile.user_id', '=', 'users.id')
                    ->leftJoin('ws_user', 'ws_user.user_id', '=', 'users.id')
                    ->leftJoin('digital_therapy_services', 'digital_therapy_services.ws_id', '=', 'users.id')
                    ->leftJoin('users_services', 'users_services.user_id', '=', 'users.id')
                    ->where('users_services.service_id', $cronofySchedule->topic_id)
                    ->where('ws_user.is_cronofy', true)
                    ->where('digital_therapy_services.company_id', $company->id)
                    ->distinct()
                    ->get();
            } else {
                $data['service'] = Service::select(
                    'services.id',
                    'services.name'
                )
                    ->leftJoin('service_sub_categories', 'service_sub_categories.service_id', '=', 'services.id')
                    ->leftJoin('users_services', 'users_services.service_id', '=', 'service_sub_categories.id')
                    ->leftJoin('users', 'users.id', '=', 'users_services.user_id')
                    ->where('users_services.user_id', $user->id)
                    ->distinct()->get()->pluck('name', 'id')->toArray();
                $data['subcategories'] = $user->userservices()->select('service_sub_categories.name', 'service_sub_categories.id')->where('service_sub_categories.service_id', $cronofySchedule->service_id)->get()->pluck('name', 'id')->toArray();
                $data['companies']     = DigitalTherapyService::join('companies', 'companies.id', '=', 'digital_therapy_services.company_id')
                    ->where('digital_therapy_services.ws_id', $user->id)
                    ->select('companies.id', 'companies.name')
                    ->get()
                    ->pluck('name', 'id')
                    ->toArray();
                $data['getWellbeingSpecialist'] = [];

                $digitalTherapyDetails = $company->digitalTherapy()->first();
                if (!empty($digitalTherapyDetails)) {
                    $setHoursBy        = $digitalTherapyDetails->set_hours_by;
                    $setAvailabilityBy = $digitalTherapyDetails->set_availability_by;
                    if ($setHoursBy == 2 && $setAvailabilityBy == 1) {
                        //Location General Data
                        $locations = $company->digitalTherapySlots()->select('company_locations.id', 'company_locations.name')
                            ->leftjoin('company_locations', 'company_locations.id', '=', 'digital_therapy_slots.location_id')
                            ->where('digital_therapy_slots.company_id', $company->id)
                            ->whereNotNull('digital_therapy_slots.location_id')
                            ->pluck('company_locations.name', 'company_locations.id')
                            ->toArray();
                    } else if ($setHoursBy == 2 && $setAvailabilityBy == 2) {
                        // Location Specific data
                        $locations = $company->digitalTherapySpecificSlots()->select('company_locations.id', 'company_locations.name')
                            ->leftjoin('company_locations', 'company_locations.id', '=', 'digital_therapy_specific.location_id')
                            ->where('digital_therapy_specific.company_id', $company->id)
                            ->whereNotNull('digital_therapy_specific.location_id')
                            ->pluck('company_locations.name', 'company_locations.id')
                            ->toArray();
                    }
                }
            }
            $startDate   = Carbon::parse($cronofySchedule->start_time);
            $endDate     = Carbon::parse($cronofySchedule->end_time);
            $joinCheck   = Carbon::now()->between($startDate, $endDate) && $cronofySchedule->status != 'canceled' && $cronofySchedule->status != 'rescheduled' && $cronofySchedule->status != 'completed';
            $updateCheck = false;
            if ($cronofySchedule->status == 'booked' && ((Carbon::parse($startDate) > Carbon::now()) || Carbon::now()->between($startDate, $endDate))) {
                $updateCheck = true;
            }
            if (!in_array($role->slug, ['wellbeing_specialist', 'company_admin'])) {
                $joinCheck = $updateCheck = false;
            }
            $data['allowJoin']          = $joinCheck;
            $data['join_url']           = $cronofySchedule->location;
            $data['allowUpdate']        = $updateCheck;
            $data['company']            = $this->getAllCompaniesGroupType($company, $cronofySchedule->location_id ?? null);
            $data['role']               = $role;
            $data['status']             = $cronofySchedule->status;
            $data['location']           = $locations;
            $data['sessionType']        = ($cronofySchedule->is_group) ? 2 : 1;

            return \view('admin.cronofy.groupsession.edit', $data);
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * @param Request $request
     * @param cronofySchedule $cronofySchedule
     * @return View
     */
    public function updateGroupSession(UpdateGroupSessionRequest $request, CronofySchedule $cronofySchedule)
    {
        if (!access()->allow('edit-sessions')) {
            abort(403);
        }
        try {
            \DB::beginTransaction();
            $records = $cronofySchedule->update([
                'notes' => $request->notes,
            ]);

            if ($records) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('Cronofy.group_session.message.data_update_success'),
                    'status' => 1,
                ];

                return \Redirect::route('admin.cronofy.sessions.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('Cronofy.group_session.message.something_wrong'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.cronofy.sessions.edit', $cronofySchedule->id)->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function getSubCategories(Request $request)
    {
        if (!access()->allow('manage-sessions')) {
            return response()->json([
                'message' => trans('Cronofy.group_session.message.unauthorized_access'),
            ], 422);
        }
        try {
            $user = auth()->user();
            $role = getUserRole($user);
            if ($role->slug == 'wellbeing_specialist') {
                $subcategories = $user->userservices()->select('service_sub_categories.name', 'service_sub_categories.id')->where('service_sub_categories.service_id', $request->service)->get()->pluck('name', 'id')->toArray();
            } else {
                $company       = $user->company()->first();
                $subcategories = ServiceSubCategory::join('services', 'services.id', '=', 'service_sub_categories.service_id')
                    ->leftJoin('digital_therapy_services', 'digital_therapy_services.service_id', '=', 'services.id')
                    ->leftJoin('users_services', 'users_services.user_id', '=', 'digital_therapy_services.ws_id');
                if ($role->group == 'company') {
                    $subcategories->where('digital_therapy_services.company_id', $company->id);
                }
                $subcategories = $subcategories->where('services.id', $request->service)
                    ->select('service_sub_categories.name', 'service_sub_categories.id')
                    ->get()
                    ->pluck('name', 'id')->toArray();
            }
            $servicesData     = Service::where('id', $request->service)->select('services.is_counselling')->first();
            $data['response'] = [
                'result'                => false,
                'serviceIsCounsessling' => (!empty($servicesData) && $servicesData->is_counselling == 1) ? 1 : 0,
            ];

            if (!empty($subcategories)) {
                $data['response'] = [
                    'result'                => true,
                    'subcategory'           => $subcategories,
                    'serviceIsCounsessling' => (!empty($servicesData) && $servicesData->is_counselling == 1) ? 1 : 0,
                ];
            }

            return $data;
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function getWSUsers(Request $request)
    {
        if (!access()->allow('create-sessions')) {
            return response()->json([
                'message' => trans('Cronofy.group_session.message.unauthorized_access'),
            ], 422);
        }
        try {
            $user                   = auth()->user();
            $company                = $user->company->first();
            $slotsData              = "";
            $getWellbeingSpecialist = User::select(\DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS name"), 'users.id')
                ->leftJoin('user_profile', 'user_profile.user_id', '=', 'users.id')
                ->leftJoin('ws_user', 'ws_user.user_id', '=', 'users.id')
                ->leftJoin('digital_therapy_services', 'digital_therapy_services.ws_id', '=', 'users.id')
                ->leftJoin('users_services', 'users_services.user_id', '=', 'users.id')
                ->whereNull('users.deleted_at')
                ->where('users_services.service_id', $request->serviceSubCategory)
                ->where('ws_user.is_cronofy', true)
                ->whereIn('users.availability_status', [1, 2])
                ->where('digital_therapy_services.company_id', $company->id)
                ->distinct()
                ->get();

            if ($getWellbeingSpecialist->isNotEmpty()) {
                $w = 800;
                $h = 800;
                $getWellbeingSpecialist->each(function ($ws) use (&$slotsData, $w, $h) {
                    $wsId    = $ws->id;
                    $wsName  = $ws->name;
                    $wsImage = $ws->getMediaData('logo', ['w' => $w, 'h' => $h, 'zc' => 3]);
                    $slotsData .= view('admin.cronofy.groupsession.ws-block-section', [
                        'wsId'       => $wsId,
                        'wsName'     => $wsName,
                        'wsImage'    => $wsImage['url'],
                        'selectedWS' => "",
                        'edit'       => false,
                    ])->render();
                });
            }

            return $slotsData;
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function getWSUsersList(Request $request)
    {
        if (!access()->allow('manage-sessions')) {
            return response()->json([
                'message' => trans('Cronofy.group_session.message.unauthorized_access'),
            ], 422);
        }
        try {
            $user      = auth()->user();
            $role      = getUserRole($user);
            if ($role->group == 'company') {
                $company = $user->company->first();
            }
            $getWellbeingSpecialist = User::select(\DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS name"), 'users.id')
                ->leftJoin('user_profile', 'user_profile.user_id', '=', 'users.id')
                ->leftJoin('ws_user', 'ws_user.user_id', '=', 'users.id')
                ->leftJoin('digital_therapy_services', 'digital_therapy_services.ws_id', '=', 'users.id')
                ->leftJoin('users_services', 'users_services.user_id', '=', 'users.id')
                ->whereNull('users.deleted_at')
                ->where('users_services.service_id', $request->serviceSubCategory)
                ->where('ws_user.is_cronofy', true);
            if ($role->group == 'company') {
                $getWellbeingSpecialist = $getWellbeingSpecialist->where('digital_therapy_services.company_id', $company->id);
            }
            $getWellbeingSpecialist = $getWellbeingSpecialist->distinct()
                ->get()
                ->pluck('name', 'id')
                ->toArray();

            $data['response'] = [
                'result' => false,
            ];

            if (!empty($getWellbeingSpecialist)) {
                $data['response'] = [
                    'result' => true,
                    'wsUser' => $getWellbeingSpecialist,
                ];
            }

            return $data;
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * @param Request $request
     * @param Company $company
     * @return View
     */
    public function getUsers(Request $request, Company $company)
    {
        if (!access()->allow('create-sessions')) {
            return response()->json([
                'message' => trans('Cronofy.group_session.message.unauthorized_access'),
            ], 422);
        }
        try {
            if (!empty($request) && !empty($request->locationId)) {
                $locationId = $request->locationId;
            }
            $userObject = $this->getAllCompaniesGroupType($company, $locationId ?? null);

            $data['response'] = [
                'result' => false,
            ];

            if (!empty($userObject)) {
                $data['response'] = [
                    'result'    => true,
                    'companies' => $userObject,
                ];
            }
            return $data;
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * Create Event Slot in WS calendar
     *
     * @return array
     **/
    public function createEventSlot(Request $request)
    {
        if (!access()->allow('create-sessions')) {
            return response()->json([
                'message' => trans('Cronofy.group_session.message.unauthorized_access'),
            ], 422);
        }
        try {
            \DB::beginTransaction();
            $user               = auth()->user();
            $role               = getUserRole($user);
            $utcNow             = \now(config('app.timezone'))->toDateTimeString();
            $newScheduleDetails = CronofySchedule::where('id', $request->scheduleId)->first();
            $notificationTag    = "group-session-invite";
            $isRescheduled      = false;
            $userTimeZone       = $user->timezone;
            if ($request->reschedule) {
                $isRescheduled   = true;
                $notificationTag = "group-session-reschedule";
                CronofySchedule::where('event_id', $request->eventId)
                    ->where('scheduling_id', $request->schedulingId)
                    ->whereNotIn('id', [$request->scheduleId])
                    ->update([
                        'cancelled_at' => $utcNow,
                        'updated_at'   => $utcNow,
                        'status'       => 'rescheduled',
                    ]);
                $oldScheduleDetails = CronofySchedule::where('event_id', $request->eventId)
                    ->where('scheduling_id', $request->schedulingId)
                    ->whereNotIn('id', [$request->scheduleId])->first();
            }

            $inviteUsers = scheduleUsers::leftjoin('users', 'users.id', '=', 'session_group_users.user_id')->where('session_group_users.session_id', $request->scheduleId)
                ->select(\DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS display_name"), 'users.email')
                ->get()
                ->toArray();

            $this->cronofyRepository->createEvent($request->all(), $inviteUsers);
            $startDate            = date("Y-m-d H:i:s", strtotime($request->notification['notification']['slot']['start']));
            $endDate              = date("Y-m-d H:i:s", strtotime($request->notification['notification']['slot']['end']));
            $bookingTimezone      = $request->notification['notification']['tzid'];
            $meta                 = ($isRescheduled ? $oldScheduleDetails->meta : $newScheduleDetails->meta);
            $uid                  = (!empty($meta) ? $meta->uid : date('Ymd') . 'T' . date('His') . '-' . rand() . '@zevo.app');
            $meta                 = [
                "wellbeing_specialist" => $newScheduleDetails->ws_id,
                "timezone"             => $userTimeZone,
                "uid"                  => $uid,
            ];

            $records = CronofySchedule::where('id', $request->scheduleId)
                ->update([
                    'start_time' => $startDate,
                    'end_time'   => $endDate,
                    'updated_at' => $utcNow,
                    'meta'       => $meta,
                    'timezone'   => $bookingTimezone,
                    'status'     => 'booked',
                ]);

            if ($records) {
                $appName               = config('app.name');
                $duration              = Carbon::parse($endDate)->diffInMinutes($startDate);
                $eventDate             = Carbon::parse("{$startDate}", config('app.timezone'))->setTimezone($userTimeZone)->format('M d, Y');
                $eventTime             = Carbon::parse("{$startDate}", config('app.timezone'))->setTimezone($userTimeZone)->format('h:i A');
                $company               = Company::where('id', $newScheduleDetails->company_id)->first();
                $companyDigitalTherapy = $company->digitalTherapy()->first();

                // Send session booked email to ws
                $sessionBookedBy = 'wellbeing_specialist';
                if ($role->slug == 'company_admin' && $role->group == 'company') {
                    $sessionBookedBy = 'company_admin';
                }
                $wsData         = User::where('id', $newScheduleDetails->ws_id)->first();
                $wsTimeZone     = !empty($wsData->timezone) ? $wsData->timezone : config('app.timezone');
                $wsEventDate    = Carbon::parse("{$startDate}", config('app.timezone'))->setTimezone($wsTimeZone)->format('M d, Y');
                $wsEventTime    = Carbon::parse("{$startDate}", config('app.timezone'))->setTimezone($wsTimeZone)->format('h:i A');
              
                $sessionDataToWs = [
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

                event(new SendSessionBookedEvent($sessionDataToWs));

                $notificationUser = User::select('users.*', 'user_notification_settings.flag AS notification_flag')
                    ->leftJoin('user_notification_settings', function ($join) {
                        $join->on('user_notification_settings.user_id', '=', 'users.id')
                            ->where('user_notification_settings.flag', '=', 1)
                            ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['digital-therapy', 'all']);
                    })
                    ->whereRaw('users.id IN ( SELECT user_id FROM `session_group_users` WHERE session_id = ? )', [$newScheduleDetails->id])
                    ->where('is_blocked', false)
                    ->groupBy('users.id')
                    ->limit(1)
                    ->get()
                    ->toArray();

                // dispatch job to send push notification to all user when group session created
                \dispatch(new SendGroupSessionPushNotification($newScheduleDetails, $notificationTag, $notificationUser, ''));

                $sessionNotes = (isset($newScheduleDetails) && !empty($newScheduleDetails->notes)) ? $newScheduleDetails->notes : null;
                if ($isRescheduled) {
                    $sessionNotes = (isset($oldScheduleDetails) && !empty($oldScheduleDetails->notes)) ? $oldScheduleDetails->notes : null;
                }

                
                // Send session booked email to users
                $sessionDataUsers = [
                    'company'       => (!empty($company) ? $company->id : null),
                    'wsFirstName'   => $newScheduleDetails->wellbeingSpecialist->first_name,
                    'wsName'        => $newScheduleDetails->wellbeingSpecialist->full_name,
                    'serviceName'   => $newScheduleDetails->name,
                    'eventDate'     => $eventDate,
                    'eventTime'     => $eventTime,
                    'duration'      => $duration,
                    'location'      => $newScheduleDetails->location,
                    'to'            => 'user',
                    'sessionId'     => $newScheduleDetails->id,
                    'isGroup'       => $newScheduleDetails->is_group,
                    'isRescheduled' => $isRescheduled,
                    'isOnline'      => (!empty($companyDigitalTherapy) && $companyDigitalTherapy->dt_is_online),
                    'notes'         => isset($sessionNotes) ? $sessionNotes : null,
                    'bookedBy'      => $sessionBookedBy,
                    'companyName'   => (!empty($company) ? $company->name : null),
                ];
                $sequence = 0;
                foreach ($notificationUser as $nUser) {

                    $userEventDate    = Carbon::parse("{$startDate}", config('app.timezone'))->setTimezone($nUser['timezone'])->format('M d, Y');
                    $userEventTime    = Carbon::parse("{$startDate}", config('app.timezone'))->setTimezone($nUser['timezone'])->format('h:i A');
                    $sequenceLog = $newScheduleDetails->inviteSequence()->select('users.id')->where('user_id', $nUser['id'])->first();
                    $sequence    = 0;
                    if (is_null($sequenceLog)) {
                        // record not exist adding
                        $newScheduleDetails->inviteSequence()->attach([$nUser['id']]);
                        $sequence = 0;
                    } else {
                        // record exist updating sequence
                        $sequence = ($sequenceLog->pivot->sequence + 1);
                        $sequenceLog->pivot->update([
                            'sequence' => $sequence,
                        ]);
                    }

                    $sessionDataUsers['email']         = $nUser['email'];
                    $sessionDataUsers['userFirstName'] = $nUser['first_name'];
                    $sessionDataUsers['userName']      = $nUser['first_name'] . ' ' . $nUser['last_name'];
                    $sessionDataUsers['eventDate']     = $userEventDate;
                    $sessionDataUsers['eventTime']     = $userEventTime;

                    $sessionDataUsers['iCal']          = generateiCal([
                        'uid'         => $uid,
                        'appName'     => $appName,
                        'inviteTitle' => trans('Cronofy.ical.title', [
                            'user_name' => $nUser['first_name'] . ' ' . $nUser['last_name'],
                            'wbs_name'  => $newScheduleDetails->wellbeingSpecialist->full_name,
                        ]),
                        'description' => trans('Cronofy.ical.description', [
                            'service_name' => $newScheduleDetails->name,
                            'wbs_name'     => $newScheduleDetails->wellbeingSpecialist->full_name,
                            'session_date' => $eventDate,
                            'session_time' => $eventTime,
                            'whereby_link' => $newScheduleDetails->location,
                        ]),
                        'timezone'    => $nUser['timezone'],
                        'today'       => Carbon::parse($utcNow)->format('Ymd\THis\Z'),
                        'startTime'   => Carbon::parse($startDate)->format('Ymd\THis\Z'),
                        'endTime'     => Carbon::parse($endDate)->format('Ymd\THis\Z'),
                        'orgName'     => $newScheduleDetails->wellbeingSpecialist->full_name,
                        'orgEamil'    => $newScheduleDetails->wellbeingSpecialist->email,
                        'userEmail'   => $nUser['email'],
                        'sequence'    => $sequence,
                    ]);

                    event(new SendSessionBookedEvent($sessionDataUsers));
                }

                \DB::commit();
                $messageData = [
                    'data'   => trans('Cronofy.group_session.message.data_update_success'),
                    'status' => 1,
                ];
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('Cronofy.group_session.message.something_wrong'),
                    'status' => 0,
                ];
            }

            return $messageData;
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * Sending mail when exception run.
     *
     * @return array
     **/
    public function cronofyException(Request $request)
    {
        try {
            $company      = [];
            $user         = [];
            $errorMessage = null;
            if (isset($request->companyId)) {
                $company = Company::where('id', $request->companyId)->first();
            }

            if (isset($request->wsId)) {
                $user = User::where('id', $request->wsId)->first();
            }

            if (isset($request->errorMessage)) {
                $errorMessage = (string) strip_tags($request->errorMessage);
            }

            $data = [
                'type'         => 'Group Session Booking',
                'message'      => $errorMessage,
                'company'      => $company,
                'wsDetails'    => $user,
                'errorDetails' => [],
            ];
            // Send email when trow error while digital therapy any operation
            event(new DigitaltherapyExceptionHandlingEvent($data));

            return $data;
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * Get All Companies Group Type
     *
     * @return array
     **/
    public function getAllCompaniesGroupType($company, $locationId = null)
    {
        $companyLocation = CompanyLocation::where('company_id', $company->id)->select('id', 'name');
        if (!empty($locationId)) {
            $companyLocation = $companyLocation->where('id', $locationId);
        }

        $companyLocation = $companyLocation->get()->toArray();
        $locationArray   = [];

        foreach ($companyLocation as $locationItem) {
            $departmentArray   = [];
            $departmentRecords = DepartmentLocation::join('departments', 'departments.id', '=', 'department_location.department_id')->where('department_location.company_location_id', $locationItem['id'])->where('department_location.company_id', $company->id)->select('departments.id', 'departments.name')->get()->toArray();

            foreach ($departmentRecords as $departmentItem) {
                $teamArray   = [];
                $teamRecords = TeamLocation::join('teams', 'teams.id', '=', 'team_location.team_id')->where('team_location.department_id', $departmentItem['id'])->where('team_location.company_id', $company->id)->where('team_location.company_location_id', $locationItem['id'])->select('teams.id', 'teams.name')->get()->toArray();

                foreach ($teamRecords as $teamItem) {
                    $usersArray  = [];
                    $userRecords = User::join('user_team', 'user_team.user_id', '=', 'users.id')
                        ->where('user_team.team_id', $teamItem['id'])->where('user_team.department_id', $departmentItem['id'])->where('user_team.company_id', $company->id)->select('users.id', 'users.first_name', 'users.last_name', 'users.email')->get()->toArray();

                    foreach ($userRecords as $userItem) {
                        $usersArray[] = [
                            'id'    => $userItem['id'],
                            'name'  => $userItem['first_name'] . ' ' . $userItem['last_name'],
                            'email' => $userItem['email'],
                        ];
                    }

                    if (!empty($usersArray)) {
                        $teamArray[] = [
                            'name' => $teamItem['name'],
                            'user' => $usersArray,
                        ];
                    }
                }

                if (!empty($teamArray)) {
                    $departmentArray[] = [
                        'departmentName' => $departmentItem['name'],
                        'team'           => $teamArray,
                    ];
                }
            }

            $locationArray[] = [
                'locationName' => $locationItem['name'],
                'department'   => $departmentArray,
            ];
        }

        return [
            'companyName' => $company->name,
            'location'    => $locationArray,
        ];
    }

    /**
     * Request session email logs html and display email logs html
     * @param Request $request
     * @param CronofySchedule $cronofySchedule
     * @return RedirectResponse
     */
    public function emailLogs(Request $request, CronofySchedule $cronofySchedule)
    {
        $role       = getUserRole();
        $user       = auth::user();
        if ($role->slug == 'wellbeing_specialist') {
            $wsDetails  = $user->wsuser()->first();
        }
        
        if (!access()->allow('view-sessions') || ($role->slug == 'wellbeing_specialist' && (!empty($wsDetails) && $wsDetails->is_cronofy && $wsDetails->responsibilities == 2))) {
            abort(403);
        }

        try {
            $data                        = $this->cronofySchedule->getSessionDetails($cronofySchedule);
            $data['role']                = $role;
            $data['cronofySchedule']     = $cronofySchedule;
            $data['reasons']             = config('zevolifesettings.session_email_reasons');
            $data['ga_title']            = trans('page_title.calendly.view');
            return \view('admin.cronofy.sessionlist.emaillogs', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('Cronofy.session_list.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.cronofy.sessions.index')->with('message', $messageData);
        }
    }

    /**
     * Get the session email logs data
     * @param Request $request
     * @param CronofySchedule $cronofySchedule
     * @return RedirectResponse
     */
    public function getEmailLogsData(Request $request, CronofySchedule $cronofySchedule)
    {
        if (!access()->allow('view-sessions')) {
            return response()->json([
                'message' => trans('Cronofy.client_list.messages.unauthorized_access'),
            ], 422);
        }
        try {
            return $this->cronofySessionEmailLogs->getEmailLogs($cronofySchedule, $request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('Cronofy.session_list.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * Send session emails to user with reason
     * @param CronofySchedule $cronofySchedule
     * @param EditSessionRequest $request
     * @return JsonResponse
     */
    public function sendSessionEmail(CronofySchedule $cronofySchedule, EditSessionRequest $request)
    {
        if (!access()->allow('view-sessions')) {
            return redirect()->back()->with('message', [
                'data'   => trans('Cronofy.session_list.messages.unauthorized_access'),
                'status' => 0,
            ]);
        }
        try {
            \DB::beginTransaction();
            $insertLog = [
                'reason'        => $request['reason'],
                'email_message' => $request['email_message'],
            ];
            $data = $this->cronofySessionEmailLogs->storeEmailLogs($insertLog, $cronofySchedule);
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('Cronofy.session_details.messages.email_success'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.cronofy.sessions.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('Cronofy.session_details.messages.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.cronofy.sessions.index')->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            return response()->json([
                'status'  => 0,
                'message' => trans('labels.common_title.something_wrong'),
            ], 500);
        }
    }

    /**
     * Upload multiple attachments
     * @param CronofySchedule $cronofySchedule
     * @param AddBulkSessionAttachmentsRequest $request
     *
     * @return RedirectResponse
     */
    public function storeAttachments(CronofySessionAttachments $sessionAttachment, CronofySchedule $cronofySchedule, AddBulkSessionAttachmentsRequest $request)
    {
        $role = getUserRole();
        if (!access()->allow('view-sessions') && $role->slug != 'wellbeing_specialist') {
            return redirect()->back()->with('message', [
                'data'   => trans('Cronofy.session_list.messages.unauthorized_access'),
                'status' => 0,
            ]);
        }
        try {
            \DB::beginTransaction();
            $data = $this->sessionAttachment->storeAttachments($cronofySchedule, $request->all());
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('Cronofy.session_details.attachments.messages.uploaded'),
                    'status' => 1,
                ];
                \Session::put('message', $messageData);
                return response()->json($messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('Cronofy.session_details.attachments.messages.something_wrong_try_again'),
                    'status' => 0,
                ];
                return response()->json($messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('Cronofy.session_details.attachments.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * To get attachments of session
     *
     * @param CronofySchedule $cronofySchedule
     * @param Request $request
     * @return Json
     */
    public function getSessionAttachments(CronofySchedule $cronofySchedule, Request $request)
    {
        if (!access()->allow('view-sessions')) {
            return response()->json([
                'message' => trans('Cronofy.client_list.messages.unauthorized_access'),
            ], 422);
        }

        try {
            return $this->sessionAttachment->getTableData($cronofySchedule, $request->all());
        } catch (\Exception $exception) {
            report($exception);
            return response()->json([
                'data'   => trans('Cronofy.client_list.messages.something_wrong_try_again'),
                'status' => 0,
            ]);
        }
    }

    /**
     * Delete the attachments
     * @param  CronofySessionAttachments $sessionAttachment
     *
     * @return View
     */
    public function deleteSessionAttachment(CronofySessionAttachments $sessionAttachment)
    {
        try {
            return $sessionAttachment->deleteRecord();
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * To download attachments of session
     *
     * @param CronofySchedule $cronofySchedule
     * @param User $client
     * @return Json
     */
    public function downloadSessionAttachments(CronofySessionAttachments $sessionAttachment, Request $request)
    {
        if (!access()->allow('view-sessions')) {
            return response()->json([
                'message' => trans('Cronofy.client_list.messages.unauthorized_access'),
            ], 422);
        }
        try {
            $fileUrl  = $sessionAttachment->getFirstMediaUrl('attachment');
            $fileName = $sessionAttachment->getFirstMedia('attachment')->name;
            header("Content-disposition:attachment; filename=$fileName");
            readfile($fileUrl);
        } catch (\Exception $exception) {
            report($exception);
            return response()->json([
                'data'   => trans('Cronofy.client_list.messages.something_wrong_try_again'),
                'status' => 0,
            ]);
        }
    }

    /**
     * @param Request $request
     * @param Company $company
     * @return View
     */
    public function getCompanyLocations(Request $request, Company $company)
    {
        if (!access()->allow('create-sessions')) {
            return response()->json([
                'message' => trans('Cronofy.group_session.message.unauthorized_access'),
            ], 422);
        }
        try {
            $user                  = auth()->user();
            $role                  = getUserRole($user);
            $digitalTherapyDetails = $company->digitalTherapy()->first();
            $locations             = [];
            $isLocation            = false;
            if (!empty($digitalTherapyDetails)) {
                $setHoursBy        = $digitalTherapyDetails->set_hours_by;
                $setAvailabilityBy = $digitalTherapyDetails->set_availability_by;

                if ($setHoursBy == 2 && $setAvailabilityBy == 1) {
                    //Location General Data
                    $locations = $company->digitalTherapySlots()->select('company_locations.id', 'company_locations.name')
                        ->leftjoin('company_locations', 'company_locations.id', '=', 'digital_therapy_slots.location_id')
                        ->where('digital_therapy_slots.company_id', $company->id);
                    if ($role->slug == 'wellbeing_specialist') {
                        $isLocation = true;
                        $locations->whereRaw('FIND_IN_SET(?, digital_therapy_slots.ws_id)', [$user->id]);
                    }
                    $locations = $locations->whereNotNull('digital_therapy_slots.location_id')
                        ->pluck('company_locations.name', 'company_locations.id')
                        ->toArray();
                } else if ($setHoursBy == 2 && $setAvailabilityBy == 2) {
                    // Location Specific data
                    $locations = $company->digitalTherapySpecificSlots()->select('company_locations.id', 'company_locations.name')
                        ->leftjoin('company_locations', 'company_locations.id', '=', 'digital_therapy_specific.location_id')
                        ->where('digital_therapy_specific.company_id', $company->id);
                    if ($role->slug == 'wellbeing_specialist') {
                        $isLocation = true;
                        $locations->where('digital_therapy_specific.ws_id', $user->id);
                    }
                    $locations = $locations->whereNotNull('digital_therapy_specific.location_id')
                        ->pluck('company_locations.name', 'company_locations.id')
                        ->toArray();
                }
            }

            $data['response'] = [
                'result'     => (!empty($locations)),
                'isLocation' => $isLocation,
                'locations'  => (!empty($locations)) ? $locations : [],
            ];

            return $data;
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

}
