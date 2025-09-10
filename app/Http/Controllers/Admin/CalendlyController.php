<?php declare (strict_types = 1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Requests\Admin\EditSessionRequest;
use App\Http\Traits\ServesApiTrait;
use App\Jobs\SendNewEapNotification;
use App\Models\Calendly;
use App\Models\Company;
use App\Models\Notification;
use App\Models\User;
use Breadcrumbs;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendlyController extends Controller
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Calendly model variable
     *
     * @var $calendly
     */
    private $calendly;

    /**
     * Notification model variable
     *
     * @var Notification
     */
    protected $notification;

    /**
     * Create a new controller instance.
     *
     * @param Calendly $calendly
     * @return void
     */
    public function __construct(Calendly $calendly, Notification $notification)
    {
        $this->calendly     = $calendly;
        $this->notification = $notification;
        $this->bindBreadcrumbs();
    }

    /**
     * Handle Calendly triggers/webhooks
     *
     * @param Request $request
     * @return mixed void|JsonResponse
     */
    public function webhook(Request $request)
    {
        try {
            $payload = $request->all();

            if (!empty($payload) && isset($payload['event'])) {
                $userEmail = $payload['payload']['email'];
                $user      = User::findByEmail($userEmail);

                if (empty($user)) {
                    return $this->successResponse([], 'User does not exist.');
                }

                if (empty($user->company->first())) {
                    return $this->successResponse([], 'User does not belong to a company.');
                }

                // Check EAP Access of company plan
                $checkAccess  = getCompanyPlanAccess($user, 'eap');
                $client       = new \GuzzleHttp\Client();
                $eventRequest = $client->request('GET', $payload['payload']['event'], [
                    'headers' => [
                        'Authorization' => config('eap.calendly_token'),
                    ],
                ]);
                $eventJson = $eventRequest->getBody()->getContents();

                if (!empty($eventJson)) {
                    $eventDetails = json_decode($eventJson);
                    $userRequest  = $client->request('GET', $eventDetails->resource->event_memberships[0]->user, [
                        'headers' => [
                            'Authorization' => config('eap.calendly_token'),
                        ],
                    ]);
                    $userJson = $userRequest->getBody()->getContents();

                    if (!empty($userJson)) {
                        $userDetails    = json_decode($userJson);
                        $therapistEmail = $userDetails->resource->email;
                        $therapist      = User::findByEmail($therapistEmail);

                        if (empty($therapist)) {
                            return $this->successResponse([], 'Counsellor does not exist.');
                        }

                        $therapistRole = $therapist->roles()->whereIn('slug', ['counsellor'])->first();

                        if (empty($therapistRole)) {
                            return $this->successResponse([], 'Counsellor does not belong to dedicated role.');
                        }

                        $calendlyRecord = $this->calendly
                            ->where('event_identifier', $payload['payload']['event'])
                            ->first();

                        if (!empty($eventDetails) && isset($eventDetails->resource->location) && !empty($eventDetails->resource->location->location)) {
                            if ($payload['event'] == 'invitee.created' && empty($calendlyRecord)) {
                                $notes      = !empty($payload['payload']['questions_and_answers']) ? $payload['payload']['questions_and_answers'][0]['answer'] : '';
                                $reminderAt = Carbon::parse($eventDetails->resource->start_time, config('app.timezone'))->subMinutes(15)->toDateTimeString();

                                $calendlyData = [
                                    'name'             => $eventDetails->resource->name,
                                    'user_id'          => $user->id,
                                    'therapist_id'     => $therapist->id,
                                    'event_identifier' => $payload['payload']['event'],
                                    'cancel_url'       => $payload['payload']['cancel_url'],
                                    'reschedule_url'   => $payload['payload']['reschedule_url'],
                                    'start_time'       => Carbon::parse($eventDetails->resource->start_time)->toDateTimeString(),
                                    'end_time'         => Carbon::parse($eventDetails->resource->end_time)->toDateTimeString(),
                                    'event_created_at' => Carbon::parse($eventDetails->resource->created_at)->toDateTimeString(),
                                    'location'         => $eventDetails->resource->location->location,
                                    'notes'            => $notes,
                                    'status'           => $eventDetails->resource->status,
                                    'reminder_at'      => $reminderAt,
                                ];

                                $calendlyResponse = $this->calendly->create($calendlyData);

                                if ($checkAccess) {
                                    // send booked notification to user
                                    $this->dispatch(new SendNewEapNotification($calendlyResponse, 'booked'));

                                    // send reminder notification to user
                                    $this->dispatch(new SendNewEapNotification($calendlyResponse, 'reminder', [
                                        'type'             => 'Manual',
                                        'scheduled_at'     => $reminderAt,
                                        'debug_identifier' => 'session-reminder-15',
                                    ]));
                                }
                            }
                        }
                    }

                    if ($payload['event'] == 'invitee.canceled' && !empty($calendlyRecord)) {
                        $status           = $payload['payload']['rescheduled'] ? 'rescheduled' : $eventDetails->resource->status;
                        $calendlyResponse = $calendlyRecord->update(
                            [
                                'status'           => $status,
                                'cancelled_by'     => $payload['payload']['cancellation']['canceled_by'],
                                'cancelled_at'     => Carbon::parse($payload['payload']['updated_at'])->toDateTimeString(),
                                'cancelled_reason' => $payload['payload']['cancellation']['reason'],
                            ]
                        );

                        if ($checkAccess) {
                            $this->dispatch(new SendNewEapNotification($calendlyRecord, ($status == 'rescheduled' ? 'rescheduled' : 'cancelled')));
                        }

                        // delete scheduled reminder notification
                        $this->notification
                            ->where('tag', '=', 'new-eap')
                            ->where('deep_link_uri', "zevolife://zevo/eap-sessions/{$calendlyRecord->id}")
                            ->where('debug_identifier', 'session-reminder-15')
                            ->delete();
                    }

                    if (!empty($calendlyResponse)) {
                        return $this->successResponse([], 'Successfully logged.');
                    }
                }
            }
        } catch (\Exception $exception) {
            report($exception);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * bind breadcrumbs of calendly module
     */
    public function bindBreadcrumbs()
    {
        Breadcrumbs::for('calendly.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('calendly.title.manage'));
        });
        Breadcrumbs::for('calendly.details', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('calendly.title.manage'), route('admin.sessions.index'));
            $trail->push(trans('calendly.title.details'));
        });
    }

    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        if (!access()->allow('manage-sessions')) {
            abort(403);
        }
        $role = getUserRole();

        try {
            $data               = array();
            $data['pagination'] = config('zevolifesettings.datatable.pagination.short');
            $data['ga_title']   = trans('page_title.calendly.index');
            $data['companies']  = Company::where('is_reseller', false)->where('allow_app', true)->get()->pluck('name', 'id')->toArray();
            $data['duration']   = [
                'next_24' => 'Next 24 Hours',
                'next_7'  => 'Next 7 Days',
                'next_30' => 'Next 30 Days',
                'next_60' => 'Next 60 Days',
            ];
            $data['status'] = [
                'upcoming'    => 'Upcoming',
                'ongoing'     => 'Ongoing',
                'completed'   => 'Completed',
                'cancelled'   => 'Cancelled',
                'rescheduled' => 'Rescheduled',
            ];
            $data['role']                   = $role;
            $data['company_col_visibility'] = $role->slug == 'super_admin';
            return \view('admin.calendly.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('calendly.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.sessions.index')->with('message', $messageData);
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
                'message' => trans('calendly.messages.unauthorized_access'),
            ], 422);
        }
        try {
            return $this->calendly->getTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('calendly.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @param  Calendly $calendly
     * @return view
     */
    public function show(Calendly $calendly)
    {
        $role = getUserRole();
        if (!access()->allow('view-sessions') || ($role->slug == 'counsellor' && $calendly->therapist_id != \Auth::user()->id)) {
            abort(403);
        }

        try {
            $data             = $this->calendly->getDetails($calendly);
            $data['ga_title'] = trans('page_title.calendly.view');
            return \view('admin.calendly.details', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('calendly.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.sessions.index')->with('message', $messageData);
        }
    }

    /**
     * @param Calendly $calendly
     * @return JsonResponse
     */
    public function markAsCompleted(Calendly $calendly)
    {
        if (!access()->allow('manage-sessions')) {
            abort(403);
        }

        try {
            if ($calendly->update(['status' => 'completed'])) {
                return array('completed' => 'true');
            }
            return array('completed' => 'error');
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('calendly.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @param Calendly $calendly
     * @param EditSessionRequest $request
     * @return JsonResponse
     */
    public function update(Calendly $calendly, EditSessionRequest $request)
    {
        try {
            \DB::beginTransaction();
            $data = $calendly->updateNotes($request->all());
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('calendly.messages.notes_update_success'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.sessions.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('calendly.messages.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.sessions.index')->with('message', $messageData);
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
}
