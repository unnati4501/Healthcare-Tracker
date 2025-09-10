<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CancelEventRequest;
use App\Http\Requests\Admin\CreateEventRequest;
use App\Http\Requests\Admin\EditEventRequest;
use App\Models\Company;
use App\Models\Event;
use App\Models\EventBookingLogs;
use App\Models\EventCsatLogs;
use App\Models\EventPresenters;
use App\Models\SubCategory;
use App\Models\User;
use Breadcrumbs;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    /**
     * variable to store the model object
     * @var Event $model
     */
    protected $model;

    /**
     * variable to store the model object
     * @var EventCsatLogs $eventCsatLogs
     */
    protected $eventCsatLogs;

    /**
     * contructor to initialize model object
     * @param Event $model;
     */
    public function __construct(Event $model, EventCsatLogs $eventCsatLogs)
    {

        $this->model         = $model;
        $this->eventCsatLogs = $eventCsatLogs;

        $this->bindBreadcrumbs();
    }

    /**
     * bind breadcrumbs of role module
     */
    private function bindBreadcrumbs()
    {

        // event crud
        Breadcrumbs::for('event.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Events');
        });
        Breadcrumbs::for('event.create', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Events', route('admin.event.index'));
            $trail->push('Add Event');
        });
        Breadcrumbs::for('event.edit', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Events', route('admin.event.index'));
            $trail->push('Edit Event');
        });
        Breadcrumbs::for('event.details', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Events', route('admin.event.index'));
            $trail->push('View Event Details');
        });
        Breadcrumbs::for('event.feedback', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Events', route('admin.event.index'));
            $trail->push('Event Feedback');
        });
    }

    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $user                           = auth()->user();
        $role                           = getUserRole($user);
        $checkPlanAccess                = getCompanyPlanAccess($user, 'event');
        $checkPlanAccessForReseller     = getDTAccessForParentsChildCompany($user, 'event');
        if (!access()->allow('event-management')  || ($role->group == 'company' &&  !$checkPlanAccess) || ($role->group == 'reseller' &&  !$checkPlanAccessForReseller)) {
            abort(403);
        }

        try {
            $timezone           = (!empty($user->timezone) ? $user->timezone : config('app.timezone'));
            $company            = $user->company()->select('companies.id', 'companies.enable_event')->first();
            $roleType           = 'zsa';
            $assigneeComapanies = [];
            if ($role->group != 'zevo') {
                if ($company->is_reseller) {
                    $roleType           = 'rsa';
                    $assigneeComapanies = Company::select('name', 'id')->where('parent_id', $company->id)->orWhere('id', $company->id)->get()->pluck('name', 'id')->toArray();
                } else {
                    $roleType = 'rca';
                }
            }

            $data = [
                'timezone'           => $timezone,
                'eventCategory'      => SubCategory::select('id', 'name')->where(['category_id' => 6, 'status' => 1])->pluck('name', 'id')->toArray(),
                'assigneeComapanies' => $assigneeComapanies,
                'bookingStatus'      => config('zevolifesettings.event-status-master')->forget([1, 2])->pluck('text', 'id'),
                'roleType'           => $roleType,
                'pagination'         => config('zevolifesettings.datatable.pagination.long'),
                'ga_title'           => trans('page_title.event.manage_event'),
            ];
            return \view('admin.event.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * @param Request $request
     *
     * @return Mixed JSON
     */
    public function getEvents(Request $request)
    {
        $user               = auth()->user();
        $checkEventRestrict = getCompanyPlanAccess($user, 'event');

        if (!access()->allow('event-management') && !$checkEventRestrict) {
            return response()->json([
                'message' => trans('labels.common_title.unauthorized_access'),
            ], 422);
        }
        try {
            return $this->model->getTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return response()->json($messageData, 500);
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function create(Request $request)
    {
        if (!access()->allow('add-event')) {
            abort(403);
        }

        try {
            $user               = auth()->user();
            $checkEventRestrict = getCompanyPlanAccess($user, 'event');
            $role               = getUserRole($user);
            $company            = $user->company()->first();

            // validate if access from company plan
            if ($role->group == 'company' && !empty($company) && !$checkEventRestrict) {
                return view('errors.401');
            }

            $presenters  = [];
            $roleType    = 'zsa';
            $nowInUTC    = now(config('app.timezone'))->toDateTimeString();
            $subcategory = SubCategory::select('id', 'name')->where(['category_id' => 6, 'status' => 1])->pluck('name', 'id')->toArray();

            // check if zevo then show all reseller parent and child companies
            if ($role->group == 'zevo') {
                $roleType  = 'zsa';
                $companies = $this->getAllCompaniesGroupType($role->group);
            } elseif ($role->group == 'company') {
                $companies = $company;
                $roleType  = 'zca';
            } else {
                // check if RSA then show their own and child companies
                if ($company->is_reseller) {
                    $roleType  = 'rsa';
                    $companies = $this->getAllCompaniesGroupType($role->group, $company);
                } else {
                    // if RCA then show their own company only
                    $companies = $company;
                    $roleType  = 'rca';
                }
            }

            if (in_array($roleType, ['zca', 'rsa', 'rca'])) {
                $presenters = User::select(\DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"), 'users.id')
                    ->whereHas('company', function ($query) use ($company) {
                        $query->where('companies.id', $company->id);
                    })
                    ->where('users.is_blocked', 0)
                    ->where("users.start_date", '<=', $nowInUTC)
                    ->whereNull('users.deleted_at')
                    ->get()
                    ->pluck('text', 'id')
                    ->toArray();
            }

            $data = [
                'fieldDisableStatus'         => false,
                'fieldCapacityDisableStatus' => false,
                'subcategory'                => $subcategory,
                'company'                    => $companies,
                'roleType'                   => $roleType,
                'presenters'                 => [],
                'eventPresenters'            => $presenters,
                'ga_title'                   => trans('page_title.event.create'),
            ];

            return \view('admin.event.create', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.event.index')->with('message', $messageData);
        }
    }

    /**
     * @param CreateEventRequest $request
     *
     * @return RedirectResponse
     */
    public function store(CreateEventRequest $request)
    {
        if (!access()->allow('add-event')) {
            abort(403);
        }

        try {
            \DB::beginTransaction();
            $data = $this->model->storeEntity($request->all());
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('labels.event.data_add_success'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.event.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('labels.common_title.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.event.create')->withInput($request->input())->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.event.create')->with('message', $messageData);
        }
    }

    /**
     * @param Event $event
     * @param Request $request
     * @return View
     */
    public function edit(Event $event, Request $request)
    {

        if (!access()->allow('edit-event')) {
            abort(403);
        }

        try {
            $user               = auth()->user();
            $role               = getUserRole($user);
            $checkEventRestrict = getCompanyPlanAccess($user, 'event');
            if ($role->group == 'company' && !$checkEventRestrict) {
                abort(403);
            }
            $roleType           = 'zsa';
            $now                = now(config('app.timezone'))->toDateTimeString();
            $fieldDisableStatus = $fieldCapacityDisableStatus = ($event->status > 1);
            if ($event->location_type == 2) {
                $fieldCapacityDisableStatus = false;
            }
            $referrerEditInner = 'eventlisting';
            $cancelButtonUrl   = route('admin.event.index');
            $submitURL         = route('admin.event.update', [$event->id]);
            $subcategory       = SubCategory::select('id', 'name')->where(['category_id' => 6, 'status' => 1])
                ->get()->pluck('name', 'id')->toArray();
            $eventPresenters = $event->presenters()
                ->select('user_id', \DB::raw("CONCAT(first_name, ' ', last_name) AS name"))
                ->join('users', 'users.id', '=', 'event_presenters.user_id')
                ->groupBy('user_id');
            $eventCompanies = $event->companies->pluck('company_id')->toArray();
            $upComingEvents = $event->booking()
                ->select('event_booking_logs.company_id', 'event_booking_logs.presenter_user_id')
                ->where('event_booking_logs.status', '4')
                ->whereRaw("TIMESTAMP(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.end_time)) > ?", [$now])
                ->get();
            $bookedCompanies  = $upComingEvents->pluck('company_id')->unique()->toArray();
            $bookedPresenters = $upComingEvents->pluck('presenter_user_id')->unique()->toArray();

            if ($request->has('referrer') && in_array($request->referrer, ["bookingPage", "detailsPage"])) {
                $submitURL = route('admin.event.update', [$event->id, 'referrer' => $request->referrer, 'referrerid' => $request->referrerid]);
                if ($request->referrer == "bookingPage") {
                    $cancelButtonUrl = route('admin.marketplace.book-event', [$event->id]);
                } elseif ($request->referrer == "detailsPage" && !empty($request->referrerid)) {
                    $cancelButtonUrl = route('admin.marketplace.booking-details', [$request->referrerid]);
                }
            }

            // check if zevo then show all reseller parent and child companies
            if ($role->group == 'zevo') {
                // check id event's company id isn't null then show 401 error page
                if (!is_null($event->company_id)) {
                    return view('errors.401');
                }

                $roleType  = 'zsa';
                $companies = $this->getAllCompaniesGroupType($role->group);
            } else {
                $company = $user->company()->first();

                // check if event is created by other company then show 401 error page
                if ($company->id != $event->company_id) {
                    return view('errors.401');
                }

                // check if RSA then show their own and child companies
                if ($company->is_reseller) {
                    $roleType  = 'rsa';
                    $companies = $this->getAllCompaniesGroupType($role->group, $company);
                } else {
                    // if RCA then show their own company only
                    $companies = $company;
                    $roleType  = 'rca';
                }
            }

            $bookedSelectedCompany = array_intersect($bookedCompanies, $eventCompanies);
            $eventPresenterIds     = $eventPresenters->pluck('user_id')->toArray();

            $presenterList = [];
            if (in_array($roleType, ['zca', 'rsa', 'rca'])) {
                $presenterList = User::select(\DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"), 'users.id')
                    ->join('ws_user', 'ws_user.user_id', '=', 'users.id')
                    ->whereHas('company', function ($query) use ($company) {
                            $query->where('companies.id', $company->id);
                        })
                    ->where('users.is_blocked', 0)
                    ->where('ws_user.is_cronofy', true)
                    ->where("users.start_date", '<=', $now)
                    ->whereNull('users.deleted_at')
                    ->get()
                    ->pluck('text', 'id')
                    ->toArray();
            } else {
                $presenterList = User::select(\DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"), 'users.id')
                    ->join('ws_user', 'ws_user.user_id', '=', 'users.id')
                    ->leftJoin('health_coach_expertises', 'health_coach_expertises.user_id', '=', 'users.id')
                    ->where(['users.is_blocked' => 0])
                    ->where(function ($where) use ($event) {
                        $where
                            ->where('health_coach_expertises.expertise_id', $event->subcategory_id);
                    })
                    ->where('ws_user.responsibilities', '!=', 1)
                    ->where('ws_user.is_cronofy', true)
                    ->whereNull('users.deleted_at')
                    ->groupBy('users.id')
                    ->get()
                    ->pluck('text', 'id')
                    ->toArray();
            }

            $data = [
                'event'                      => $event,
                'fieldDisableStatus'         => $fieldDisableStatus,
                'fieldCapacityDisableStatus' => $fieldCapacityDisableStatus,
                'subcategory'                => $subcategory,
                'roleType'                   => $roleType,
                'company'                    => $companies,
                'eventCompanies'             => $eventCompanies,
                'bookedCompanies'            => $bookedCompanies,
                'bookedPresenters'           => $bookedPresenters,
                'bookedSelectedCompany'      => (!(count($bookedSelectedCompany) > 0)),
                'presenters'                 => [],
                'eventPresenters'            => $presenterList,
                'eventPresenterIds'          => $eventPresenterIds,
                'ga_title'                   => trans('page_title.event.edit'),
                'referrerEditInner'          => $referrerEditInner,
                'cancelButtonUrl'            => $cancelButtonUrl,
                'submitURL'                  => $submitURL,
            ];

            return \view('admin.event.edit', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.event.index')->with('message', $messageData);
        }
    }

    /**
     * @param EditEventRequest $request
     *
     * @return RedirectResponse
     */
    public function update(Event $event, EditEventRequest $request)
    {
        if (!access()->allow('edit-event')) {
            abort(403);
        }

        try {
            \DB::beginTransaction();
            $data = $event->updateEntity($request->all());
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('labels.event.data_update_success'),
                    'status' => 1,
                ];
                if ($request->has('referrer') && in_array($request->referrer, ["bookingPage", "detailsPage"])) {
                    if ($request->referrer == "bookingPage") {
                        return \Redirect::route('admin.marketplace.book-event', [$event->id])->with('message', $messageData);
                    } elseif ($request->referrer == "detailsPage" && !empty($request->referrerid)) {
                        return \Redirect::route('admin.marketplace.booking-details', $request->referrerid)->with('message', $messageData);
                    } else {
                        return \Redirect::route('admin.event.index')->with('message', $messageData);
                    }
                } else {
                    return \Redirect::route('admin.event.index')->with('message', $messageData);
                }
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('labels.common_title.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::back()->withInput($request->all())->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::back()->withInput($request->all())->with('message', $messageData);
        }
    }

    /**
     * To delete an event
     *
     * @param  $id
     *
     * @return View
     */
    public function delete(Event $event)
    {
        if (!access()->allow('delete-event')) {
            abort(403);
        }

        try {
            $user               = auth()->user();
            $role               = getUserRole($user);
            $checkEventRestrict = getCompanyPlanAccess($user, 'event');
            if ($role->group == 'company' && !$checkEventRestrict) {
                abort(403);
            }
            // check if event is published and event booking counts are > 0 then prevent event to be deleted
            if ($event->status == 2) {
                $now              = now(config('app.timezone'))->toDateTimeString();
                $openBookingCount = $event->booking()
                    ->where('event_booking_logs.status', '4')
                    ->whereRaw("TIMESTAMP(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.end_time)) > ?", [$now])
                    ->count('event_booking_logs.id');
                if ($openBookingCount > 0) {
                    return [
                        'deleted' => "event_booked",
                    ];
                }
            }

            return $event->deleteRecord();
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.event.index')->with('message', $messageData);
        }
    }

    /**
     * To view an assigned companies of event
     *
     * @param Event $event
     * @return View
     */
    public function view(Event $event, Request $request)
    {
        if (!access()->allow('view-event')) {
            abort(403);
        }

        try {
            $user               = auth()->user();
            $role               = getUserRole($user);
            $checkEventRestrict = getCompanyPlanAccess($user, 'event');
            if ($role->group == 'company' && !$checkEventRestrict) {
                abort(403);
            }

            if ($event->status == 1) {
                return view('errors.401');
            }

            // check if event's company id isn't null then show 401 error page
            if ($role->group == 'zevo') {
                if (!is_null($event->company_id)) {
                    return view('errors.401');
                }
            } else {
                // check if event is created by other company then show 401 error page
                $company = $user->company()->first();
                if ($company->id != $event->company_id) {
                    return view('errors.401');
                }
            }

            $timezone         = (!empty($user->timezone) ? $user->timezone : config('app.timezone'));
            $cancelUrl        = route('admin.event.index');
            $cancelButtonText = "Back to Events";
            $fetchDataUrl     = route('admin.event.view', $event->id);
            if ($request->has('referrer') && in_array($request->referrer, ["bookingPage", "detailsPage"])) {
                if ($request->referrer == "bookingPage") {
                    $cancelButtonText = "Back to Booking Page";
                    $cancelUrl        = route('admin.marketplace.book-event', $event->id);
                } elseif ($request->referrer == "detailsPage") {
                    $cancelButtonText = "Back to Booking Details Page";
                    $cancelUrl        = route('admin.marketplace.booking-details', $request->referrerid);
                }
                $fetchDataUrl = route('admin.event.view', [$event->id, 'referrer' => $request->referrer, 'referrerid' => $request->referrerid]);
            }
            $eventCompanies  = $event->companies()->select('event_companies.company_id', 'companies.name')->join('companies', 'companies.id', '=', 'event_companies.company_id')->get()->pluck('name', 'company_id')->toArray();
            $eventPresenters = $event->presenters()->select('event_presenters.user_id', \DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS name"))->join('users', 'users.id', '=', 'event_presenters.user_id')->groupBy('event_presenters.user_id')->get()->pluck('name', 'user_id')->toArray();
            $eventStatus     = config('zevolifesettings.event-status-master')->forget([1, 2, 7, 8])->pluck('text', 'id')->toArray();

            $data = [
                'timezone'         => $timezone,
                'cancelUrl'        => $cancelUrl,
                'cancelButtonText' => $cancelButtonText,
                'fetchDataUrl'     => $fetchDataUrl,
                'event'            => $event,
                'eventSubcategory' => (!empty($event->subcategory) ? $event->subcategory->name :$event->special_event_category_title),
                'eventCompanies'   => $eventCompanies,
                'eventPresenters'  => $eventPresenters,
                'eventStatus'      => $eventStatus,
                'pagination'       => config('zevolifesettings.datatable.pagination.long'),
                'ga_title'         => trans('page_title.event.view_details'),
            ];

            return \view('admin.event.view', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.event.index')->with('message', $messageData);
        }
    }

    /**
     * To get list of assigned companies in view event page
     *
     * @param Event $event
     * @param Request $request
     * @return Mixed JSON
     */
    public function getEventCompaniesList(Event $event, Request $request)
    {
        if (!access()->allow('view-event')) {
            return response()->json([
                'message' => trans('labels.common_title.unauthorized_access'),
            ], 422);
        }
        try {
            return $event->getEventBookins($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return response()->json($messageData, 500);
        }
    }

    /**
     * To get list of presenters according to logged in user
     *
     * @param  Request $request
     * @return json array
     */
    public function getPresenters(Request $request)
    {
        if (!access()->allow('view-event')) {
            return response()->json([
                'message' => trans('labels.common_title.unauthorized_access'),
            ], 422);
        }
        try {
            return $this->model->getPresenters($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return response()->json($messageData, 500);
        }
    }

    /**
     * To publish an event
     *
     * @param  Event $event
     * @param  Request $request
     * @return json array
     */
    public function publishEvent(Event $event, Request $request)
    {
        try {
            \DB::beginTransaction();
            $data = $event->publishEvent($request->all());
            \DB::commit();

            return $data;
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.event.index')->with('message', $messageData);
        }
    }

    /**
     * To cancel an event of specific company
     *
     * @param  EventBookingLogs $bookingLog
     * @param  CancelEventRequest $request
     * @return json
     */
    public function cancelEvent(EventBookingLogs $bookingLog, CancelEventRequest $request)
    {
        if (!access()->allow('cancel-event')) {
            return response()->json([
                'message'   => trans('labels.common_title.unauthorized_access'),
                'cancelled' => 0,
            ], 422);
        }

        try {
            $user               = auth()->user();
            $appTimezone        = config('app.timezone');
            $now                = now($appTimezone);
            $role               = getUserRole($user);
            $checkEventRestrict = getCompanyPlanAccess($user, 'event');
            if ($role->group == 'company' && !$checkEventRestrict) {
                abort(403);
            }

            // Validate if logged user is event member of event company
            if ($role->group == 'zevo') {
                $eventCompany = $bookingLog->event('events.id', 'events.company_id')->first();
                if (!is_null($eventCompany->company_id)) {
                    return response()->json([
                        'message'   => trans('labels.common_title.unauthorized_access'),
                        'cancelled' => 0,
                    ], 422);
                }
            } elseif ($role->group == 'reseller') {
                $company = $user->company()
                    ->select('companies.id', 'companies.is_reseller', 'companies.parent_id')
                    ->first();
                if ($company->is_reseller) {
                    $rsaCompanies = Company::select('name', 'id')
                        ->where('id', $company->id)
                        ->orWhere('parent_id', $company->id)
                        ->get()->pluck('id')->toArray();

                    // check event is booked for self or child companies
                    if (!in_array($bookingLog->company_id, $rsaCompanies)) {
                        return response()->json([
                            'message'   => trans('labels.common_title.unauthorized_access'),
                            'cancelled' => 0,
                        ], 422);
                    }
                } elseif (!is_null($company->parent_id)) {
                    if ($company->id != $bookingLog->company_id) {
                        return response()->json([
                            'message'   => trans('labels.common_title.unauthorized_access'),
                            'cancelled' => 0,
                        ], 422);
                    }
                }
            }

            // check event start time difference to current time if lesser then 60 minutes then prevent to be cancelled
            $difference = $now->diffInSeconds("{$bookingLog->booking_date} {$bookingLog->start_time}");
            if ($difference <= 3600) {
                return response()->json([
                    'message'   => "Event is happening in 1 hour so you can't cancel the event.",
                    'cancelled' => 0,
                ], 422);
            }

            \DB::beginTransaction();
            $data = $bookingLog->cancelEvent($request->all());
            \DB::commit();
            return $data;
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            return response()->json([
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ], 500);
        }
    }

    /**
     * Get cancelled event details
     *
     * @param  EventBookingLogs $bookingLog
     * @return json
     */
    public function cancelEventDetails(EventBookingLogs $bookingLog)
    {
        try {
            return $bookingLog->cancelEventDetails();
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return response()->json($messageData, 500);
        }
    }

    /**
     * View event feedback - Responded by user from portal
     *
     * @param Event $event
     * @return view
     */
    public function viewFeedback(Event $event)
    {
        if (!access()->allow('view-event-feedback')) {
            abort(403);
        }

        try {
            $user               = auth()->user();
            $role               = getUserRole($user);
            $checkEventRestrict = getCompanyPlanAccess($user, 'event');
            if ($role->group == 'company' && !$checkEventRestrict) {
                abort(403);
            }

            // if event status is 'draft' then show unauthorized error
            if ($event->status == 1) {
                return view('errors.401');
            }

            // validate logged in user is event owner if not then show unauthorized error
            $roleType           = 'zsa';
            $companies          = [];
            $company            = null;
            $uniquePresenterIds = EventPresenters::select('user_id')->groupBy('user_id')->get()->pluck('user_id')->toArray();
            $presenters         = User::select('users.id', \DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS name"))
                ->leftJoin('user_team', 'user_team.user_id', '=', 'users.id')
                ->whereIn('users.id', (!empty($uniquePresenterIds) ? $uniquePresenterIds : [0]));

            if ($role->group == 'zevo') {
                // check id event's company id isn't null then show 401 error page
                if (!is_null($event->company_id)) {
                    return view('errors.401');
                }

                $companies = Company::select('id', 'name')
                    ->pluck('name', 'id')
                    ->toArray();
            } else {
                $company = $user->company()->first();
                // check if event is created by other company then show 401 error page
                if ($company->id != $event->company_id) {
                    return view('errors.401');
                }

                if ($company->is_reseller) {
                    $roleType = 'rsa';
                    $presenters->where(function ($query) use ($company) {
                        $query
                            ->where('user_team.company_id', $company->id);
                    });
                    $companies = Company::select('id', 'name')
                        ->where(function ($query) use ($company) {
                            $query->where('id', $company->id)->orWhere('parent_id', $company->id);
                        })
                        ->pluck('name', 'id')
                        ->toArray();
                } elseif (!$company->is_reseller && is_null($company->parent_id)) {
                    $roleType  = 'zca';
                    $companies = [$company->id => $company->name];
                    $presenters->where(function ($query) use ($company) {
                        $query
                            ->where('user_team.company_id', $company->id);
                    });
                } else {
                    $roleType  = 'rca';
                    $companies = [$company->id => $company->name];
                    $presenters->where(function ($query) use ($company) {
                        $query
                            ->where('user_team.company_id', $company->id)
                            ->orWhere('user_team.company_id', $company->parent_id);
                    });
                }
            }

            $feedbackCount = $event->csat()->count('event_csat_user_logs.id');
            if ($feedbackCount == 0 || is_null($feedbackCount)) {
                return \Redirect::route('admin.event.index')->with('message', [
                    'data'   => "Event has not received any feedback yet!",
                    'status' => 0,
                ]);
            }

            $data = [
                'event'            => $event,
                'eventSubcategory' => $event->subcategory()->select('name')->first(),
                'role'             => $role,
                'roleType'         => $roleType,
                'company'          => $company,
                'feedback'         => config('zevolifesettings.nps_feedback_type'),
                'companies'        => $companies,
                'presenters'       => $presenters->get()->pluck('name', 'id')->toArray(),
                'timezone'         => (!empty($user->timezone) ? $user->timezone : config('app.timezone')),
                'date_format'      => config('zevolifesettings.date_format.moment_default_datetime'),
                'pagination'       => config('zevolifesettings.datatable.pagination.long'),
                'ga_title'         => trans('page_title.event.view_event_feedback'),
            ];

            return \view('admin.event.feedback', $data);
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * To get event feedback request
     *
     * @param Request $request
     * @return Mixed JSON
     */
    public function getEventFeedback(Event $event, Request $request)
    {
        try {
            if (!empty($request->type) && $request->type == "graph") {
                return $this->eventCsatLogs->getCsatGraph($event, $request->all());
            } else {
                return $this->eventCsatLogs->getTableData($event, $request->all());
            }
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return response()->json($messageData, 500);
        }
    }

    /**
     * Get All Companies Group Type
     *
     * @return array
     **/
    protected function getAllCompaniesGroupType($role = '', $companiesDetails = [])
    {
        $groupType = config('zevolifesettings.content_company_group_type');
        if ($role != 'zevo') {
            unset($groupType[1]);
        }
        $companyGroupType = [];
        $nowInUTC         = now(config('app.timezone'))->toDateTimeString();
        foreach ($groupType as $value) {
            if ($value == 'Zevo') {
                    $companies = Company::select('companies.id', 'companies.name')
                        ->where('subscription_start_date', '<=', $nowInUTC)
                        ->where('subscription_end_date', '>=', $nowInUTC)
                        ->where('is_reseller', false)
                        ->whereNull('parent_id')
                        ->get()->pluck('name', 'id')->toArray();
            } elseif($value == 'Parent'){
                    $companies = Company::select('companies.id', 'companies.name')
                        ->where('subscription_start_date', '<=', $nowInUTC)
                        ->where('subscription_end_date', '>=', $nowInUTC)
                        ->whereNull('parent_id')
                        ->where('is_reseller', true);
                    if ($role == 'reseller') {
                        $companies->where('id', $companiesDetails->id);
                    }
                    $companies = $companies->pluck('name', 'id')->toArray();
            } else {
                    $companies = Company::select('companies.id', 'companies.name')
                        ->where('subscription_start_date', '<=', $nowInUTC)
                        ->where('subscription_end_date', '>=', $nowInUTC)
                        ->whereNotNull('parent_id')
                        ->where('is_reseller', false);
                    if ($role == 'reseller') {
                        $companies->where('parent_id', $companiesDetails->id);
                    }
                    $companies = $companies->pluck('name', 'id')->toArray();
            }

            if (count($companies) > 0) {
                $companyGroupType[] = [
                    'roleType'  => $value,
                    'companies' => $companies,
                ];
            }
        }
        return $companyGroupType;
    }
}
