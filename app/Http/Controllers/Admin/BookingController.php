<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BookEventRequest;
use App\Http\Requests\Admin\EditBookEventRequest;
use App\Models\Company;
use App\Models\CompanyWiseCredit;
use App\Models\CronofyAuthenticate;
use App\Models\Event;
use App\Models\EventBookingLogs;
use App\Models\EventBookingLogsTemp;
use App\Models\EventPresenters;
use App\Models\HealthCoachExpertise;
use App\Models\SubCategory;
use App\Models\User;
use App\Repositories\CronofyRepository;
use Breadcrumbs;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BookingController extends Controller
{
    /**
     * variable to store the Event model object
     * @var Event $model
     */
    protected $model;

    /**
     * variable to store the EventBookingLogs model object
     * @var EventBookingLogs $eventBookingLogs
     */
    protected $eventBookingLogs;

    /**
     * variable to store the Cronofy Repository Repository object
     * @var CronofyRepository $cronofyRepository
     */
    protected $cronofyRepository;

    /**
     * variable define for all methods
     */
    protected $dateFormat;

    /**
     * contructor to initialize model object
     *
     * @param Event $event
     */
    public function __construct(Event $event, EventBookingLogs $eventBookingLogs, CronofyRepository $cronofyRepository, CronofyAuthenticate $authenticateModel)
    {
        $this->event             = $event;
        $this->eventBookingLogs  = $eventBookingLogs;
        $this->cronofyRepository = $cronofyRepository;
        $this->authenticateModel = $authenticateModel;
        $this->dateFormat = config('zevolifesettings.date_format.default_date');
        $this->bindBreadcrumbs();
    }

    /**
     * bind breadcrumbs of role module
     */
    private function bindBreadcrumbs()
    {
        Breadcrumbs::for ('booking.book_event.edit', function ($trail, $bookingLogId) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Bookings', route('admin.bookings.index'));
            $trail->push('Booking Details', route('admin.bookings.booking-details', $bookingLogId));
            $trail->push('Edit Booked Event');
        });

        // users
        Breadcrumbs::for ('booking.book_event.users', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Booking', route('admin.bookings.index'));
            $trail->push('Event Registered Users');
        });

        // Session event
        Breadcrumbs::for ('marketplace.book_session.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Marketplace', route('admin.marketplace.index', '#bookings-tab'));
            $trail->push('Book Event');
        });
    }

    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $role = getUserRole($user);
        Breadcrumbs::for ('booking.index', function ($trail) use ($role) {
            $trail->push('Home', route('dashboard'));
            $trail->push(($role->slug == 'wellbeing_specialist') ? 'Events' : 'Bookings');
        });

        $checkPlanAccess            = getCompanyPlanAccess($user, 'event');
        $checkPlanAccessForReseller = getDTAccessForParentsChildCompany($user, 'event');
        if ($role->slug == 'wellbeing_specialist') {
            $wsDetails        = $user->wsuser()->first();
        }
        
        if (!access()->allow('bookings') || ($role->slug == 'wellbeing_specialist' && (!empty($wsDetails) && $wsDetails->is_cronofy && $wsDetails->responsibilities == 1)) || ($role->group == 'company' && !$checkPlanAccess) || ($role->group == 'reseller' && !$checkPlanAccessForReseller)) {
            abort(403);
        }

        try {
            $user               = auth()->user();
            $role               = getUserRole($user);
            $company            = $user->company()->first();
            $checkEventRestrict = getCompanyPlanAccess($user, 'event');

            // Validate if access from company plan
            if ($role->group == 'company' && !$checkEventRestrict) {
                return view('errors.401');
            }

            $comapanies         = [];
            $uniquePresenterIds = EventPresenters::select('user_id')->groupBy('user_id')->get()->pluck('user_id')->toArray();
            $companyDisable     = false;
            $nowInUTC           = now(config('app.timezone'))->toDateTimeString();
            $timezone           = (!empty($user->timezone) ? $user->timezone : config('app.timezone'));
            $presenters         = User::select('users.id', \DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS name"))
                ->join('ws_user', 'ws_user.user_id', '=', 'users.id')
                ->whereIn('users.id', (!empty($uniquePresenterIds) ? $uniquePresenterIds : [0]))
                ->where('ws_user.responsibilities', '!=', 1)
                ->where('ws_user.is_cronofy', true);

            if ($role->group == 'zevo') {
                $comapanies = Company::select('name', 'id')
                    ->where('subscription_start_date', '<=', $nowInUTC)
                    ->where('subscription_end_date', '>=', $nowInUTC)
                    ->get()->pluck('name', 'id')->toArray();
                if ($role->slug == 'wellbeing_specialist') {
                    $comapanies = $this->eventBookingLogs->select('companies.name', 'companies.id')
                        ->leftjoin('companies', 'event_booking_logs.company_id', '=', 'companies.id')
                        ->where('event_booking_logs.presenter_user_id', $user->id)
                        ->orderBy('companies.name')
                        ->get()->pluck('name', 'id')->toArray();
                }
            } else {
                if ($company->is_reseller) {
                    $comapanies = Company::select('name', 'id')
                        ->where('id', $company->id)
                        ->orWhere('parent_id', $company->id)
                        ->get()->pluck('name', 'id')->toArray();
                } elseif (!is_null($company->parent_id)) {
                    $companyDisable = true;
                } elseif (is_null($company->parent_id)) {
                    $companyDisable = true;
                }
            }

            $tabCategory = SubCategory::select('sub_categories.id', 'sub_categories.name')
                ->join('events', 'events.subcategory_id', '=', 'sub_categories.id')
                ->join('event_companies', 'event_companies.event_id', '=', 'events.id')
                ->join('companies', function ($join) use ($nowInUTC) {
                    $join
                        ->on('companies.id', '=', 'event_companies.company_id')
                        ->where('companies.subscription_start_date', '<=', $nowInUTC)
                        ->where('companies.subscription_end_date', '>=', $nowInUTC);
                })
                ->where(['sub_categories.category_id' => 6, 'sub_categories.status' => 1])
                ->where('events.status', 2)
                ->groupBy('sub_categories.id')
                ->having('events_count', '>', 0);

            if ($role->group == 'zevo') {
                $tabCategory
                    ->whereNull('events.company_id')
                    ->whereNull('event_companies.deleted_at')
                    ->withCount(['events' => function ($query) {
                        $query
                            ->join('event_companies', 'event_companies.event_id', '=', 'events.id')
                            ->whereNull('events.company_id')
                            ->whereNull('event_companies.deleted_at');
                    }]);

                if ($role->slug == 'health_coach') {
                    $subCategory = HealthCoachExpertise::select('sub_categories.id', 'sub_categories.name', \DB::raw('0 as events_count'))
                        ->leftjoin('sub_categories', 'health_coach_expertises.expertise_id', '=', 'sub_categories.id')
                        ->where('health_coach_expertises.user_id', $user->id)->get()->toArray();
                }

            } elseif ($role->group == 'reseller') {
                if ($company->is_reseller) {
                    $assigneeComapnies = Company::select('id')
                        ->where('parent_id', $company->id)
                        ->orWhere('id', $company->id)
                        ->get()->pluck('id')->toArray();
                    $tabCategory
                        ->whereIn('event_companies.company_id', $assigneeComapnies)
                        ->whereNull('event_companies.deleted_at')
                        ->where(function ($where) use ($company) {
                            $where
                                ->whereNull('events.company_id')
                                ->orWhere('events.company_id', $company->id);
                        })
                        ->withCount(['events' => function ($query) use ($assigneeComapnies, $company) {
                            $query
                                ->join('event_companies', 'event_companies.event_id', '=', 'events.id')
                                ->whereIn('event_companies.company_id', $assigneeComapnies)
                                ->whereNull('event_companies.deleted_at')
                                ->where(function ($subWhere) use ($company) {
                                    $subWhere
                                        ->whereNull('events.company_id')
                                        ->orWhere('events.company_id', $company->id);
                                });
                        }]);
                } elseif (!is_null($company->parent_id)) {
                    $tabCategory
                        ->where('event_companies.company_id', $company->id)
                        ->whereNull('event_companies.deleted_at')
                        ->withCount(['events' => function ($query) use ($company) {
                            $query
                                ->join('event_companies', 'event_companies.event_id', '=', 'events.id')
                                ->where('event_companies.company_id', $company->id)
                                ->whereNull('event_companies.deleted_at');
                        }]);
                }
            } elseif ($role->group == 'company') {
                $tabCategory
                    ->where('event_companies.company_id', $company->id)
                    ->whereNull('event_companies.deleted_at')
                    ->withCount(['events' => function ($query) use ($company) {
                        $query
                            ->join('event_companies', 'event_companies.event_id', '=', 'events.id')
                            ->where('event_companies.company_id', $company->id)
                            ->whereNull('event_companies.deleted_at');
                    }]);
            }

            $tabCategory           = $tabCategory->get();
            $loginemail            = ($user->email ?? "");
            $loginId               = ($user->id ?? "");
            $healthCoachCategories = [];
            $categories            = [];
            if ($role->slug == 'health_coach') {
                $healthCoachCategories = array_merge($tabCategory->toArray(), $subCategory);
                if (sizeof($healthCoachCategories) > 0) {
                    foreach ($healthCoachCategories as $val) {
                        $categories[$val['id']] = $val['name'];
                    }
                }
            } else {
                $categories = $tabCategory->pluck('name', 'id')->toArray();
            }

            $data = [
                'timezone'            => $timezone,
                'company'             => $company,
                'comapanies'          => $comapanies,
                'companyDisable'      => $companyDisable,
                'presenters'          => $presenters->get()->pluck('name', 'id')->toArray(),
                'tabCategory'         => $tabCategory,
                'categories'          => $categories,
                'role'                => $role,
                'statuses'            => config('zevolifesettings.event_listing_status'),
                'pagination'          => config('zevolifesettings.datatable.pagination.long'),
                'ga_title'            => trans('page_title.marketplace.bookings'),
                'loginemail'          => $loginemail,
                'loginId'             => $loginId
            ];
            return \view('admin.booking.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            return abort(500);
        }
    }

    /**
     * To get booked events
     *
     * @param Request $request
     * @return json
     */
    public function getBookedEvents(Request $request)
    {
        try {
            return $this->eventBookingLogs->getBookedEvents($request->all());
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
     * To cancel an event from booking page
     * @param Event $event
     * @return json
     */
    public function cancelEvent(Request $request)
    {
        if (!access()->allow('cancel-event')) {
            $messageData = [
                'data'   => trans('labels.common_title.unauthorized_access'),
                'status' => 0,
            ];
            return response()->json($messageData, 401);
        }
        try {
            $user             = auth()->user();
            $role             = getUserRole($user);
            $payload          = $request->all();
            $eventBookingLog  = EventBookingLogs::find($payload['event']);
            $event            = Event::find($eventBookingLog->event_id);
            $todayDate        = Carbon::now()->toDateTimeString();
            $eventStartTime   = Carbon::parse("{$eventBookingLog->booking_date} {$eventBookingLog->start_time}")->toDateTimeString();
            $advanceDate      = Carbon::now()->addHours(48)->toDateTimeString();

            if ($role->group == 'zevo') {
                if ($eventStartTime < $todayDate) {
                    $messageData = [
                        'message' => 'Event is already started, please contact support@zevohealth.zendesk.com to cancel or reschedule this event.',
                        'status'  => 0,
                    ];
                    return response()->json($messageData, 422);
                }
                if ($advanceDate > $eventStartTime) {
                    $messageData = [
                        'message' => 'As its less than 48 hours before the event start time, please contact support@zevohealth.zendesk.com to cancel or reschedule this event.',
                        'status'  => 0,
                    ];
                    return response()->json($messageData, 422);
                }
            }

            $data = $eventBookingLog->cancelEvent($payload);
            if ($data['cancelled']) {
                $companyDetails = Company::find($eventBookingLog->company_id);
                $creditData     = [
                    'credits'         => $companyDetails->credits + 1,
                    'on_hold_credits' => ($companyDetails->on_hold_credits > 0) ? $companyDetails->on_hold_credits - 1 : 0,
                ];
                $companyDetails->update($creditData);

                $creditLogData = [
                    'company_id'        => $eventBookingLog->company_id,
                    'user_name'         => $user->full_name,
                    'credits'           => 1,
                    'notes'             => "Event " . $event->name . " cancelled",
                    'type'              => 'Add',
                    'available_credits' => $companyDetails->credits,
                ];
                CompanyWiseCredit::insert($creditLogData);
                $this->cronofyRepository->cancelEvent($data['presenter_id'], $data['scheduling_id']);
                return $data;
            }
        } catch (\Exception $exception) {
            report($exception);
            return response()->json([
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ], 500);
        }
    }

    /**
     * To view event booking details
     * @param Request $eventBookingId
     * @return View
     */
    public function bookingDetails(EventBookingLogs $eventBookingId)
    {
        try {
            $user = auth()->user();
            $role = getUserRole($user);
            Breadcrumbs::for ('booking.book_event.details', function ($trail) use ($role) {
                $trail->push('Home', route('dashboard'));
                $trail->push(($role->slug == 'wellbeing_specialist') ? 'Events' : 'Bookings', route('admin.bookings.index'));
                $trail->push(($role->slug == 'wellbeing_specialist') ? 'Event Details' : 'Booking Details');
            });
            // show error page if event is cancelled
            if ($eventBookingId->status == '3') {
                return \view('errors.401');
            }

            $checkEventRestrict = getCompanyPlanAccess($user, 'event');
            $company            = $user->company()->select('companies.id', 'companies.is_reseller', 'companies.parent_id')->first();

            // event access from company plan.
            if ($role->group == 'company' && !$checkEventRestrict) {
                return view('errors.401');
            }

            $event = $eventBookingId->event;
            // validate page is viewing by valid user
            if ($role->group == 'zevo' && !is_null($event->company_id)) {
                return view('errors.401');
            } elseif ($role->group == 'company' && $eventBookingId->company_id != $company->id) {
                return view('errors.401');
            } elseif ($role->group == 'reseller') {
                if ($company->is_reseller) {
                    $assigneeComapanies = Company::select('id')
                        ->where('parent_id', $company->id)
                        ->orWhere('id', $company->id)->get()
                        ->pluck('id')->toArray();
                    if (!in_array($eventBookingId->company_id, $assigneeComapanies)) {
                        return view('errors.401');
                    }
                } elseif (!is_null($company->parent_id) && $eventBookingId->company_id != $company->id) {
                    return view('errors.401');
                }
            }

            $bookingCompany             = $eventBookingId->company()->select('companies.id', 'companies.is_reseller', 'companies.allow_app')->first();
            $editBtn                    = false;
            $appTimezone                = config('app.timezone');
            $now                        = now($appTimezone);
            $timezone                   = (!empty($user->timezone) ? $user->timezone : $appTimezone);
            $cancelBtn                  = true;
            $feedLabelVisibility        = true;
            $complementaryOptVisibility = true;
            $joinBtn                    = false;
            $presenterString            = "";
            $feedLabelVisibility        = $bookingCompany->allow_app;

            if (is_null($company)) {
                $editBtn = (is_null($event->company_id));
            } else {
                $editBtn = in_array($company->id, [$event->company_id, $bookingCompany->id]);
                if (!is_null($company->parent_id)) {
                    $complementaryOptVisibility = false;
                }
            }

            if ($role->group != 'zevo' && ($eventBookingId->status == 6 || $eventBookingId->status == 7 || $eventBookingId->status == 8)) {
                $editBtn = false;
            }

            // check is event start duration is lesser then 1 hour then prevent event to be edited/cancelled
            if ($now->diffInSeconds("{$eventBookingId->booking_date} {$eventBookingId->start_time}", false) <= 3600) {
                $cancelBtn = $editBtn = false;
            }

            $bookingDate = Carbon::parse("{$eventBookingId->booking_date} {$eventBookingId->start_time}", $appTimezone)
                ->setTimezone($timezone)->format($this->dateFormat);
            $startTime = Carbon::parse("{$eventBookingId->booking_date} {$eventBookingId->start_time}", $appTimezone)
                ->setTimezone($timezone)->format('h:i A');
            $endTime = Carbon::parse("{$eventBookingId->booking_date} {$eventBookingId->end_time}", $appTimezone)
                ->setTimezone($timezone)->format('h:i A');

            // Display join button 15 mins before from the event start time.
            if (((Carbon::parse("{$eventBookingId->booking_date} {$eventBookingId->start_time}", $appTimezone)->subMinutes(15)) <= $now) && ((Carbon::parse("{$eventBookingId->booking_date} {$eventBookingId->end_time}", $appTimezone)) >= $now)) {
                $joinBtn = true;
            }

            if (!empty($eventBookingId->meta->presenter)) {
                $presenterString .= "{$eventBookingId->meta->presenter}";
            }

            $registrationDate = Carbon::parse($eventBookingId->registration_date, $appTimezone)->setTimezone($timezone)->format(config('zevolifesettings.date_format.date_time_12_hours'));

            $data = [
                'eventBookingId'             => $eventBookingId,
                'event'                      => $event,
                'eventCompany'               => $eventBookingId->company,
                'feedLabelVisibility'        => $feedLabelVisibility,
                'registrationDate'           => $registrationDate,
                'editBtn'                    => $editBtn,
                'cancelBtn'                  => $cancelBtn,
                'bookingDate'                => $bookingDate,
                'startTime'                  => $startTime,
                'endTime'                    => $endTime,
                'presenterString'            => $presenterString,
                'complementaryOptVisibility' => $complementaryOptVisibility,
                'joinBtn'                    => $joinBtn,
                'role'                       => $role,
                'locationTypes'              => config('zevolifesettings.event-location-type'),
                'ga_title'                   => trans('page_title.marketplace.booking_details'),
            ];

            return \view('admin.booking.booking-event-details', $data);
        } catch (\Exception $exception) {
            report($exception);
            return abort(500);
        }
    }

    /**
     * Edit booked event page
     *
     * @param Event $event
     * @return View
     */
    public function editBookedEvent(EventBookingLogs $bookingLog,  ? EventBookingLogsTemp $eventBookingLogsTemp)
    {

        if (!access()->allow('book-event')) {
            abort(403);
        }

        try {

            $user                 = auth()->user();
            $role                 = getUserRole($user);
            $checkEventRestrict   = getCompanyPlanAccess($user, 'event');
            $userCompany          = $user->company()->select('companies.id', 'companies.is_reseller', 'companies.parent_id')->first();
            $appTimezone          = config('app.timezone');
            $nowInUTC             = now($appTimezone);
            $disableEmailNote     = false;
            $disableCompany       = true;
            $showComplementaryOpt = true;
            $status               = [];
            $companiesAttr        = [];
            $company              = $bookingLog->company()->select('id', 'name', 'is_reseller', 'parent_id', 'allow_app')->first();
            $event                = $bookingLog->event()
                ->select('id', 'name', 'company_id', 'subcategory_id', 'duration', 'location_type', 'capacity', 'description')->first();

            // event access from company plan.
            if ($role->group == 'company' && !$checkEventRestrict) {
                return view('errors.401');
            }

            // validate page is viewing by valid user
            if ($role->group == 'zevo' && !is_null($event->company_id)) {
                return view('errors.401');
            } elseif ($role->group == 'company' && $bookingLog->company_id != $userCompany->id) {
                return view('errors.401');
            } elseif ($role->group == 'reseller') {
                if ($userCompany->is_reseller) {
                    $assigneeComapanies = Company::select('name', 'id')
                        ->where('parent_id', $userCompany->id)
                        ->orWhere('id', $userCompany->id)->get()
                        ->pluck('id')->toArray();
                    if (!in_array($bookingLog->company_id, $assigneeComapanies)) {
                        return view('errors.401');
                    }
                } elseif (!is_null($userCompany->parent_id) && $bookingLog->company_id != $userCompany->id) {
                    return view('errors.401');
                }
            }

            if (!$company->is_reseller && !is_null($company->parent_id)) {
                $showComplementaryOpt = false;
            }

            $companyType = "zca";
            if ($company->is_reseller) {
                $companyType = "rsa";
            } elseif (!is_null($company->parent_id)) {
                $companyType = "rca";
            }

            $timezone    = $company->locations()->select('timezone')->where('default', true)->first();
            $timezone    = (!empty($timezone->timezone) ? $timezone->timezone : $appTimezone);
            $bookingDate = Carbon::parse("{$bookingLog->booking_date} {$bookingLog->start_time}", $appTimezone)
                ->setTimezone($timezone);
            $endTime = Carbon::parse("{$bookingLog->booking_date} {$bookingLog->end_time}", $appTimezone)
                ->setTimezone($timezone);

            $slotInfo = [];
            if (!empty($bookingLog->meta->presenter)) {
                $slotInfo['name'] = $bookingLog->meta->presenter;
            }
            if (!empty($bookingLog->meta->start_time) && !empty($bookingLog->meta->end_time)) {
                $presenterTimeZone      = (!empty($bookingLog->meta->timezone) ? $bookingLog->meta->timezone : $appTimezone);
                $slotInfo['start_time'] = Carbon::parse("{$bookingLog->booking_date} {$bookingLog->meta->start_time}", $presenterTimeZone)
                    ->setTimezone($timezone)->format('h:i A');
                $slotInfo['end_time'] = Carbon::parse("{$bookingLog->booking_date} {$bookingLog->meta->end_time}", $presenterTimeZone)
                    ->setTimezone($timezone)->format('h:i A');
            }

            $companies = $event->companies()
                ->select(
                    'event_companies.id',
                    'companies.id AS company_id',
                    'companies.name',
                    'companies.allow_app',
                    \DB::raw("(CASE
                        WHEN companies.is_reseller = true THEN 'rsa'
                        WHEN companies.is_reseller = false AND companies.parent_id IS NOT NULL THEN 'rca'
                        WHEN companies.is_reseller = false AND companies.parent_id IS NULL THEN 'zca'
                    END) as 'company_type'")
                )
                ->join('companies', 'companies.id', '=', 'event_companies.company_id')
                ->where('companies.subscription_start_date', '<=', $nowInUTC)
                ->where('companies.subscription_end_date', '>=', $nowInUTC)
                ->whereNull('event_companies.deleted_at')
                ->havingRaw('company_type IS NOT NULl');

            // check is event start duration is lesser then 24 hour then prevent email notes to be edit
            if ($nowInUTC->diffInSeconds("{$bookingLog->booking_date} {$bookingLog->start_time}", false) <= (3600 * 24)) {
                $disableEmailNote = true;
            }

            if (is_null($company)) {
                $companyType = null;
                $editBtn     = (is_null($event->company_id));
                if (!$editBtn) {
                    return \view('errors.401');
                }
            } else {
                $editBtn = ($event->company_id == $company->id);
                if ($company->is_reseller) {
                    $companyType = null;
                    $companies->whereHas('company', function ($query) use ($company) {
                        $query->where('parent_id', $company->id)
                            ->orWhere('id', $company->id);
                    });
                } elseif (!is_null($company->parent_id)) {
                    $companyType          = "rca";
                    $showComplementaryOpt = false;
                    $disableCompany       = true;
                    $companies->where('event_companies.company_id', $company->id);
                } elseif (is_null($company->parent_id)) {
                    $companyType    = "zca";
                    $disableCompany = true;
                    $companies->where('event_companies.company_id', $company->id);
                }
            }

            // companies attributes
            $companies->each(function ($value) use (&$companiesAttr) {
                $companiesAttr[$value->company_id] = [
                    'data-company-type'   => $value->company_type,
                    'data-feed-selection' => ((($value->company_type == 'rca' && $value->allow_app) || $value->company_type == 'zca') ? 'true' : 'false'),
                ];
            });

            $eventPresenters = $event->presenters()
                ->select('user_id', \DB::raw("CONCAT(first_name, ' ', last_name) AS name"))
                ->join('users', 'users.id', '=', 'event_presenters.user_id')
                ->groupBy('user_id');

            $eventPresenterIds = $eventPresenters->pluck('user_id')->toArray();

            $presenterList = User::select(\DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"), 'users.id', 'ws_user.video_link')
                ->join('ws_user', 'ws_user.user_id', '=', 'users.id')
                ->leftJoin('health_coach_expertises', 'health_coach_expertises.user_id', '=', 'users.id')
                ->where(['users.is_blocked' => 0])
                ->where(function ($where) use ($event, $eventPresenterIds) {
                    $where
                        ->where('health_coach_expertises.expertise_id', $event->subcategory_id)
                        ->whereIn('users.id', $eventPresenterIds);
                })
                ->where('ws_user.responsibilities', '!=', 1)
                ->where('ws_user.is_cronofy', true)
                ->whereIn('users.availability_status', [1, 2])
                ->whereNull('users.deleted_at')
                ->groupBy('users.id')
                ->get();

            $realTimeScheduleId = 'sch_' . (string) Str::uuid();
            $presenterString    = null;
            $eventBookingId     = $bookingLog;
            $displayBookingDate = Carbon::parse("{$eventBookingId->booking_date} {$eventBookingId->start_time}", $appTimezone)
                ->setTimezone($timezone)->format($this->dateFormat);
            $displayStartTime = Carbon::parse("{$eventBookingId->booking_date} {$eventBookingId->start_time}", $appTimezone)
                ->setTimezone($timezone)->format('h:i A');
            $displayEndTime = Carbon::parse("{$eventBookingId->booking_date} {$eventBookingId->end_time}", $appTimezone)
                ->setTimezone($timezone)->format('h:i A');

            if (!empty($eventBookingId->meta->presenter)) {
                $presenterString .= "{$eventBookingId->meta->presenter}";
            }

            $data = [
                'duration'              => timeToDecimal($event->duration),
                'bookingLog'            => (isset($eventBookingLogsTemp->id) && !empty($eventBookingLogsTemp->id)) ? $eventBookingLogsTemp : $bookingLog,
                'eventBookingLogsTemp'  => $eventBookingLogsTemp,
                'bookingLogId'          => $bookingLog->id,
                'event'                 => $event,
                'company'               => $company,
                'bookingDate'           => $bookingDate->format('d-m-Y'),
                'fromTime'              => $bookingDate->format('h:i A'),
                'toTime'                => $endTime->format('h:i A'),
                'slotInfo'              => $slotInfo,
                'companiesAttr'         => $companiesAttr,
                'eventPresenters'       => $presenterList,
                'companies'             => $companies->pluck('name', 'company_id')->toArray(),
                'addToStroryVisibility' => $company->allow_app,
                'disableEmailNote'      => $disableEmailNote,
                'showComplementaryOpt'  => $showComplementaryOpt,
                'companyType'           => $companyType,
                'scheduling_id'         => $realTimeScheduleId,
                'statuses'              => $status,
                'reschedule'            => true,
                'displayBookingDate'    => $displayBookingDate,
                'displayStartTime'      => $displayStartTime,
                'displayEndTime'        => $displayEndTime,
                'presenterString'       => $presenterString,
                'disableCompany'        => $disableCompany,
                'registrationDate'      => (isset($eventBookingLogsTemp->registration_date) && !empty($eventBookingLogsTemp->registration_date)) ? Carbon::parse($eventBookingLogsTemp->registration_date, $appTimezone)->setTimezone($user->timezone)->toDatetimeString() : Carbon::parse($bookingLog->registration_date, $appTimezone)->setTimezone($user->timezone)->toDatetimeString(),
                'presenterName'         => ((in_array($companyType, ['rca', 'rsa', 'zca'])) ? $bookingLog->meta->presenter : null),
                'cc_email'              => (!empty($eventBookingLogsTemp) && isset($eventBookingLogsTemp->cc_email)) ? explode(',', $eventBookingLogsTemp->cc_email) : [],
                'locationTypes'         => config('zevolifesettings.event-location-type'),
                'ga_title'              => trans('page_title.marketplace.edit_booked_event', ["event_name" => ""]),
            ];

            //Get the email list for booked event
            $data['customEmails'] = [];
            if (!empty($data['cc_email'])) {
                foreach ($data['cc_email'] as $key => $value) {
                    $data['customEmails'][$key]['id']    = $key;
                    $data['customEmails'][$key]['email'] = $value;
                }
            } else {
                $customEmails = $bookingLog->eventBookingEmails()->where(['event_booking_log_id' => $bookingLog->id])->get();
                if (!empty($customEmails)) {
                    foreach ($customEmails as $customEmailIndex => $customEmailValue) {
                        $data['customEmails'][$customEmailIndex] = [
                            'id'    => $customEmailValue->id,
                            'email' => $customEmailValue->email,
                        ];
                    }
                }
            }
            return \view('admin.booking.edit-book-event-new', $data);
        } catch (\Exception $exception) {
            report($exception);
            return abort(500);
        }
    }

    /**
     *
     * @param EditBookEventRequest $request
     * @return json
     */
    public function updateBookedEvent(EventBookingLogs $bookingLog, EditBookEventRequest $request,  ? EventBookingLogsTemp $eventBookingLogsTemp)
    {
        if (!access()->allow('book-event')) {
            return response()->json([
                'data'   => trans('labels.common_title.unauthorized_access'),
                'status' => 0,
            ], 401);
        }

        try {
            \DB::beginTransaction();
            // validate edit is being edited by valid user
            $user               = auth()->user();
            $role               = getUserRole($user);
            $checkEventRestrict = getCompanyPlanAccess($user, 'event');
            $company            = $user->company()->select('companies.id')->first();
            $event              = $bookingLog->event;
            if ($role->group == 'company' && !$checkEventRestrict) {
                return response()->json([
                    'data'   => trans('labels.common_title.unauthorized_access'),
                    'status' => 0,
                ], 401);
            }

            if (is_null($company)) {
                $editBtn = (is_null($event->company_id));
            } else {
                $editBtn = in_array($company->id, [$event->company_id, $bookingLog->company_id]);
            }
            
            if (!$editBtn) {
                return response()->json([
                    'data'   => trans('labels.common_title.unauthorized_access'),
                    'status' => 0,
                ], 401);
            }
            // check is event start duration is lesser then 1 hour then prevent event to be edited/cancelled
            $appTimezone = config('app.timezone');
            $now         = now($appTimezone);
            if ($now->diffInSeconds("{$bookingLog->booking_date} {$bookingLog->start_time}", false) <= 3600) {
                return response()->json([
                    'data'   => "Event is happening in 1 hour so you can't update the event.",
                    'status' => 0,
                ], 422);
            }

            $data                   = array();
            $healthCoachUnavailable = [];
            $nowInUTC               = now(config('app.timezone'))->toDateTimeString();
            $eventCompany           = Company::select('name', 'subscription_start_date', 'subscription_end_date')->find($bookingLog->company_id);
            if ($nowInUTC < $eventCompany->subscription_start_date && $nowInUTC > $eventCompany->subscription_end_date) {
                $messageData = [
                    'data'   => "It seems {$eventCompany->name} company's subscription isn't active, please contact your Administrator or Zevo account manager.",
                    'status' => 0,
                ];
                return \Redirect::route('admin.marketplace.index')->with('message', $messageData);
            }


            if ($request->updateflag == 1) {
                $bookingLog->updateBookedEntity($request->all());
                \DB::commit();
                $messageData = [
                    'data'   => "Marketplace event has been updated successfully.",
                    'status' => 1,
                ];
                return \Redirect::route('admin.bookings.index')->with('message', $messageData);

            } else {
                $wcUserId    = $request->ws_user;
                $wcUser      = User::where('id', $wcUserId)->first();
                $appTimezone = config('app.timezone');
                $timezone    = (!empty($wcUser->timezone) ? $wcUser->timezone : $appTimezone);
                $wcDetails   = $wcUser->wsuser()->first();
                $userProfile  = $wcUser->profile()->first();
                $noticePeriod = ($userProfile->notice_period > 0) ? $userProfile->notice_period : 48;
                if ($wcUser->availability_status == 2) {
                    $healthCoachUnavailable = $wcUser->healthCocahAvailability()->select(
                        'from_date',
                        'to_date'
                    )->get()->toArray();
                }
                

                if ($wcDetails->is_authenticate) {
                    $authentication = $wcUser->cronofyAuthenticate()->first();
                    if (!empty($authentication)) {
                        $this->cronofyRepository->refreshToken($authentication);
                    }
                }

                $duration           = timeToDecimal($event->duration);
                $featureBooking     = 14; // In days
                $date               = Carbon::now();
                $eventId            = $event->id;
                $realTimeScheduleId = $bookingLog->scheduling_id;
                $tokens             = $this->authenticateModel->getTokens($wcUser->id);
                $subId              = $tokens['subId'];
                $todayDate          = Carbon::now()->toDateTimeString();
                $registrationDate   = Carbon::parse($request->registrationdate)->toDateTimeString();
                $startDate          = $registrationDate;
                $diffBetweenTwoDays = carbon::parse($todayDate)->diffInHours(carbon::parse($startDate), false);

                if ($diffBetweenTwoDays <= $noticePeriod) {
                    // 48 Hours logic exclude Saturday and Sunday
                    $currentDate = Carbon::parse($date);
                    $startTime   = Carbon::parse($date, $timezone)->addHours($noticePeriod)->setTimezone($appTimezone)->toDateTimeString();
                    $endDate     = Carbon::parse($startTime);
                    $period      = CarbonPeriod::create($currentDate, $endDate);
                    foreach ($period as $date) {
                        $checkDay = Carbon::parse($date)->format('l');
                        if (in_array($checkDay, ['Saturday', 'Sunday'])) {
                            $startTime = Carbon::parse($startTime, $timezone)->addDays(1)->setTimezone($appTimezone)->toDateTimeString();
                        }
                    }
                    $checkDay = Carbon::parse($startTime)->format('l');
                    if (in_array($checkDay, ['Saturday'])) {
                        $startTime = Carbon::parse($startTime, $timezone)->addDays(1)->setTimezone($appTimezone)->toDateTimeString();
                    }
                    $checkDaySunday = Carbon::parse($startTime)->format('l');
                    if (in_array($checkDaySunday, ['Sunday'])) {
                        $startTime = Carbon::parse($startTime, $timezone)->addDays(1)->setTimezone($appTimezone)->toDateTimeString();
                    }
                    $dayCount        = 0;
                    $crossCheckCount = CarbonPeriod::create($currentDate, $startTime);
                    foreach ($crossCheckCount as $date) {
                        $checkDay = Carbon::parse($date)->format('l');
                        if (!in_array($checkDay, ['Saturday', 'Sunday'])) {
                            $dayCount = $dayCount + 1;
                        }
                    }
                    $totalDays     = $noticePeriod / 24;
                    $remainingDays = $totalDays - $dayCount;
                    if ($remainingDays) {
                        $startTime = Carbon::parse($startTime, $timezone)->addDays($remainingDays)->setTimezone($appTimezone)->toDateTimeString();

                        $checkDay = Carbon::parse($startTime)->format('l');
                        if (in_array($checkDay, ['Saturday'])) {
                            $startTime = Carbon::parse($startTime, $timezone)->addDays(1)->setTimezone($appTimezone)->toDateTimeString();
                        }
                        $checkDaySunday = Carbon::parse($startTime)->format('l');
                        if (in_array($checkDaySunday, ['Sunday'])) {
                            $startTime = Carbon::parse($startTime, $timezone)->addDays(1)->setTimezone($appTimezone)->toDateTimeString();
                        }
                    }
                } else {
                    $startTime = Carbon::parse($startDate, $timezone)->setTimezone($appTimezone)->toDateTimeString();
                }

                $endTime                 = Carbon::parse($startTime, $timezone)->setTimezone($appTimezone)->addDays($featureBooking)->toDateTimeString();
                $wcSlot                  = $wcUser->eventPresenterSlots()->select('day', 'start_time', 'end_time')->get()->toArray();
                $this->cronofyRepository->updateAvailability($wcSlot, $wcUser, false);
                $response                = $this->cronofyRepository->dateTimePicker($wcUser->id);
                $queryPeriod             = generateQueryPeriod($wcSlot, $startTime, $endTime, $appTimezone, $timezone, $healthCoachUnavailable);
                $startTime               = date("Y-m-d\TH:i:s.000\Z", strtotime($startTime));
                $endTime                 = date("Y-m-d\TH:i:s.000\Z", strtotime($endTime));
                $data['event_id']        = $eventId;
                $data['wc_id']           = $wcUser->id;
                $data['scheduling_id']   = $realTimeScheduleId;
                $data['name']            = $event->name;
                $data['token']           = !(empty($response)) ? $response['element_token']['token'] : null;
                $data['company_id']      = $bookingLog->company_id;
                $data['duration']        = $duration;
                $data['timezone']        = $timezone;
                $data['subId']           = $subId;
                $data['eventbooking_id'] = $bookingLog->id;
                $data['startTime']       = $startTime;
                $data['endTime']         = $endTime;
                $data['queryPeriod']     = $queryPeriod;
                $data['reschedule']      = true;
                $data['payload']         = $request->all();

                if (!empty($eventBookingLogsTemp)) {
                    $eventBookingLogsTemp = new EventBookingLogsTemp;
                }
                $eventBookingLogsTemp->event_id          = $eventId;
                $eventBookingLogsTemp->company_id        = $bookingLog->company_id;
                $eventBookingLogsTemp->presenter_user_id = $wcUser->id;
                $eventBookingLogsTemp->company_type      = (isset($request->company_type)) ? $request->company_type : 'Zevo';
                $eventBookingLogsTemp->video_link        = $request->video_link;
                $eventBookingLogsTemp->capacity_log      = $bookingLog->capacity_log;
                $eventBookingLogsTemp->description       = $request->description;
                $eventBookingLogsTemp->notes             = $request->notes;
                $eventBookingLogsTemp->email_notes       = $request->email_notes;
                $eventBookingLogsTemp->cc_email          = !empty($request->email) ? implode(',', $request->email) : null;
                $eventBookingLogsTemp->registration_date = $request->registrationdate;
                $eventBookingLogsTemp->is_complementary  = !empty($request->is_complementary);
                $eventBookingLogsTemp->add_to_story      = !empty($request->add_to_story);
                $eventBookingLogsTemp->save();
                $data['eventbookinglogsId'] = $eventBookingLogsTemp->id;
                \DB::commit();
                return \view('admin.marketplace.cronofy-ui-element', $data);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData, 500);
        }
    }

    /**
     * To get participated users of the event
     *
     * @param EventBookingLogs $eventBookingId
     * @return view
     */
    public function eventRegisteredUsers(EventBookingLogs $eventBookingId)
    {
        if (!access()->allow('event-registered-users')) {
            abort(403);
        }

        try {
            $user               = auth()->user();
            $role               = getUserRole($user);
            $checkEventRestrict = getCompanyPlanAccess($user, 'event');
            $company            = $user->company()
                ->select('companies.id', 'companies.is_reseller', 'companies.parent_id')
                ->first();

            // if booking status is other then booking then prevent
            if ($eventBookingId->status == 3) {
                return response()->json([
                    'data'   => trans('labels.common_title.unauthorized_access'),
                    'status' => 0,
                ], 401);
            }

            // Check company plan access or not
            if ($role->group == 'company' && !$checkEventRestrict) {
                return view('errors.401');
            }

            // validate booking logs is viewing by valid user
            if ($company->is_reseller) {
                $assigneeComapnies = Company::select('id')
                    ->where('parent_id', $company->id)
                    ->orWhere('id', $company->id)
                    ->get()->pluck('id')->toArray();
                if (!in_array($eventBookingId->company_id, $assigneeComapnies)) {
                    return view('errors.401');
                }
            } elseif (!is_null($company->parent_id) || is_null($company->parent_id)) {
                if ($eventBookingId->company_id != $company->id) {
                    return view('errors.401');
                }
            }

            $participatedUsersCount = $eventBookingId->users()
                ->select('users.id')
                ->where('is_cancelled', false)
                ->count('users.id');
            if (is_null($participatedUsersCount) || $participatedUsersCount == 0) {
                $messageData = [
                    'data'   => 'No any registered users for the event.',
                    'status' => 0,
                ];
                return \Redirect::route('admin.marketplace.index', '#booked-tab')->with('message', $messageData);
            }

            $timezone = (!empty($user->timezone) ? $user->timezone : config('app.timezone'));

            $data = [
                'bookingLog'  => $eventBookingId,
                'event'       => $eventBookingId->event()->select('id', 'name', 'company_id')->first(),
                'timezone'    => $timezone,
                'date_format' => config('zevolifesettings.date_format.moment_default_datetime'),
                'pagination'  => config('zevolifesettings.datatable.pagination.long'),
                'ga_title'    => trans('page_title.marketplace.event_registered_users'),
            ];

            return \view('admin.booking.event-registered-users', $data);
        } catch (\Exception $exception) {
            report($exception);
            return abort(500);
        }
    }

    /**
     * To get participated users of the event
     *
     * @param EventBookingLogs $eventBookingId
     * @return json
     */
    public function getEventRegisteredUsers(EventBookingLogs $eventBookingId, Request $request)
    {
        if (!access()->allow('event-registered-users')) {
            return response()->json([
                'message' => trans('labels.common_title.unauthorized_access'),
            ], 422);
        }

        try {
            return $eventBookingId->getEventRegisteredUsers($request->all());
        } catch (\Exception $exception) {
            report($exception);
            return response()->json([
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ], 500);
        }
    }

    /**
     * Export bookings
     * @param Request $request
     * @return Array
     * @throws Exception
     */
    public function exportBookings(Request $request)
    {
        try {
            \DB::beginTransaction();
            $data = $this->eventBookingLogs->exportBookings($request->all());
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('challenges.messages.report_success'),
                    'status' => 1,
                ];
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('challenges.messages.no_records_found'),
                    'status' => 0,
                ];
            }
            return $messageData;
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return response()->json($messageData, 500);
        }
    }
}
