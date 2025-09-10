<?php

namespace App\Http\Controllers\Admin;

use App\Events\DigitaltherapyExceptionHandlingEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BookEventRequest;
use App\Http\Requests\Admin\CreateEventSlotRequest;
use App\Models\Company;
use App\Models\CronofyAuthenticate;
use App\Models\Event;
use App\Models\EventBookingLogs;
use App\Models\EventBookingLogsTemp;
use App\Models\EventPresenters;
use App\Models\SubCategory;
use App\Models\User;
use App\Repositories\CronofyRepository;
use Breadcrumbs;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MarketplaceController extends Controller
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
    private $cronofyRepository;

    /**
     * variable to store the Cronofy Authenticate model object
     * @var CronofyAuthenticate $authenticateModel
     */
    protected $authenticateModel;

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
        $this->bindBreadcrumbs();
    }

    /**
     * bind breadcrumbs of role module
     */
    private function bindBreadcrumbs()
    {
        // marketplace
        Breadcrumbs::for ('marketplace.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Marketplace');
        });

        // book event
        Breadcrumbs::for ('marketplace.book_event.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Marketplace', route('admin.marketplace.index', '#bookings-tab'));
            $trail->push('Book Event');
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
        $user                       = auth()->user();
        $role                       = getUserRole($user);
        $checkPlanAccess            = getCompanyPlanAccess($user, 'event');
        $checkPlanAccessForReseller = getDTAccessForParentsChildCompany($user, 'event');
        if (!access()->allow('market-place-list') || ($role->group == 'company' && !$checkPlanAccess) || ($role->group == 'reseller' && !$checkPlanAccessForReseller)) {
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
                ->leftJoin('user_team', 'user_team.user_id', '=', 'users.id')
                ->join('ws_user', 'ws_user.user_id', '=', 'users.id')
                ->whereIn('users.id', (!empty($uniquePresenterIds) ? $uniquePresenterIds : [0]));

            if ($role->group == 'zevo') {
                $presenters->where('ws_user.responsibilities', '!=', 1)
                    ->where('ws_user.is_cronofy', true);
                $comapanies = Company::select('name', 'id')
                    ->where('subscription_start_date', '<=', $nowInUTC)
                    ->where('subscription_end_date', '>=', $nowInUTC)
                    ->get()->pluck('name', 'id')->toArray();
            } else {
                if ($company->is_reseller) {
                    $presenters->where(function ($query) use ($company) {
                        $query
                            ->where('user_team.company_id', $company->id)
                            ->where('ws_user.responsibilities', '!=', 1)
                            ->where('ws_user.is_cronofy', true);
                    });

                    $comapanies = Company::select('name', 'id')
                        ->where('id', $company->id)
                        ->orWhere('parent_id', $company->id)
                        ->get()->pluck('name', 'id')->toArray();
                } elseif (!is_null($company->parent_id)) {
                    $companyDisable = true;
                    $presenters->where(function ($query) use ($company) {
                        $query
                            ->where('ws_user.responsibilities', '!=', 1)
                            ->where('ws_user.is_cronofy', true)
                            ->where(function ($query) use ($company) {
                                $query->where('user_team.company_id', $company->id)
                                    ->orWhere('user_team.company_id', $company->parent_id);
                            });
                    });
                } elseif (is_null($company->parent_id)) {
                    $companyDisable = true;
                    $presenters->where(function ($query) use ($company) {
                        $query
                            ->where('user_team.company_id', $company->id)
                            ->orWhere('users.is_coach', true);
                    });
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

            $tabCategory = $tabCategory->get();

            $data = [
                'timezone'       => $timezone,
                'company'        => $company,
                'comapanies'     => $comapanies,
                'companyDisable' => $companyDisable,
                'presenters'     => $presenters->get()->pluck('name', 'id')->toArray(),
                'tabCategory'    => $tabCategory,
                'categories'     => $tabCategory->pluck('name', 'id')->toArray(),
                'pagination'     => config('zevolifesettings.datatable.pagination.long'),
                'ga_title'       => trans('page_title.marketplace.booking_page'),
            ];

            return \view('admin.marketplace.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            return abort(500);
        }
    }

    /**
     * To get events for marketplace
     *
     * @param Request $request
     * @return View
     */
    public function getEvents(Request $request)
    {
        if (!access()->allow('market-place-list')) {
            return response()->json([
                'message' => trans('labels.common_title.unauthorized_access'),
            ], 422);
        }
        try {
            return $this->event->getEventsForMarketPlace($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'message' => trans('labels.common_title.something_wrong'),
                'status'  => 0,
            ];
            return response()->json($messageData, 500);
        }
    }

    /**
     * Book event page
     *
     * @param Event $event
     * @return View
     */
    public function bookEvent(Event $event)
    {
        if (!access()->allow('book-event')) {
            abort(403);
        }

        try {
            // if event isn't published then show unauthorized error page
            if ($event->status != 2) {
                return \view('errors.401');
            }

            $user                 = auth()->user();
            $role                 = getUserRole($user);
            $checkEventRestrict   = getCompanyPlanAccess($user, 'event');
            $nowInUTC             = now(config('app.timezone'))->toDateTimeString();
            $company              = $user->company()->first();
            $showComplementaryOpt = true;

            // Validate if access from company plan
            if ($role->group == 'company' && !$checkEventRestrict) {
                return view('errors.401');
            }

            $editBtn        = false;
            $disableCompany = false;
            $companyType    = null;
            $companies      = $event->companies()
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

            // edit button and
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

            $companies = $companies->get();
            if ($companies->isEmpty()) {
                return \view('errors.401');
            }

            // companies attributes
            $companiesAttr = [];
            $companies->each(function ($value) use (&$companiesAttr) {
                $companiesAttr[$value->company_id] = [
                    'data-company-type'   => $value->company_type,
                    'data-feed-selection' => ((($value->company_type == 'rca' && $value->allow_app) || $value->company_type == 'zca') ? 'true' : 'false'),
                ];
            });

            $data = [
                'event'                => $event,
                'editBtn'              => $editBtn,
                'company'              => $company,
                'companies'            => $companies->pluck('name', 'company_id')->toArray(),
                'companiesAttr'        => $companiesAttr,
                'disableCompany'       => $disableCompany,
                'showComplementaryOpt' => $showComplementaryOpt,
                'companyType'          => $companyType,
                'locationTypes'        => config('zevolifesettings.event-location-type'),
                'ga_title'             => trans('page_title.marketplace.book_event', ["event_name" => ""]),
            ];

            return \view('admin.marketplace.book-event', $data);
        } catch (\Exception $exception) {
            report($exception);
            return abort(500);
        }
    }

    /**
     * Book event New page
     *
     * @param Event $event
     * @return View
     */
    public function bookEventNew(Event $event,  ? EventBookingLogsTemp $eventBookingLogsTemp)
    {
        if (!access()->allow('book-event')) {
            abort(403);
        }

        try {

            // if event isn't published then show unauthorized error page
            if ($event->status != 2) {
                return \view('errors.401');
            }

            $user                 = auth()->user();
            $role                 = getUserRole($user);
            $checkEventRestrict   = getCompanyPlanAccess($user, 'event');
            $nowInUTC             = now(config('app.timezone'))->toDateTimeString();
            $company              = $user->company()->first();
            $showComplementaryOpt = true;

            // Validate if access from company plan
            if ($role->group == 'company' && !$checkEventRestrict) {
                return view('errors.401');
            }

            $editBtn        = false;
            $disableCompany = false;
            $companyType    = null;
            $companies      = $event->companies()
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

            // edit button and
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

            $companies = $companies->get();
            if ($companies->isEmpty()) {
                return \view('errors.401');
            }

            // companies attributes
            $companiesAttr = [];
            $companies->each(function ($value) use (&$companiesAttr) {
                $companiesAttr[$value->company_id] = [
                    'data-company-type'   => $value->company_type,
                    'data-feed-selection' => ((($value->company_type == 'rca' && $value->allow_app) || $value->company_type == 'zca') ? 'true' : 'false'),
                ];
            });

            $realTimeScheduleId = 'sch_' . (string) Str::uuid();

            $data = [
                'event'                => $event,
                'duration'             => timeToDecimal($event->duration),
                'editBtn'              => $editBtn,
                'company'              => $company,
                'companies'            => $companies->pluck('name', 'company_id')->toArray(),
                'companiesAttr'        => $companiesAttr,
                'reschedule'           => false,
                'scheduling_id'        => $realTimeScheduleId,
                'disableCompany'       => $disableCompany,
                'eventPresenters'      => $presenterList,
                'eventPresenterIds'    => $eventPresenterIds,
                'showComplementaryOpt' => $showComplementaryOpt,
                'companyType'          => $companyType,
                'eventBookingLogsTemp' => $eventBookingLogsTemp,
                'description'          => (!empty($eventBookingLogsTemp) && isset($eventBookingLogsTemp->description)) ? $eventBookingLogsTemp->description : $event->description,
                'presenter_user_id'    => (!empty($eventBookingLogsTemp) && isset($eventBookingLogsTemp->presenter_user_id)) ? $eventBookingLogsTemp->presenter_user_id : null,
                'cc_email'             => (!empty($eventBookingLogsTemp) && isset($eventBookingLogsTemp->cc_email)) ? explode(',', $eventBookingLogsTemp->cc_email) : [],
                'locationTypes'        => config('zevolifesettings.event-location-type'),
                'ga_title'             => trans('page_title.marketplace.book_event', ["event_name" => ""]),
            ];

            if (!empty($data['cc_email'])) {
                foreach ($data['cc_email'] as $key => $value) {
                    $data['customEmails'][$key]['id']    = $key;
                    $data['customEmails'][$key]['email'] = $value;
                }
            }

            return \view('admin.marketplace.book-event-new', $data);
        } catch (\Exception $exception) {
            report($exception);
            return abort(500);
        }
    }
    /**
     * To get available slots with presenters for marketplace
     *
     * @param Event $event
     * @param Request $request
     * @return json
     */
    public function getSlots(Event $event,  ? EventBookingLogs $bookingLog, Request $request)
    {
        try {
            $nowInUTC     = now(config('app.timezone'))->toDateTimeString();
            $eventCompany = Company::select('name', 'subscription_start_date', 'subscription_end_date')->find($request->company);
            if ($nowInUTC > $eventCompany->subscription_start_date && $nowInUTC < $eventCompany->subscription_end_date) {
                return $event->getSlots($event, $bookingLog, $request->all());
            } else {
                return response()->json([
                    'message' => "It seems {$eventCompany->name} company's subscription isn't active, please contact your Administrator or Zevo account manager.",
                ], 422);
            }
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData, 500);
        }
    }

    /**
     *
     * @param Event $event
     * @param BookEventRequest $request
     * @return json
     */
    public function confirmEventBooking(Event $event, BookEventRequest $request,  ? EventBookingLogsTemp $eventBookingLogsTemp)
    {
        if (!access()->allow('book-event')) {
            $messageData = [
                'data'   => trans('labels.common_title.unauthorized_access'),
                'status' => 0,
            ];
            return \Redirect::route('admin.marketplace.index')->with('message', $messageData);
        }
        $user    = Auth::user();
        $company = $user->company()->first();
        $wcUserId     = $request->ws_user;
        $wcUser       = User::where('id', $wcUserId)->first();

        try {
            \DB::beginTransaction();
            // if event status is other then published then send error response
            if ($event->status != 2) {
                $messageData = [
                    'data'   => trans('labels.book_event.event_cancelled'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.marketplace.index')->with('message', $messageData);
            }

            $wsUser    = User::where('id', $request->ws_user)->first();
            $wcDetails = $wsUser->wsuser()->first();
            if ($wcDetails->is_authenticate) {
                $authentication = $wsUser->cronofyAuthenticate()->first();
                if (!empty($authentication)) {
                    $this->cronofyRepository->refreshToken($authentication);
                }
            }

            // Check is company is available for an event
            $eventCompany = $event->companies()->select('event_companies.id')->where('company_id', $request->company)->first();
            if (empty($eventCompany)) {
                $messageData = [
                    'data'   => trans('labels.book_event.event_company_deleted'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.marketplace.index')->with('message', $messageData);
            }

            $data                   = array();
            $healthCoachUnavailable = [];
            $nowInUTC               = now(config('app.timezone'))->toDateTimeString();
            $eventCompany           = Company::select('name', 'subscription_start_date', 'subscription_end_date')->find($request->company);

            if ($nowInUTC < $eventCompany->subscription_start_date && $nowInUTC > $eventCompany->subscription_end_date) {
                $messageData = [
                    'data'   => "It seems {$eventCompany->name} company's subscription isn't active, please contact your Administrator or Zevo account manager.",
                    'status' => 0,
                ];
                return \Redirect::route('admin.marketplace.index')->with('message', $messageData);
            }

            $appTimezone  = config('app.timezone');
            $timezone     = (!empty($wcUser->timezone) ? $wcUser->timezone : $appTimezone);
            $wcDetails    = $wcUser->wsuser()->first();
            $userProfile  = $wcUser->profile()->first();
            $noticePeriod = ($userProfile->notice_period > 0) ? $userProfile->notice_period : 48;

            if ($wcUser->availability_status == 2) {
                $healthCoachUnavailable = $wcUser->healthCocahAvailability()->select(
                    'from_date',
                    'to_date'
                )->get()->toArray();
            }

            $duration           = timeToDecimal($event->duration);
            $featureBooking     = 14; // In days
            $date               = Carbon::now();
            $eventId            = $event->id;
            $realTimeScheduleId = 'sch_' . (string) Str::uuid();
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
            $data['company_id']      = $request->company;
            $data['duration']        = $duration;
            $data['timezone']        = $timezone;
            $data['subId']           = $subId;
            $data['startTime']       = $startTime;
            $data['endTime']         = $endTime;
            $data['queryPeriod']     = $queryPeriod;
            $data['eventbooking_id'] = 0;
            $data['reschedule']      = false;
            $data['payload']         = $request->all();

            if (!empty($eventBookingLogsTemp)) {
                $eventBookingLogsTemp = new EventBookingLogsTemp;
            }
            $eventBookingLogsTemp->event_id          = $eventId;
            $eventBookingLogsTemp->company_id        = $request->company;
            $eventBookingLogsTemp->presenter_user_id = $wcUser->id;
            $eventBookingLogsTemp->company_type      = (isset($request->company_type)) ? $request->company_type : 'Zevo';
            $eventBookingLogsTemp->video_link        = $request->video_link;
            $eventBookingLogsTemp->capacity_log      = $request->capacity;
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

        } catch (\Exception $exception) {
            \DB::rollback();
            // Send email when trow error while digital therapy any operation
            event(new DigitaltherapyExceptionHandlingEvent([
                'type'         => 'Event Booking',
                'message'      => (string) $exception->getMessage(),
                'company'      => $company,
                'wsDetails'    => $wcUser,
                'userDetails'  => $user,
                'errorDetails' => [],
            ]));
            report($exception);
            $messageData = [
                'data'   => trans('marketplace.messages.uielementnotfoundMessage'),
                'status' => 0,
            ];
            return \Redirect::route('admin.marketplace.index')->with('message', $messageData);
        }
    }

    /**
     * Create event slot from cronofy UI element
     *
     * @param Request $request
     * @return json
     */
    public function createEventSlot(Event $event, CreateEventSlotRequest $request)
    {
        if (!access()->allow('book-event')) {
            $messageData = [
                'data'   => trans('labels.common_title.unauthorized_access'),
                'status' => 0,
            ];
            return response()->json($messageData, 401);
        }

        try {
            \DB::beginTransaction();
            $slot      = $request->notification['notification']['slot'];
            $payload   = $request->payload;
            $wsUser    = User::where('id', $request->wsId)->first();
            if (empty($wsUser)) {
                $msg = trans('labels.book_event.evnet_booked_err');
                $messageData = [
                    'data'   => $msg,
                    'status' => 0,
                ];
                return response()->json($messageData, 422);
            }

            $wcDetails = $wsUser->wsuser()->first();
            if ($wcDetails->is_authenticate) {
                $authentication = $wsUser->cronofyAuthenticate()->first();
                if (!empty($authentication)) {
                    $this->cronofyRepository->refreshToken($authentication);
                }
            }

            $payload['slot']            = $slot;
            $payload['date']            = Carbon::parse($slot['start'])->format('Y-m-d');
            $payload['timeFrom']        = Carbon::parse($slot['start'])->format('H:i:s');
            $payload['timeTo']          = Carbon::parse($slot['end'])->format('H:i:s');
            $payload['company']         = $request->company;
            $payload['eventbooking_id'] = $request->eventbooking_id;
            $payload['bookingTimezone'] = $request->notification['notification']['tzid'];
            $payload['schedulingId']    = $request->schedulingId;

            // Remove temp records from temp table
            EventBookingLogsTemp::where('id', $request->eventbookinglogsId)->delete();

            $this->cronofyRepository->createEvent($request->all());
            $data                 = $event->confirmBooking($payload);
            if ($request->eventbooking_id > 0) {
                $bookingLog = $this->eventBookingLogs->select('old_presenter_user_id', 'scheduling_id', 'event_id')->where('id', $request->eventbooking_id)->first();
                if (!empty($bookingLog) && $bookingLog['old_presenter_user_id'] > 0) {
                    $this->cronofyRepository->cancelEvent($bookingLog->old_presenter_user_id, $bookingLog->scheduling_id);
                }
            }

            if ($data == 1) {
                \DB::commit();
                $messageData = [
                    'redirectTo' => route('admin.marketplace.index', ['#bookings-tab']),
                    'data'       => trans('labels.book_event.evnet_booked'),
                    'status'     => 1,
                ];
                \Session::put('message', $messageData);
                return response()->json($messageData, 200);
            } else {
                $msg = trans('labels.book_event.evnet_booked_err');
                if ($data == 2 || $data == 3) {
                    $msg = trans('labels.book_event.presenter_unavailable');
                }
                \DB::rollback();
                $messageData = [
                    'data'   => $msg,
                    'status' => $data,
                ];
                return response()->json($messageData, 422);
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
     * Accept event by presnter
     * @param Integer $eventBookingLogId
     * @return json
     */
    public function acceptEvent(int $eventBookingLogId)
    {
        try {
            return $this->eventBookingLogs->acceptEvent($eventBookingLogId);
        } catch (\Exception $exception) {
            return view('errors.401');
        }
    }

    /**
     * Get Company Credit
     * @param Company $company
     * @return boolean
     */
    public function checkCredit(Company $company)
    {
        try {
            return [
                'data' => ($company->credits > 0),
            ];
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
