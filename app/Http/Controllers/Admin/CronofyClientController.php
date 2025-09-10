<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateHealthReferralRequest;
use App\Http\Requests\Admin\EditClientNoteRequest;
use App\Http\Requests\Admin\EditSessionRequest;
use App\Http\Requests\Admin\SendEmailForAccessKinInfoRequest;
use App\Models\Company;
use App\Models\CompanyLocation;
use App\Models\ConsentFormLogs;
use App\Models\CronofyCalendar;
use App\Models\CronofySchedule;
use App\Models\CronofySessionAttachments;
use App\Models\OccupationalHealthReferral;
use App\Models\SessionUserNotes;
use App\Models\User;
use App\Models\WsClientNote;
use App\Models\AdminAlert;
use App\Jobs\AdminAlertJob;
use App\Repositories\CronofyRepository;
use App\Traits\PaginationTrait;
use Breadcrumbs;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class CronofyClientController
 *
 * @package App\Http\Controllers\Admin
 */
class CronofyClientController extends Controller
{
    use PaginationTrait;

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
     * variable to store the consent form id model object
     * @var consentFormLogs $consentFormLogs
     */
    private $consentFormLogs;

    /**
     * variable to store the Cronofy session emails model object
     * @var CronofySessionAttachments $sessionAttachment
     */
    private $sessionAttachment;

    /**
     * contructor to initialize model object
     */
    public function __construct(CronofyRepository $cronofyRepository, CronofyCalendar $cronofyCalendar, CronofySchedule $cronofySchedule, WsClientNote $wsClientNote, ConsentFormLogs $consentFormLogs, CronofySessionAttachments $sessionAttachment)
    {
        $this->cronofyRepository = $cronofyRepository;
        $this->cronofyCalendar   = $cronofyCalendar;
        $this->cronofySchedule   = $cronofySchedule;
        $this->wsClientNote      = $wsClientNote;
        $this->consentFormLogs   = $consentFormLogs;
        $this->sessionAttachment = $sessionAttachment;
        $this->bindBreadcrumbs();
    }

    /**
     * bind breadcrumbs of course module
     */
    private function bindBreadcrumbs()
    {
        Breadcrumbs::for ('cronofy.clientlist.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Clients');
        });
        Breadcrumbs::for ('cronofy.clientlist.details', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('Cronofy.client_list.title.index'), route('admin.cronofy.clientlist.index'));
            $trail->push(trans('Cronofy.client_list.title.details'));
        });

        Breadcrumbs::for ('cronofy.clientlist.health-referral', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('Cronofy.client_list.title.index'), route('admin.cronofy.clientlist.index'));
            $trail->push(trans('Cronofy.client_list.title.health_referral'));
        });
    }

    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $user             = auth()->user();
        $role             = getUserRole($user);
        $loginemail       = ($user->email ?? "");
        if ($role->slug == 'wellbeing_specialist') {
            $wsDetails        = $user->wsuser()->first();
        }
        if (!access()->allow('manage-clients') || ($role->slug == 'wellbeing_specialist' && (!empty($wsDetails) && $wsDetails->is_cronofy && $wsDetails->responsibilities == 2))) {
            abort(403);
        }
        try {
            $companies = Company::select('companies.id', 'companies.name')->leftJoin('cp_company_plans', 'companies.id', '=', 'cp_company_plans.company_id')
                ->join('cp_plan', 'cp_plan.id', '=', 'cp_company_plans.plan_id')
                ->join('cp_plan_features', 'cp_plan_features.plan_id', '=', 'cp_plan.id')
                ->join('cp_features', 'cp_features.id', '=', 'cp_plan_features.feature_id');
            $companies = $companies->where(function ($q) {
                $q->where('cp_features.slug', 'digital-therapy')
                    ->orWhere('cp_features.slug', 'eap');
            })->groupBy('companies.id');
            $companies = $companies->pluck('name', 'id')->toArray();

            $companyLocation = CompanyLocation::select(
                'company_locations.id',
                'company_locations.name',
            )
                ->groupBy('company_locations.id');
            if (!empty(Request()->get('company'))) {
                $companyLocation->where('company_locations.company_id', Request()->get('company'));
            }
            $companyLocation = $companyLocation->get()
                ->pluck('name', 'id')->toArray();

            $data = [
                'companies'              => $companies ?? [],
                'role'                   => $role->slug,
                'companyLocation'        => $companyLocation,
                'getWellbeingSpecialist' => [],
                'pagination'             => config('zevolifesettings.datatable.pagination.long'),
                'ga_title'               => trans('page_title.clientlist.index'),
                'loginemail'             => $loginemail,
            ];

            if ($role->slug == 'super_admin') {
                $data['getWellbeingSpecialist'] = User::select(\DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS name"), 'users.id')
                    ->leftJoin('ws_user', 'ws_user.user_id', '=', 'users.id')
                    ->where('ws_user.is_cronofy', true)
                    ->distinct()
                    ->get()->pluck('name', 'id')->toArray();
            }

            return \view('admin.cronofy.clientlist.index', $data);
        } catch (\Exception $exception) {
            abort(500);
        }
    }

    /**
     * To get list of clients
     *
     * @param Request $request
     * @return Json
     */
    public function getClients(Request $request)
    {
        if (!access()->allow('manage-clients')) {
            return response()->json([
                'message' => trans('Cronofy.client_list.messages.unauthorized_access'),
            ], 422);
        }
        try {
            return $this->cronofySchedule->getTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            return response()->json([
                'data'   => trans('Cronofy.client_list.messages.something_wrong_try_again'),
                'status' => 0,
            ]);
        }
    }

    /**
     * To get list of clients
     *
     * @param Request $request
     * @return Json
     */
    public function exportClient(Request $request)
    {
        if (!access()->allow('manage-clients')) {
            return response()->json([
                'message' => trans('Cronofy.client_list.messages.unauthorized_access'),
            ], 422);
        }

        try {
            \DB::beginTransaction();
            $data = $this->cronofySchedule->exportClientData($request->all());
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('Cronofy.client_list.details.modal.export.report_running_background'),
                    'status' => 1,
                ];
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('Cronofy.client_list.messages.no_data_exists'),
                    'status' => 0,
                ];
            }
            return $messageData;
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('Cronofy.client_list.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * To get details of clients
     *
     * @param CronofySchedule $cronofySchedule
     * @return view
     */
    public function clientDetails(CronofySchedule $cronofySchedule, Request $request)
    {
        if (!access()->allow('view-clients')) {
            abort(403);
        }

        try {
            $user             = auth()->user();
            $role             = getUserRole($user);
            $appTimezone       = config('app.timezone');
            $nowInUTC          = now($appTimezone);
            $timezone          = (!empty($user->timezone) ? $user->timezone : $appTimezone);
            $role              = getUserRole($user);
            $now               = now($timezone)->toDateTimeString();
            $client            = $cronofySchedule->user()->select('id', 'first_name', 'last_name', 'email')->first();
            $isConsent         = false;
            $isConsentFormSent = false;
            // if client is empty return 403
            if (empty($client)) {
                return view('errors.401');
            }

            $clientProfile  = $client->profile;
            $clientCompany  = $client->company()->select('companies.id', 'companies.name')->first();
            $digitalTherapy = $clientCompany->digitalTherapy()->first();

            if (!empty($digitalTherapy)) {
                $isConsent = $digitalTherapy->consent;
            }
            // completed sessions count
            $completedCount = CronofySchedule::select(\DB::raw('COUNT(cronofy_schedule.id)'))
                ->join('session_group_users', 'session_group_users.session_id', '=', 'cronofy_schedule.id')
                ->join('users', 'users.id', '=', 'session_group_users.user_id')
                ->join('users as ws', 'ws.id', '=', 'cronofy_schedule.ws_id')
                ->where(function ($query) use ($timezone, $now) {
                    $query
                    ->whereRaw("CONVERT_TZ(cronofy_schedule.end_time, ?, ?) <= ?",[
                        'UTC',$timezone,$now
                    ])
                    ->orWhere('cronofy_schedule.status', 'completed');
                })
                ->whereNull('cronofy_schedule.cancelled_at')
                ->whereNotIn('cronofy_schedule.status', ['canceled', 'rescheduled', 'open', 'short_canceled'])
                ->where('cronofy_schedule.no_show', 'No')
                ->whereNull('users.deleted_at')
                ->whereNull('ws.deleted_at'); 
                if ($role->slug == 'wellbeing_specialist') {
                    $completedCount = $completedCount->where('cronofy_schedule.ws_id', $user->id);
                }
                $completedCount = $completedCount->where('session_group_users.user_id', $cronofySchedule->user_id)
                    ->groupBy('session_group_users.user_id')
                    ->count('cronofy_schedule.id');

            // ongoing sessions count
            $ongoingCount = CronofySchedule::select(\DB::raw('COUNT(cronofy_schedule.id)'))
                ->join('session_group_users', 'session_group_users.session_id', '=', 'cronofy_schedule.id')
                ->join('users', 'users.id', '=', 'session_group_users.user_id')
                ->join('users as ws', 'ws.id', '=', 'cronofy_schedule.ws_id')
                ->whereRaw('? BETWEEN cronofy_schedule.start_time AND cronofy_schedule.end_time', $nowInUTC->toDateTimeString())
                ->whereNull('cronofy_schedule.cancelled_at')
                ->where('cronofy_schedule.status', 'booked')
                ->where('cronofy_schedule.no_show', 'No')
                ->whereNull('users.deleted_at')
                ->whereNull('ws.deleted_at');
                if ($role->slug == 'wellbeing_specialist') {
                    $ongoingCount = $ongoingCount->where('cronofy_schedule.ws_id', $user->id);
                }
                $ongoingCount = $ongoingCount->where('session_group_users.user_id', $cronofySchedule->user_id)
                    ->groupBy('session_group_users.user_id')
                    ->count('cronofy_schedule.id');

            // cancelled sessions count
            $cancelledCount = CronofySchedule::select(\DB::raw('COUNT(cronofy_schedule.id)'))
            ->select(\DB::raw('COUNT(cronofy_schedule.id)'))
            ->join('session_group_users', 'session_group_users.session_id', '=', 'cronofy_schedule.id')
            ->join('users', 'users.id', '=', 'session_group_users.user_id')
            ->join('users as ws', 'ws.id', '=', 'cronofy_schedule.ws_id')
            ->whereNotNull('cronofy_schedule.cancelled_at')
            ->where(function ($query) {
                $query->where('cronofy_schedule.status', 'canceled')
                    ->orWhere('cronofy_schedule.status', 'short_canceled')
                    ->orWhere('cronofy_schedule.status', 'rescheduled');
            })
            ->whereNull('users.deleted_at')
            ->whereNull('ws.deleted_at')
            ->where('session_group_users.user_id', $cronofySchedule->user_id);
            if ($role->slug == 'wellbeing_specialist') {
                $cancelledCount = $cancelledCount->where('cronofy_schedule.ws_id', $user->id);
            }
            $cancelledCount = $cancelledCount->groupBy('session_group_users.user_id')
                ->count('cronofy_schedule.id');

            // Ws client notes which are displaying on client details page
            $wsClientNotes = WsClientNote::leftJoin('users', 'ws_client_notes.user_id', '=', 'users.id')
                ->leftJoin('cronofy_schedule', 'ws_client_notes.cronofy_schedule_id', '=', 'cronofy_schedule.id')
                ->select(
                    'ws_client_notes.id',
                    'ws_client_notes.comment',
                    'ws_client_notes.user_id',
                    \DB::raw("CONCAT(users.first_name, ' ', users.last_name) as notesAddedBy"),
                    \DB::raw("'wsClientsTable' as fromTable"),
                )->selectRaw(
                    "CONVERT_TZ(ws_client_notes.created_at, ?, ?) AS created_at"
                ,[$appTimezone,$timezone])
                ->where(function ($query) use ($cronofySchedule) {
                    $query->where('cronofy_schedule.user_id', $cronofySchedule->user_id)
                        ->orWhereRaw("ws_client_notes.cronofy_schedule_id IN (SELECT session_id FROM `session_group_users` WHERE `user_id` = ?)", [$cronofySchedule->user_id]);
                })
                ->whereNull('users.deleted_at');
            $wsClientNotes = $wsClientNotes->get()
                ->toArray();

            $scheduleNotes = $cronofySchedule->select(
                \DB::raw("cronofy_schedule.id as id"),
                'cronofy_schedule.notes as comment',
                \DB::raw("cronofy_schedule.ws_id as user_id"),
                \DB::raw("CONCAT(users.first_name, ' ', users.last_name) as notesAddedBy"),
                \DB::raw("'scheduleTable' as fromTable"),
            )->selectRaw(
                "CONVERT_TZ(cronofy_schedule.updated_at, ?, ?) AS created_at"
            ,[$appTimezone,$timezone])
            ->leftJoin('users', 'cronofy_schedule.ws_id', '=', 'users.id')
                ->where(function ($query) use ($cronofySchedule) {
                    $query->where('cronofy_schedule.user_id', $cronofySchedule->user_id)
                        ->orWhereRaw("cronofy_schedule.id IN (SELECT session_id FROM `session_group_users` WHERE `user_id` = ?)", [$cronofySchedule->user_id]);
                })
                ->whereNull('users.deleted_at');
            $scheduleNotes = $scheduleNotes->where('cronofy_schedule.notes', '<>', '')
                ->get()
                ->toArray();
            $mergeClientSessionNotes = array_merge($wsClientNotes, $scheduleNotes);
            $wsNotes                 = CronofySchedule::hydrate($mergeClientSessionNotes)->toArray();
            $notes                   = $this->paginate($wsNotes, null, ["path" => route('admin.cronofy.clientlist.details', [$cronofySchedule->id, 'type' => 'notes'])]);
            // Old session user notes
            $sessionUserNotes = SessionUserNotes::where('user_id', $cronofySchedule->user_id)->select(
                'notes as userNote'
            )->selectRaw(
                "? as scheduleId"
            ,[$cronofySchedule->id])
            ->selectRaw(
                "CONVERT_TZ(created_at, ?, ?) AS created_at"
            ,[$appTimezone,$timezone])
            ->get()
            ->toArray();

            // Get the session user notes
            if ($role->slug == 'wellbeing_team_lead') {
                $uNotes = $cronofySchedule
                    ->select(
                        'cronofy_schedule.id as scheduleId',
                        'cronofy_schedule.user_notes as userNote'
                    )->selectRaw(
                        "CONVERT_TZ(cronofy_schedule.created_at, ?, ?) AS created_at"
                    ,[$appTimezone,$timezone])
                    ->where('cronofy_schedule.user_notes', '<>', '')
                    ->where('cronofy_schedule.user_id', $client->id)
                    ->get()
                    ->toArray();
            } else {
                $uNotes = $user->myWsClients()
                    ->select(
                        'cronofy_schedule.id as scheduleId',
                        'cronofy_schedule.user_notes as userNote'
                    )->selectRaw(
                        "CONVERT_TZ(cronofy_schedule.created_at, ?, ?) AS created_at"
                    ,[$appTimezone,$timezone])
                    ->where('cronofy_schedule.user_notes', '<>', '')
                    ->where('cronofy_schedule.user_id', $client->id)
                    ->get()
                    ->toArray();
            }
            $mergeUserNotes = array_merge($sessionUserNotes, $uNotes);
            $userNotes      = CronofySchedule::hydrate($mergeUserNotes)->toArray();
            $userNotes      = $this->paginate($userNotes, null, ["path" => route('admin.cronofy.clientlist.details', [$cronofySchedule->id, 'type' => 'usernotes'])]);

            // Check if consent form is submitted previously or not
            $nextToKinInfo      = [];
            $getConsentFormLogs = $this->consentFormLogs->where(['user_id' => $client->id, 'ws_id' => $cronofySchedule->ws_id])->get()->first();
            if (!empty($getConsentFormLogs)) {
                $isConsentFormSent = true;
                if (!empty($getConsentFormLogs['fullname'])) {
                    $nextToKinInfo['fullname'] = $getConsentFormLogs['fullname'];
                }
                if (!empty($getConsentFormLogs['contact_no'])) {
                    $nextToKinInfo['contact_no'] = $getConsentFormLogs['contact_no'];
                }
                if (!empty($getConsentFormLogs['relation'])) {
                    $nextToKinInfo['relation'] = $getConsentFormLogs['relation'];
                }
            }

            $data = [
                'cronofySchedule'              => $cronofySchedule,
                'client'                       => $client,
                'dob'                          => Carbon::parse($clientProfile->birth_date)->format(config('zevolifesettings.date_format.default_date')),
                'gender'                       => ucfirst($clientProfile->gender),
                'clientCompany'                => $clientCompany,
                'completedCount'               => $completedCount ?? 0,
                'ongoingCount'                 => $ongoingCount ?? 0,
                'cancelledCount'               => $cancelledCount ?? 0,
                'sessionStatus'                => config('zevolifesettings.calendly_session_status'),
                'pagination'                   => config('zevolifesettings.datatable.pagination.short'),
                'timezone'                     => $timezone,
                'notes'                        => $notes,
                'queryString'                  => $request->get('type'),
                'date_format'                  => config('zevolifesettings.date_format.moment_default_datetime'),
                'ga_title'                     => trans('page_title.clientlist.details'),
                'userNotes'                    => $userNotes,
                'isConsent'                    => $isConsent,
                'loginemail'                   => ($user->email ?? ""),
                'role'                         => $role,
                'isConsentFormSent'            => $isConsentFormSent,
                'nextToKinInfo'                => $nextToKinInfo,
                'wellbeingSpacialist'          => $user,
                'clientAttachmentPerPageValue' => config('zevolifesettings.datatable.pagination.clientAttachments'),
            ];

            if (!empty($getConsentFormLogs)) {
                $data['is_kin_accessed'] = $getConsentFormLogs['is_accessed'];
            }
            
            return \view('admin.cronofy.clientlist.details', $data);
        } catch (\Exception $exception) {

            abort(500);
        }
    }

    /**
     * To get sessions of clients
     *
     * @param CronofySchedule $cronofySchedule
     * @param User $client
     * @return Json
     */
    public function getClientSessions(CronofySchedule $cronofySchedule, User $client, Request $request)
    {
        if (!access()->allow('view-clients')) {
            return response()->json([
                'message' => trans('Cronofy.client_list.messages.unauthorized_access'),
            ], 422);
        }

        try {
            return $this->cronofySchedule->getClientSessions($client, $request->all());
        } catch (\Exception $exception) {
            report($exception);
            return response()->json([
                'data'   => trans('Cronofy.client_list.messages.something_wrong_try_again'),
                'status' => 0,
            ]);
        }
    }

    /**
     * Add note for client
     *
     * @param CronofySchedule $cronofySchedule
     * @param Request $request
     * @return RedirectResponse
     */
    public function addNote(CronofySchedule $cronofySchedule, Request $request)
    {
        if (!access()->allow('view-clients')) {
            return redirect()->back()->with('message', [
                'data'   => trans('Cronofy.client_list.messages.unauthorized_access'),
                'status' => 0,
            ]);
        }

        try {
            \DB::beginTransaction();
            $data = $cronofySchedule->storeNote($request->all());
            if ($data) {
                \DB::commit();
                return \Redirect::route('admin.cronofy.clientlist.details', $cronofySchedule->id)->with('message', [
                    'data'   => "Note has been added successfully",
                    'status' => 1,
                ]);
            } else {
                \DB::rollback();
                return redirect()->back()->with('message', [
                    'data'   => trans('Cronofy.client_list.messages.something_wrong_try_again'),
                    'status' => 0,
                ]);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            return redirect()->back()->with('message', [
                'data'   => trans('Cronofy.client_list.messages.something_wrong_try_again'),
                'status' => 0,
            ]);
        }
    }

    /**
     * Send consent form notification to client
     *
     * @param CronofySchedule $cronofySchedule
     * @param Request $request
     * @return RedirectResponse
     */
    public function sendConsent(CronofySchedule $cronofySchedule, Request $request)
    {
        if (!access()->allow('view-clients')) {
            return redirect()->back()->with('message', [
                'data'   => trans('Cronofy.client_list.messages.unauthorized_access'),
                'status' => 0,
            ]);
        }
        try {
            \DB::beginTransaction();
            $data = $cronofySchedule->sendConsent($request->all());

            if ($data) {
                \DB::commit();
                return \Redirect::route('admin.cronofy.clientlist.details', $cronofySchedule->id)->with('message', [
                    'data'   => "Consent form notification has been sent successfully",
                    'status' => 1,
                ]);
            } else {
                \DB::rollback();
                return redirect()->back()->with('message', [
                    'data'   => trans('Cronofy.client_list.messages.something_wrong_try_again'),
                    'status' => 0,
                ]);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            return redirect()->back()->with('message', [
                'data'   => trans('Cronofy.client_list.messages.something_wrong_try_again'),
                'status' => 0,
            ]);
        }
    }

    /**
     * Get and display note in editor
     *
     * @param Request $request
     * @param CronofySchedule $cronofySchedule
     * @return view
     */
    public function getNoteById(Request $request, CronofySchedule $cronofySchedule)
    {
        try {
            if ($request['noteFrom'] != null || (isset($request['noteFromTable']) && $request['noteFromTable'] == 'scheduleTable')) {
                $data  = $cronofySchedule->where('id', $request['id'])->get()->first();
                $notes = $data->notes;
            } else {
                $data  = $this->wsClientNote->where('id', $request['id'])->get()->first();
                $notes = $data->comment;
            }
            return response()->json([
                'note' => $notes,
            ], 200);
        } catch (\Exception $exception) {
            report($exception);
            return redirect()->back()->with('message', [
                'data'   => trans('Cronofy.client_list.messages.something_wrong_try_again'),
                'status' => 0,
            ]);
        }
    }

    /**
     * Update notes
     *
     * @param EditClientNoteRequest $request
     * @param EditSessionRequest $sessionRequest
     * @param CronofySchedule $cronofySchedule
     * @return view
     */
    public function updateNoteById(EditClientNoteRequest $request, EditSessionRequest $sessionRequest, CronofySchedule $cronofySchedule)
    {
        try {
            if ($request['noteFrom'] != null || (isset($request['noteFromTable']) && $request['noteFromTable'] == 'scheduleTable')) {
                $data = $cronofySchedule->updateSessionNotes($sessionRequest->all());
            } else {
                $data = $this->wsClientNote->updateNotes($request->all());
            }
            if ($data) {
                return \Redirect::route('admin.cronofy.clientlist.details', $request['clientId'])->with('message', [
                    'data'   => "Note has been edited successfully",
                    'status' => 1,
                ]);
            } else {
                return redirect()->back()->with('message', [
                    'data'   => trans('Cronofy.client_list.messages.something_wrong_try_again'),
                    'status' => 0,
                ]);
            }
        } catch (\Exception $exception) {
            report($exception);
            return redirect()->back()->with('message', [
                'data'   => trans('Cronofy.client_list.messages.something_wrong_try_again'),
                'status' => 0,
            ]);
        }
    }

    /**
     * delete client notes
     *
     * @param int $id
     * @param Request $request
     * @return view
     */
    public function deleteClientNote(int $id, Request $request)
    {
        try {
            return $this->wsClientNote->deleteRecord($id);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('Cronofy.client_list.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * Export wellbeing specialist and user notes
     * @param Request $request
     * @param CronofySchedule $cronofySchedule
     * @return view
     */
    public function exportNotes(Request $request, CronofySchedule $cronofySchedule)
    {
        try {
            \DB::beginTransaction();
            $data = $this->cronofySchedule->exportNotesDataEntity($request->all(), $cronofySchedule);
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('Cronofy.client_list.details.modal.export.report_running_background'),
                    'status' => 1,
                ];
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('Cronofy.client_list.messages.no_data_exists'),
                    'status' => 0,
                ];
            }
            return response()->json($messageData);
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('Cronofy.client_list.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * delete session notes
     *
     * @param int $id
     * @param Calendly $calendy
     * @return view
     */
    public function deleteSessionNote($id, CronofySchedule $cronofySchedule)
    {
        try {
            return $cronofySchedule->deleteNote($id);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('clientlist.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * To get attachments for clients
     *
     * @param CronofySchedule $cronofySchedule
     * @param Request $request
     * @return Json
     */
    public function getAttachmentsForClients(CronofySchedule $cronofySchedule, Request $request)
    {
        if (!access()->allow('view-clients')) {
            return response()->json([
                'message' => trans('Cronofy.client_list.messages.unauthorized_access'),
            ], 422);
        }

        try {
            return $this->sessionAttachment->getClientAttachmentData($cronofySchedule, $request->all());
        } catch (\Exception $exception) {
            report($exception);
            return response()->json([
                'data'   => trans('Cronofy.client_list.messages.something_wrong_try_again'),
                'status' => 0,
            ]);
        }
    }

    /**
     * To get html for occuptional health referral view
     *
     * @param CronofySchedule $cronofySchedule
     * @return view
     */
    public function addHealthReferral(CronofySchedule $cronofySchedule, Request $request, OccupationalHealthReferral $healthReferral)
    {
        $user = auth()->user();
        $role = getUserRole($user);
        if (!access()->allow('add-occupational-health-referral') || ($role->slug != 'wellbeing_team_lead')) {
            abort(403);
        }
        try {
            $appTimezone = config('app.timezone');
            $nowInUTC    = now($appTimezone);
            $timezone    = (!empty($user->timezone) ? $user->timezone : $appTimezone);
            $client      = $cronofySchedule->user()->select('id', 'first_name', 'last_name', 'email')->first();
            $now         = now($timezone)->toDateTimeString();
            // if client is empty return 403
            if (empty($client)) {
                return view('errors.401');
            }

            $clientProfile  = $client->profile;
            $clientCompany  = $client->company()->select('companies.id', 'companies.name')->first();

            // wellbeing specialists
            $wellbeingSpecilists = User::join('digital_therapy_services', 'digital_therapy_services.ws_id', '=', 'users.id')
                ->where('digital_therapy_services.company_id', $cronofySchedule->company_id)
                ->select('users.id', \DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS name"))
                ->distinct()
                ->get()->pluck('name', 'id')->toArray();

            // completed sessions count
            $completedCount = CronofySchedule::select(\DB::raw('COUNT(cronofy_schedule.id)'))
                ->join('session_group_users', 'session_group_users.session_id', '=', 'cronofy_schedule.id')
                ->join('users', 'users.id', '=', 'session_group_users.user_id')
                ->join('users as ws', 'ws.id', '=', 'cronofy_schedule.ws_id')
                ->where(function ($query) use ($timezone, $now) {
                    $query
                    ->whereRaw(
                        "CONVERT_TZ(cronofy_schedule.end_time, ?, ?) <= ?"
                    ,['UTC',$timezone,$now])
                    ->orWhere('cronofy_schedule.status', 'completed');
                })
                ->whereNull('cronofy_schedule.cancelled_at')
                ->whereNotIn('cronofy_schedule.status', ['canceled', 'rescheduled', 'open', 'short_canceled'])
                ->where('cronofy_schedule.no_show', 'No')
                ->whereNull('users.deleted_at')
                ->whereNull('ws.deleted_at')
                ->where('session_group_users.user_id', $cronofySchedule->user_id)
                ->groupBy('session_group_users.user_id')
                ->count('cronofy_schedule.id');

            // ongoing sessions count
            $ongoingCount = CronofySchedule::select(\DB::raw('COUNT(cronofy_schedule.id)'))
                ->join('session_group_users', 'session_group_users.session_id', '=', 'cronofy_schedule.id')
                ->join('users', 'users.id', '=', 'session_group_users.user_id')
                ->join('users as ws', 'ws.id', '=', 'cronofy_schedule.ws_id')
                ->whereRaw('? BETWEEN cronofy_schedule.start_time AND cronofy_schedule.end_time', $nowInUTC->toDateTimeString())
                ->whereNull('cronofy_schedule.cancelled_at')
                ->where('cronofy_schedule.status', 'booked')
                ->where('cronofy_schedule.no_show', 'No')
                ->whereNull('users.deleted_at')
                ->whereNull('ws.deleted_at')
                ->where('session_group_users.user_id', $cronofySchedule->user_id)
                ->groupBy('session_group_users.user_id')
                ->count('cronofy_schedule.id');

            // cancelled sessions count
            $cancelledCount = CronofySchedule::select(\DB::raw('COUNT(cronofy_schedule.id)'))
                ->select(\DB::raw('COUNT(cronofy_schedule.id)'))
                ->join('session_group_users', 'session_group_users.session_id', '=', 'cronofy_schedule.id')
                ->join('users', 'users.id', '=', 'session_group_users.user_id')
                ->join('users as ws', 'ws.id', '=', 'cronofy_schedule.ws_id')
                ->whereNotNull('cronofy_schedule.cancelled_at')
                ->where(function ($query) {
                    $query->where('cronofy_schedule.status', 'canceled')
                        ->orWhere('cronofy_schedule.status', 'short_canceled')
                        ->orWhere('cronofy_schedule.status', 'rescheduled');
                })
                ->whereNull('users.deleted_at')
                ->whereNull('ws.deleted_at')
                ->where('session_group_users.user_id', $cronofySchedule->user_id)
                ->groupBy('session_group_users.user_id')
                ->count('cronofy_schedule.id');

            $data = [
                'cronofySchedule'     => $cronofySchedule,
                'client'              => $client,
                'dob'                 => Carbon::parse($clientProfile->birth_date)->format(config('zevolifesettings.date_format.default_date')),
                'gender'              => ucfirst($clientProfile->gender),
                'clientCompany'       => $clientCompany,
                'completedCount'      => $completedCount ?? 0,
                'ongoingCount'        => $ongoingCount ?? 0,
                'cancelledCount'      => $cancelledCount ?? 0,
                'wellbeingSpecilists' => (!empty($wellbeingSpecilists) ? $wellbeingSpecilists : null),
                'pagination'          => config('zevolifesettings.datatable.pagination.short'),
                'timezone'            => $timezone,
                'date_format'         => config('zevolifesettings.date_format.moment_default_datetime'),
                'ga_title'            => trans('page_title.clientlist.health_referral'),
                'loginemail'          => ($user->email ?? ""),
                'role'                => $role,
            ];
            return \view('admin.cronofy.clientlist.health-referral', $data);
        } catch (\Exception $exception) {
            abort(500);
        }
    }

    /**
     * Store the health referral data for WBTL
     * @param CreateHealthReferralRequest $request
     * @param CronofySchedule $cronofySchedule
     * @param OccupationalHealthReferral $healthReferral
     * @return RedirectResponse
     */
    public function storeHealthReferral(CreateHealthReferralRequest $request, CronofySchedule $cronofySchedule, OccupationalHealthReferral $healthReferral)
    {
        $user = auth()->user();
        $role = getUserRole($user);
        if (!access()->allow('add-occupational-health-referral') || ($role->slug != 'wellbeing_team_lead')) {
            abort(403);
        }
        try {
            \DB::beginTransaction();
            $data = $healthReferral->storeEntity($request->all(), $cronofySchedule);
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => "Record added successfully",
                    'status' => 1,
                ];
                return \Redirect::route('admin.cronofy.clientlist.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => "Something went wrong please try again.",
                    'status' => 0,
                ];
                return redirect()->back()->withInput()->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.cronofy.clientlist.index')->with('message', $messageData);
        }
    }

    /**
     * Get location details based on company id
     * @param company $company
     * @return Array
     */
    public function getLocation(Company $company)
    {
        if (!access()->allow('manage-clients')) {
            abort(403);
        }

        try {
            // get company wise locations
            $locations = $company->locations()
                ->select('company_locations.id', 'company_locations.name')
                ->orderBy('company_locations.name')
                ->get()
                ->pluck('name', 'id')
                ->toArray();

            if (!empty($locations)) {
                $result = [
                    'result'    => true,
                    'locations' => $locations,
                ];
            } else {
                $result = [
                    'result'    => false,
                    'locations' => [],
                ];
            }

            return $result;

        } catch (\Exception $exception) {
            abort(500);
        }
    }

    /**
     * Send emails to users when WBS access the kin information
     * @param SendEmailForAccessKinInfoRequest $request
     * @return 
     */
    function sendEmailForAccessKinInfo(SendEmailForAccessKinInfoRequest $request){
        $user       = auth()->user();
        $alertUsers = AdminAlert::leftJoin('admin_alert_users', 'admin_alert_users.alert_id', '=', 'admin_alerts.id')
            ->select('admin_alert_users.user_email','admin_alert_users.user_name')->where('admin_alerts.title','Access Next to Kin Info')->pluck('user_email','user_name')->toArray();
        
        $getConsentFormLogs = $this->consentFormLogs->select(
            \DB::raw("CONCAT(users.first_name,' ',users.last_name) as ws_name"),
            'users.email as ws_email',
            'consent_form_logs.name as client_name',
            'consent_form_logs.email as client_email',
            'consent_form_logs.is_accessed')
        ->leftJoin('users', 'users.id', '=', 'consent_form_logs.ws_id')
        ->where(['consent_form_logs.user_id' => $request['user_id'], 'consent_form_logs.ws_id' => $request['ws_id']])->get()->first();
        
        //Check if kin information is already accessed by WBS, If yes then not need to send alert emails
        if (!empty($request['type']) && $request['type'] == 'alreadyAccessedKinInfo') {
            return $getConsentFormLogs->is_accessed;
        }
        foreach ($alertUsers as $alertName => $alertEmail) {
            $data = [
                'email'        => $user->email,
                'name'         => $user->full_name,
                'alertEmails'  => $alertEmail,
                'alertName'    => $alertName,
                'client_name'  => ($getConsentFormLogs ? $getConsentFormLogs->client_name : null),
                'client_email' => ($getConsentFormLogs ? $getConsentFormLogs->client_email : null),
                'ws_name'      => ($getConsentFormLogs ? $getConsentFormLogs->ws_name : null),
                'ws_email'     => ($getConsentFormLogs ? $getConsentFormLogs->ws_email : null),
            ];
            $this->consentFormLogs->where(['consent_form_logs.user_id' => $request['user_id'], 'consent_form_logs.ws_id' => $request['ws_id']])->update(['is_accessed' => true]);
            dispatch(new AdminAlertJob($data));
        }
    }
}
