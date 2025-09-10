<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\EventBookingLogs;
use App\Models\SubCategory;
use App\Models\User;
use App\Http\Requests\Admin\NpsReportExportRequest;
use Breadcrumbs;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BookingReportController extends Controller
{
    /**
     * variable to store the model object
     * @var EventBookingLogs $eventBookingLogs
     */
    protected $eventBookingLogs;

    /**
     * constructor to initialize variables
     * @var EventBookingLogs $eventBookingLogs
     */
    public function __construct(EventBookingLogs $eventBookingLogs)
    {
        $this->eventBookingLogs = $eventBookingLogs;
        $this->bindBreadcrumbs();
    }

    /*
     * Bind breadcrumbs of role module
     */
    public function bindBreadcrumbs()
    {
        Breadcrumbs::for('bookingreport.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Bookings Report');
        });
        Breadcrumbs::for('bookingreport.summary', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Bookings Report', route('admin.reports.booking-report', '#summary-view-tab'));
            $trail->push('Bookings Report Details');
        });
        Breadcrumbs::for('bookingreport.calendar', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Bookings Report', route('admin.reports.booking-report', '#calender-view-tab'));
            $trail->push('Bookings Report Details');
        });
    }

    /**
     * @param Request $request
     * @return View
     * @throws Exception
     */
    public function index(Request $request)
    {
        $user                       = auth()->user();
        $timezone                   = (!empty($user->timezone) ? $user->timezone : config('app.timezone'));
        $role                       = getUserRole($user);
        $checkPlanAccess            = getCompanyPlanAccess($user, 'event');
        $checkPlanAccessForReseller = getDTAccessForParentsChildCompany($user, 'event');
        if (!access()->allow('booking-report-detailed-view') || !access()->allow('booking-report-summary-view') || ($role->group == 'company' &&  !$checkPlanAccess) || ($role->group == 'reseller' &&  !$checkPlanAccessForReseller)) {
            abort(403);
        }

        try {
            $company            = $user->company()->first();
            $presenterDivClass  = "";
            $expertiseDivClass  = "";
            $timezone           = (!empty($user->timezone) ? $user->timezone : config('app.timezone'));
            $roleType           = 'zsa';
            $loginemail         = ($user->email ?? "");
            $presenters         = User::select('users.id', \DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS name"))
                ->join('ws_user', 'ws_user.user_id', '=', 'users.id')
                ->where('ws_user.responsibilities', '!=', 1)
                ->whereNull('users.deleted_at')
                ->where('ws_user.is_cronofy', true);
            $status = config('zevolifesettings.event-status-master');

            // check if zevo then show all reseller parent and child companies
            if ($role->group == 'zevo') {
                $roleType  = 'zsa';
                $companies = EventBookingLogs::select('event_booking_logs.company_id', 'companies.name')
                    ->join('companies', 'companies.id', '=', 'event_booking_logs.company_id')
                    ->groupBy('event_booking_logs.company_id')
                    ->pluck('name', 'company_id')
                    ->toArray();
            } elseif ($role->group == 'company') {
                // if ZCA then show their own company only
                $companies = $company;
                $roleType  = 'zca';
            } else {
                // check if RSA then show their own and child companies
                if ($company->is_reseller) {
                    $roleType  = 'rsa';
                    $companies = Company::select('id', 'name')
                        ->where(function ($query) use ($company) {
                            $query->where('id', $company->id)->orWhere('parent_id', $company->id);
                        })
                        ->pluck('name', 'id')
                        ->toArray();
                } else {
                    // if RCA then show their own company only
                    $companies = $company;
                    $roleType  = 'rca';
                }
            }

            $nowInUTC    = now(config('app.timezone'))->toDateTimeString();
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
            $expertise   = SubCategory::where(['category_id' => 6, 'status' => 1])->get()->pluck('name', 'id')->toArray();

            $presentersCount = $presenters->get()->count();
            if ($presentersCount > 10) {
                $presenterDivClass = "custom-scrollbar";
            }
            if (count($expertise) > 10) {
                $expertiseDivClass = "custom-scrollbar";
            }

            $data = [
                'timezone'          => $timezone,
                'roleType'          => $roleType,
                'company'           => $company,
                'companies'         => $companies,
                'presenters'        => $presenters->get()->pluck('name', 'id')->toArray(),
                'presenterDivClass' => $presenterDivClass,
                'expertiseDivClass' => $expertiseDivClass,
                'categories'        => $tabCategory->pluck('name', 'id')->toArray(),
                'status'            => $status->forget(['1', '2'])->pluck('text', 'id')->toArray(),
                'summaryStatus'     => $status->forget(['1', '2', '3'])->pluck('text', 'id')->toArray(),
                'expertise'         => $expertise,
                'pagination'        => config('zevolifesettings.datatable.pagination.long'),
                'ga_title'          => trans('page_title.event-booking.event-booking-report'),
                'loginemail'        => $loginemail
            ];
            return \view('admin.report.event.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * @param Request $request
     * @return json
     * @throws Exception
     */
    public function detailedReport(Request $request)
    {
        $user               = auth()->user();
        $role               = getUserRole($user);
        $company            = $user->company()->first();
        $checkEventRestrict = getCompanyPlanAccess($user, 'event');

        if (!access()->allow('booking-report-detailed-view') || ($role->group == 'company' && !empty($company) && !$checkEventRestrict)) {
            $messageData = [
                'data'   => trans('bookingreport.message.unauthorized_access'),
                'status' => 0,
            ];
            return response()->json($messageData, 401);
        }

        try {
            return $this->eventBookingLogs->getDetailedReport($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('bookingreport.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData, 500);
        }
    }

    /**
     * @param Request $request
     * @return json
     * @throws Exception
     */
    public function summaryReport(Request $request)
    {
        $user               = auth()->user();
        $role               = getUserRole($user);
        $checkEventRestrict = getCompanyPlanAccess($user, 'event');

        if (!access()->allow('booking-report-summary-view') || ($role->group == 'company' && !$checkEventRestrict)) {
            $messageData = [
                'data'   => trans('bookingreport.message.unauthorized_access'),
                'status' => 0,
            ];
            return response()->json($messageData, 401);
        }

        try {
            return $this->eventBookingLogs->getSummaryReport($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('bookingreport.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData, 500);
        }
    }

    /**
     * @param Company $company
     * @return View
     * @throws Exception
     */
    public function bookingReportComapnyWise(Company $company)
    {
        $user               = auth()->user();
        $role               = getUserRole($user);
        $checkEventRestrict = getCompanyPlanAccess($user, 'event');
        if (!access()->allow('booking-report-summary-view') || ($role->group == 'company' && !$checkEventRestrict)) {
            abort(403);
        }

        try {
            $user                = auth()->user();
            $timezone            = (!empty($user->timezone) ? $user->timezone : config('app.timezone'));
            $role                = getUserRole($user);
            $loggedInUserCompany = $user->company()->first();
            $status              = config('zevolifesettings.event-status-master');
            $presenters         = User::select('users.id', \DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS name"))
                ->join('ws_user', 'ws_user.user_id', '=', 'users.id')
                ->where('ws_user.responsibilities', '!=', 1)
                ->whereNull('users.deleted_at')
                ->where('ws_user.is_cronofy', true);
            $isExportButton = false;

            if ($role->group == 'zevo' || ($role->group == 'reseller' && is_null($loggedInUserCompany->parent_id))) {
                $isExportButton = true;
            }
            if ($role->group == 'zevo') {
            } elseif (!is_null($loggedInUserCompany) && $loggedInUserCompany->is_reseller) {
                $assigneeComapnies = Company::select('id')
                    ->where('parent_id', $loggedInUserCompany->id)
                    ->orWhere('id', $loggedInUserCompany->id)
                    ->get()->pluck('id')->toArray();

                if (!in_array($company->id, $assigneeComapnies)) {
                    return view('errors.401');
                }
            }

            $data = [
                'company'        => $company,
                'timezone'       => $timezone,
                'isExportButton' => $isExportButton,
                'presenters'     => $presenters->get()->pluck('name', 'id')->toArray(),
                'status'         => $status->forget(['1', '2'])->pluck('text', 'id')->toArray(),
                'pagination'     => config('zevolifesettings.datatable.pagination.long'),
                'ga_title'       => trans('page_title.event-booking.company-wise-report'),
                'loginemail'     => ($user->email ?? "")
            ];

            return \view('admin.report.event.company-wise-report', $data);
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * @param Request $request
     * @param Company $company
     * @return json
     * @throws Exception
     */
    public function getBookingReportComapnyWise(Company $company, Request $request)
    {
        $user               = auth()->user();
        $role               = getUserRole($user);
        $checkEventRestrict = getCompanyPlanAccess($user, 'event');
        if (!access()->allow('booking-report-summary-view') || ($role->group == 'company' && !$checkEventRestrict)) {
            $messageData = [
                'data'   => trans('bookingreport.message.unauthorized_access'),
                'status' => 0,
            ];
            return response()->json($messageData, 401);
        }

        try {
            return $this->eventBookingLogs->getBookingReportComapnyWise($company, $request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('bookingreport.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData, 500);
        }
    }

    /**
     * @param Request $request
     * @return json
     * @throws Exception
     */
    public function calendarReport(Request $request)
    {
        try {
            $result = $this->eventBookingLogs->getCalenderReport($request->all());
            return response()->json($result);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('bookingreport.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData, 500);
        }
    }

      /**
     * @param NpsReportExportRequest $request
     * @return RedirectResponse
     */
    public function exportBookingDetailReport(NpsReportExportRequest $request)
    {
        if (!access()->allow('booking-report-detailed-view') || !access()->allow('booking-report-summary-view')) {
            abort(403);
        }

        try {
            \DB::beginTransaction();
            $data = $this->eventBookingLogs->exportBookingReportDataEntity($request->all());
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
            return \Redirect::route('admin.reports.booking-report')->with('message', $messageData);
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('challenges.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /** Company wise booking report export
     * @param NpsReportExportRequest $request
     * @return RedirectResponse
     */
    public function exportBookingReportCompanyWise(NpsReportExportRequest $request, Company $company)
    {
        $user               = auth()->user();
        $role               = getUserRole($user);
        $checkEventRestrict = getCompanyPlanAccess($user, 'event');
        if (!access()->allow('booking-report-summary-view') || ($role->group == 'company' && !$checkEventRestrict)) {
            $messageData = [
                'data'   => trans('bookingreport.message.unauthorized_access'),
                'status' => 0,
            ];
            return response()->json($messageData, 401);
        }

        try {
            \DB::beginTransaction();
            $data = $this->eventBookingLogs->exportBookingReportCompanyWiseDataEntity($request->all(), $company);
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
            return \Redirect::route('admin.reports.booking-report-comapny-wise', $company)->with('message', $messageData);
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('challenges.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * To view calendar booking details
     * @param Request $eventBookingId
     * @return View
     */
    public function calendarBookingDetails(EventBookingLogs $eventBookingId)
    {
        try {
            $user               = auth()->user();
            $role               = getUserRole($user);
            $checkEventRestrict = getCompanyPlanAccess($user, 'event');
            $company            = $user->company()->select('companies.id', 'companies.is_reseller', 'companies.parent_id')->first();
            $dateFormat         = config('zevolifesettings.date_format.default_date');
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
                ->setTimezone($timezone)->format($dateFormat);
            $startTime = Carbon::parse("{$eventBookingId->booking_date} {$eventBookingId->start_time}", $appTimezone)
                ->setTimezone($timezone)->format('h:i A');
            $endTime = Carbon::parse("{$eventBookingId->booking_date} {$eventBookingId->end_time}", $appTimezone)
                ->setTimezone($timezone)->format('h:i A');

            if (!empty($eventBookingId->meta->start_time) && !empty($eventBookingId->meta->end_time)) {
                $presenterTimeZone = (!empty($eventBookingId->meta->timezone) ? $eventBookingId->meta->timezone : $appTimezone);
                $slotStartTime     = Carbon::parse("{$eventBookingId->booking_date} {$eventBookingId->meta->start_time}", $presenterTimeZone)
                    ->setTimezone($timezone)->format('h:i A');
                $slotEndTime = Carbon::parse("{$eventBookingId->booking_date} {$eventBookingId->meta->end_time}", $presenterTimeZone)
                    ->setTimezone($timezone)->format('h:i A');

                $presenterString .= "<span>{$slotStartTime} - {$slotEndTime}</span>";
            }

            if (!empty($eventBookingId->meta->presenter)) {
                $presenterString .= "<span class='mr-5'>{$eventBookingId->meta->presenter}</span>";
            }

            $data = [
                'eventBookingId'             => $eventBookingId,
                'event'                      => $event,
                'eventCompany'               => $eventBookingId->company,
                'feedLabelVisibility'        => $feedLabelVisibility,
                'editBtn'                    => $editBtn,
                'cancelBtn'                  => $cancelBtn,
                'bookingDate'                => $bookingDate,
                'startTime'                  => $startTime,
                'endTime'                    => $endTime,
                'presenterString'            => $presenterString,
                'complementaryOptVisibility' => $complementaryOptVisibility,
                'locationTypes'              => config('zevolifesettings.event-location-type'),
                'ga_title'                   => trans('page_title.marketplace.booking_details'),
            ];

            return \view('admin.report.event.calendar-event-details', $data);
        } catch (\Exception $exception) {
            report($exception);
            return abort(500);
        }
    }
}
