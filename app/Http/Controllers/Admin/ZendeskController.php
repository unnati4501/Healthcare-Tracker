<?php declare (strict_types = 1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ZendeskTrigger\ZdTicketTriggerRequest;
use App\Http\Requests\Admin\EditClientNoteRequest;
use App\Http\Requests\Admin\EditSessionRequest;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Company;
use App\Models\User;
use App\Models\ZdTicket;
use App\Models\ZdTicketComment;
use App\Models\Calendly;
use Breadcrumbs;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Zendesk\API\HttpClient as ZendeskAPI;

class ZendeskController extends Controller
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * ZdTicket model variable
     *
     * @var $zdTicket
     */
    private $zdTicket;

    /**
     * ZdTicketComment model variable
     *
     * @var $zdTicketComment
     */
    private $zdTicketComment;

    /**
     * Create a new controller instance.
     *
     * @param ZdTicket $zdTicket
     * @return void
     */
    public function __construct(ZdTicket $zdTicket, ZdTicketComment $zdTicketComment)
    {
        $this->zdTicket        = $zdTicket;
        $this->zdTicketComment = $zdTicketComment;
        $this->subdomain       = config('eap.zd_subdomain');
        $this->username        = config('eap.zd_username');
        $this->token           = config('eap.zd_token');
        $this->bindBreadcrumbs();
    }

    /**
     * bind breadcrumbs of calendly module
     */
    private function bindBreadcrumbs()
    {
        Breadcrumbs::for('clientlist.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('clientlist.title.index'));
        });
        Breadcrumbs::for('clientlist.details', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('clientlist.title.index'), route('admin.sessions.index'));
            $trail->push(trans('clientlist.title.details'));
        });
    }

    /**
     * Handle Zendesk triggers/webhooks
     *
     * @param ZdTicketTriggerRequest $request
     * @return mixed void|JsonResponse
     */
    public function webhook(ZdTicketTriggerRequest $request)
    {
        try {
            // Zendesk client object
            $this->zdClient = new ZendeskAPI($this->subdomain);
            $this->zdClient->setAuth('basic', ['username' => $this->username, 'token' => $this->token]);

            $triggerType = (!empty($request->triggerType) ? $request->triggerType : '');
            if ($triggerType == 'ticket') {
                // Handle ticket trigger
                $this->zdTicket->updateOrCreateTicket($this->zdClient, $request->all());
                return $this->successResponse([], 'Successfully logged!');
            } else {
                return "Invalid trigger type {$triggerType}";
            }
        } catch (\Exception $exception) {
            report($exception);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        if (!access()->allow('manage-clients')) {
            abort(403);
        }

        try {
            $user = auth()->user();
            $role = getUserRole($user);
            $data = [
                'companies'  => Company::where('is_reseller', false)->where('allow_app', true)->get()->pluck('name', 'id')->toArray(),
                'role'       => $role->slug,
                'pagination' => config('zevolifesettings.datatable.pagination.short'),
                'ga_title'   => trans('page_title.clientlist.index'),
            ];

            return \view('admin.clientlist.index', $data);
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
                'message' => trans('clientlist.messages.unauthorized_access'),
            ], 422);
        }

        try {
            return $this->zdTicket->getTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            return response()->json([
                'data'   => trans('clientlist.messages.something_wrong_try_again'),
                'status' => 0,
            ]);
        }
    }

    /**
     * To get details of clients
     *
     * @param ZdTicket $ticket
     * @return view
     */
    public function clientDetails(ZdTicket $ticket, Request $request)
    {
        if (!access()->allow('view-clients')) {
            abort(403);
        }

        try {
            $user        = auth()->user();
            $appTimezone = config('app.timezone');
            $nowInUTC    = now($appTimezone);
            $timezone    = (!empty($user->timezone) ? $user->timezone : $appTimezone);
            $role        = getUserRole($user);
            $client      = $ticket->user()->select('id', 'first_name', 'last_name', 'email')->first();

            if (!access()->allow('view-clients') || ($role->slug == 'counsellor' && $ticket->therapist_id != $user->id)) {
                return view('errors.401');
            }

            // if client is empty return 403
            if (empty($client)) {
                return view('errors.401');
            }

            $clientProfile = $client->profile;
            $clientCompany = $client->company()->select('companies.id', 'companies.name')->first();

            // completed sessions count
            $completedCount = $user->myClients()
                ->whereRaw('eap_calendly.end_time <= ?', $nowInUTC->toDateTimeString())
                ->where('eap_calendly.status', 'active')
                ->whereNull('eap_calendly.cancelled_at')
                ->where('user_id', $client->id)
                ->count('eap_calendly.id');

            // ongoing sessions count
            $ongoingCount = $user->myClients()
                ->whereRaw('? BETWEEN eap_calendly.start_time AND eap_calendly.end_time', $nowInUTC->toDateTimeString())
                ->where('eap_calendly.status', 'active')
                ->whereNull('eap_calendly.cancelled_at')
                ->where('user_id', $client->id)
                ->count('eap_calendly.id');

            // cancelled sessions count
            $cancelledCount = $user->myClients()
                ->where('eap_calendly.status', 'canceled')
                ->whereNotNull('eap_calendly.cancelled_at')
                ->where('user_id', $client->id)
                ->count('eap_calendly.id');

            // grab all the counsellor type comments
            $notes = $ticket->comments()
                ->select(
                    'eap_ticket_comments.id',
                    'eap_ticket_comments.comment'
                )->selectRaw(
                    "CONVERT_TZ(eap_ticket_comments.created_at, ?, ?) AS created_at"
                ,[$appTimezone,$timezone])
                ->where('type', 'counsellor')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            // grab all the tickets between logged in therapist_id and user
            $tickets = $this->zdTicket->select('id')
                ->where('user_id', $client->id)
                ->where('therapist_id', $user->id)
                ->get()->pluck('id')->toArray();

            // grab all the internal_note type comments of specified tickets
            $internalNotes = $this->zdTicketComment
                ->select(
                    'eap_ticket_comments.id',
                    'eap_ticket_comments.comment'
                )->selectRaw(
                    "CONVERT_TZ(eap_ticket_comments.created_at, ?, ?) AS created_at"
                ,[$appTimezone,$timezone])
                ->where('type', 'internal_note')
                ->whereIn('ticket_id', $tickets)
                ->whereRaw('DATE(eap_ticket_comments.created_at) = ?', [now($timezone)->toDateString()])
                ->get();

            //Get the session user notes
            $sessionNotes = $user->myClients()
            ->select(
                'eap_calendly.id as CalendyId',
                'eap_calendly.notes as SessionNote'
            )->selectRaw(
                "CONVERT_TZ(eap_calendly.created_at, ?, ?) AS created_at"
            ,[$appTimezone,$timezone])
            ->where('eap_calendly.notes', '<>', '')
            ->where('eap_calendly.user_id', $client->id)
            ->paginate(config('zevolifesettings.datatable.pagination.short'));
            
            $data = [
                'ticket'         => $ticket,
                'client'         => $client,
                'dob'            => Carbon::parse($clientProfile->birth_date)->format(config('zevolifesettings.date_format.default_date')),
                'gender'         => ucfirst($clientProfile->gender),
                'clientCompany'  => $clientCompany,
                'completedCount' => $completedCount,
                'ongoingCount'   => $ongoingCount,
                'cancelledCount' => $cancelledCount,
                'sessionStatus'  => config('zevolifesettings.calendly_session_status'),
                'pagination'     => config('zevolifesettings.datatable.pagination.short'),
                'timezone'       => $timezone,
                'notes'          => $notes,
                'internalNotes'  => $internalNotes,
                'queryString'    => $request->all(),
                'date_format'    => config('zevolifesettings.date_format.moment_default_datetime'),
                'ga_title'       => trans('page_title.clientlist.details'),
                'sessionNotes'   => $sessionNotes
            ];

            return \view('admin.clientlist.details', $data);
        } catch (\Exception $exception) {
            abort(500);
        }
    }

    /**
     * To get sessions of clients
     *
     * @param ZdTicket $ticket
     * @param User $client
     * @return Json
     */
    public function getClientSessions(ZdTicket $ticket, User $client, Request $request)
    {
        if (!access()->allow('view-clients')) {
            return response()->json([
                'message' => trans('clientlist.messages.unauthorized_access'),
            ], 422);
        }

        try {
            return $this->zdTicket->getClientSessions($client, $request->all());
        } catch (\Exception $exception) {
            report($exception);
            return response()->json([
                'data'   => trans('clientlist.messages.something_wrong_try_again'),
                'status' => 0,
            ]);
        }
    }

    /**
     * Add note for client
     *
     * @param ZdTicket $ticket
     * @param Request $request
     * @return RedirectResponse
     */
    public function addNote(ZdTicket $ticket, Request $request)
    {
        if (!access()->allow('view-clients')) {
            return redirect()->back()->with('message', [
                'data'   => trans('clientlist.messages.unauthorized_access'),
                'status' => 0,
            ]);
        }

        try {
            \DB::beginTransaction();
            $data = $ticket->storeNote($request->all());
            if ($data) {
                \DB::commit();
                return \Redirect::route('admin.clientlist.details', $ticket->id)->with('message', [
                    'data'   => "Note has been added successfully",
                    'status' => 1,
                ]);
            } else {
                \DB::rollback();
                return redirect()->back()->with('message', [
                    'data'   => trans('clientlist.messages.something_wrong_try_again'),
                    'status' => 0,
                ]);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            return redirect()->back()->with('message', [
                'data'   => trans('clientlist.messages.something_wrong_try_again'),
                'status' => 0,
            ]);
        }
    }

    /**
     * Get specified type and date notes
     *
     * @param ZdTicket $ticket
     * @param String $type
     * @param Request $request
     * @return view
     */
    public function getNotes(ZdTicket $ticket, String $type, Request $request)
    {
        try {
            $user          = auth()->user();
            $appTimezone   = config('app.timezone');
            $client        = $ticket->user()->select('id', 'first_name', 'last_name', 'email')->first();
            $timezone      = (!empty($user->timezone) ? $user->timezone : $appTimezone);
            $date          = Carbon::parse($request->date, $timezone)->toDateString();
            $internalNotes = '';

            // grab all the tickets between logged in therapist_id and user
            $ticketIds = $ticket->select('id')
                ->where('user_id', $client->id)
                ->where('therapist_id', $user->id)
                ->get()->pluck('id')->toArray();

            $this->zdTicketComment
                ->select(
                    'eap_ticket_comments.id',
                    'eap_ticket_comments.comment'
                )->selectRaw(
                    "CONVERT_TZ(eap_ticket_comments.created_at, ?, ?) AS created_at"
                ,[$appTimezone,$timezone])
                ->where('type', 'internal_note')
                ->whereIn('ticket_id', $ticketIds)
                ->whereRaw('DATE(eap_ticket_comments.created_at) = ?', [$date])
                ->each(function ($note) use (&$internalNotes) {
                    $internalNotes .= \view('admin.clientlist.comment-block', ['note' => $note])->render();
                });
            return $internalNotes;
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            return redirect()->back()->with('message', [
                'data'   => trans('clientlist.messages.something_wrong_try_again'),
                'status' => 0,
            ]);
        }
    }

    /**
     * Get and display note in editor
     *
     * @param Request $request
     * @param Calendly $calendy
     * @return view
     */
    public function getNoteById(Request $request, Calendly $calendy)
    {
        try {
            if ($request['noteFrom'] != null) {
                $data = $calendy->where('id', $request['id'])->get()->first();
                $notes = $data->notes;
            } else {
                $data = $this->zdTicketComment->where('id', $request['id'])->get()->first();
                $notes = $data->comment;
            }
            return response()->json([
                'note' => $notes,
            ], 200);
        } catch (\Exception $exception) {
            report($exception);
            return redirect()->back()->with('message', [
                'data'   => trans('clientlist.messages.something_wrong_try_again'),
                'status' => 0,
            ]);
        }
    }

    /**
     * Update notes
     *
     * @param EditClientNoteRequest $request
     * @param EditSessionRequest $sessionRequest
     * @param Calendly $calendy
     * @return view
     */
    public function updateNoteById(EditClientNoteRequest $request, EditSessionRequest $sessionRequest, Calendly $calendy)
    {
        try {
            if ($request['noteFrom'] != null) {
                $data = $this->zdTicketComment->updateSessionNotes($sessionRequest->all());
            } else {
                $data = $this->zdTicketComment->updateNotes($request->all());
            }
            if ($data) {
                return \Redirect::route('admin.clientlist.details', $request['clientId'])->with('message', [
                    'data'   => "Note has been edited successfully",
                    'status' => 1,
                ]);
            } else {
                return redirect()->back()->with('message', [
                    'data'   => trans('clientlist.messages.something_wrong_try_again'),
                    'status' => 0,
                ]);
            }
        } catch (\Exception $exception) {
            report($exception);
            return redirect()->back()->with('message', [
                'data'   => trans('clientlist.messages.something_wrong_try_again'),
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
            return $this->zdTicketComment->deleteRecord($id);
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
     * delete session notes
     *
     * @param int $id
     * @param Calendly $calendy
     * @return view
     */
    public function deleteSessionNote($id, Calendly $calendy)
    {
        try {
            return $calendy->deleteNote($id);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('clientlist.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }
}
