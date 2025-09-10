<?php declare (strict_types = 1);

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Dashboard;
use App\Models\Industry;
use App\Models\SurveyCategory;
use App\Models\ZcQuestion;
use App\Models\ZcSurveyResponse;
use App\Models\Service;
use Breadcrumbs;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Repositories\AuditLogRepository;
use App\Events\Sendwebsocket;
use App\Events\PrivateWebSocket;

class DashboardController extends Controller
{

    /**
     * @var AuditLogRepository $auditLogRepository
     */
    private $auditLogRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Dashboard $dashboard, AuditLogRepository $auditLogRepository)
    {
        $this->middleware('auth');
        $this->dashboard = $dashboard;
        $this->auditLogRepository = $auditLogRepository;
        $this->bindBreadcrumbs();
    }

    /**
     * bind breadcrumbs of course module
     */
    private function bindBreadcrumbs()
    {
        Breadcrumbs::for('dashboard.question_report', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Question report');
        });
        Breadcrumbs::for('dashboard.question_report.details', function ($trail, $url) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Question report', $url);
            $trail->push('Review free text question', $url);
        });
    }

    /**
     * Show the application dashboard.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // event(new Sendwebsocket);
        // event(new PrivateWebSocket(auth()->user()));
        try {
            $user               = auth()->user();
            $role               = getUserRole($user);
            $companyData        = $user->company->first();
            $wsDetails          = $user->wsuser()->first();
            $wcDetails          = $user->healthCoachUser()->first();
            $services           = [];
            $dtCompanies        = [];
            $industries         = [];
            $assigneeComapnies  = [];
            
            if ($role->group == 'reseller' && $companyData->is_reseller && $companyData->parent_id == null) {
                $assigneeComapnies = Company::select('id')
                ->where('parent_id', $companyData->id)
                ->orWhere('id', $companyData->id)
                ->get()->pluck('id')->toArray();
            } else if ($role->group == 'company' || ($role->group == 'reseller' && $companyData->parent_id != null)) {
                $assigneeComapnies[] =  $companyData->id ?? [];
            }
            $services = Service::select('services.id', 'services.name');
            
            if($role->group != 'zevo'){
                $services = $services->join('digital_therapy_services', 'digital_therapy_services.service_id', '=', 'services.id')
                ->whereIn('digital_therapy_services.company_id', $assigneeComapnies);
            }
            $services = $services->pluck('name', 'id')->toArray();

            if (!access()->allow('view-dashboard')) {
                $domains         = getPortalDomain();
                $companyBranding = (!empty($companyData) ? $companyData->branding : null);
                $portal_domain   = (!empty($companyBranding) && !empty($companyBranding->portal_domain) ? $companyBranding->portal_domain : last($domains));
                return view('dashboard.app-user-index', [
                    'ga_title'            => trans('page_title.dashboard.index'),
                    'user'                => $user,
                    'company'             => $companyData,
                    'companies'           => [],
                    'departments'         => [],
                    'locations'           => [],
                    'age'                 => [],
                    'dtCompanies'         => [],
                    'dtCompaniesId'       => null,
                    'companiesId'         => null,
                    'industry'            => [],
                    'resellerType'        => null,
                    'childResellerType'   => null,
                    'auditTabVisibility'  => null,
                    'dtTabVisibility'     => (!empty($wsDetails) || !empty($wcDetails)),
                    'portal_domain'       => $portal_domain,
                    'role'                => $role,
                    'wsDetails'           => $wsDetails,
                    'wcDetails'           => $wcDetails,
                    'services'            => $services,
                    'serviceIds'          => null,
                    'calendarCount'       => $user->cronofyCalendar()->count()
                ]);
            }

            $age       = config('zevolifesettings.age');
            $companies = Company::select('id', 'name');
            if ($role->group == 'reseller' && $companyData->parent_id == null) {
                $companies->where('id', $companyData->id)->orWhere('parent_id', $companyData->id);
            }
            $companies  = $companies->pluck('name', 'id')->toArray();

            $dtCompanies = Company::select('companies.id', 'companies.name')->leftJoin('cp_company_plans', 'companies.id', '=', 'cp_company_plans.company_id')
            ->leftJoin('cp_plan', 'cp_plan.id', '=', 'cp_company_plans.plan_id')
            ->leftJoin('cp_plan_features', 'cp_plan_features.plan_id', '=', 'cp_plan.id')
            ->leftJoin('cp_features', 'cp_features.id', '=', 'cp_plan_features.feature_id');
            if ($role->group == 'reseller' && $companyData->parent_id == null) {
                $dtCompanies = $dtCompanies->where(function ($q) use($companyData) {
                    $q->where('companies.id', $companyData->id)
                        ->orWhere('companies.parent_id', $companyData->id);
                });
            }
            $dtCompanies = $dtCompanies->where(function ($q) {
                $q->where('cp_features.slug', 'digital-therapy')
                    ->orWhere(function ($q1) {
                        $q1->where('cp_plan.slug', 'eap-with-challenge')
                            ->orWhere('cp_plan.slug', 'eap');
                    });
            });

            $dtCompanies = $dtCompanies->pluck('name', 'id')->toArray();
            $resellerType      = null;
            $childResellerType = null;
            if ($role->group == 'reseller' && $companyData->parent_id == null) {
                $industries = Industry::select('industries.id', 'industries.name')
                    ->join('companies', 'companies.industry_id', '=', 'industries.id')
                    ->where('companies.id', $companyData->id)
                    ->orWhere('companies.parent_id', $companyData->id)
                    ->pluck('name', 'id')
                    ->toArray();
                $resellerType = 1;
            }

            if ($role->group == 'reseller' && $companyData->parent_id != null) {
                $childResellerType = 1;
            } elseif ($role->group == 'company' && is_null($companyData->parent_id) && !$companyData->is_reseller) {
                $childResellerType = 1;
            }

            $company          = $user->company->first();
            $auditTabVisibility     = $dtTabVisibility = false;
            
            $isAuditTabAccess               = getCompanyPlanAccess($user, 'wellbeing-score-card');
            $isAuditTabAccessForReseller    = getDTAccessForParentsChildCompany($user, 'wellbeing-scorecard');
            $isUsageTabAccessForReseller    = getDTAccessForParentsChildCompany($user, 'explore');
            $isEventTabAccess               = getCompanyPlanAccess($user, 'event');
            $isEventTabAccessForReseller    = getDTAccessForParentsChildCompany($user, 'event');

            $departments      = $locations = [] ;
            if ($company) {
                $departments  = $company->departments->pluck('name', 'id');
                $locations    = $company->locations->pluck('name', 'id');
            }
            
            if ($role->group == 'zevo') {
                $auditTabVisibility = (!is_null(ZcSurveyResponse::first()));
                $dtTabVisibility = true;
            } elseif ($role->group == 'reseller' && $company->parent_id == null) {
                $companiesIds   = company::select('id')->where('id', $company->id)->orWhere('parent_id', $company->parent_id)->get()->pluck('id')->toArray();
                $auditTabVisibility = (!is_null(ZcSurveyResponse::whereIn('company_id', $companiesIds)->first()));
                $dtTabVisibility = getDTAccessForParentsChildCompany($user, 'digital-therapy');
            } else {
                $auditTabVisibility = (!is_null(ZcSurveyResponse::where('company_id', $company->id)->first()));
                if ($role->group == 'company' && !$isAuditTabAccess) {
                    $auditTabVisibility = false;
                }
                $companyPlan  = $company->companyplan()->first();
                if ($role->group == 'company' && ($companyPlan->slug == 'eap' || $companyPlan->slug == 'eap-with-challenge')) {
                    $dtTabVisibility = true;
                }

                if ($role->group == 'reseller' && ($company->parent_id != null)) {
                    $dtTabVisibility = getCompanyPlanAccess($user, 'digital-therapy');
                }
            }

            $data = [
                'role'                  => $role,
                'age'                   => $age,
                'company'               => $company,
                'companies'             => $companies,
                'companiesId'           => (!empty($companies)) ? implode(',', array_keys($companies)) : [],
                'dtCompanies'           => $dtCompanies,
                'dtCompaniesId'         => (!empty($dtCompanies)) ? implode(',', array_keys($dtCompanies)) : [],
                'industry'              => $industries,
                'resellerType'          => $resellerType,
                'childResellerType'     => $childResellerType,
                'departments'           => $departments,
                'locations'             => $locations,
                'auditTabVisibility'    => ($role->group == 'zevo') || ($auditTabVisibility && (($isAuditTabAccessForReseller && $role->group == 'reseller') || ($isAuditTabAccess && $role->group == 'company'))),
                'dtTabVisibility'       => $dtTabVisibility,
                'wsDetails'             => $wsDetails,
                'wcDetails'             => $wcDetails,
                'services'              => $services,
                'usageTabVisibility'    => (($role->group == 'zevo') || ($isUsageTabAccessForReseller && $role->group == 'reseller') || $role->group == 'company'),
                'eventTabVisibility'    => ($role->group == 'zevo') || ($isEventTabAccessForReseller && $role->group == 'reseller') || ($isEventTabAccess && $role->group == 'company'),
                'serviceIds'            => (!empty($services)) ? implode(',', array_keys($services)) : [],
                'genders'               => config('zevolifesettings.gender'),
                'ga_title'              => trans('page_title.dashboard.index'),
            ];
            return view('dashboard.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * Get App Usage Tab Data
     *
     * @param Request $request
     * @return Json
     */
    public function getAppUsageTabData(Request $request)
    {
        try {
            $payload = $request->all();
            $tier    = $payload['tier'] ?? 1;

            $data = [];
            switch ($tier) {
                case 1:
                    // Users, Meditation Hours Blocks
                    $data = $this->dashboard->getAppUsageTabTier1Data($payload);
                    break;

                case 2:
                    // Meditation - Popular Categories/Top 10
                    $data = $this->dashboard->getAppUsageTabTier2Data($payload);
                    break;

                case 3:
                    // Recipe - Top 5 / Webinar - Popular Categories/Top 10
                    $data = $this->dashboard->getAppUsageTabTier3Data($payload);
                    break;

                case 4:
                    // Masterclass - Popular Categories/Top 10 / Feed - Popular Categories/Top 10
                    $data = $this->dashboard->getAppUsageTabTier4Data($payload);
                    break;

                default:
                    $data = [];
                    break;
            }

            return response()->json($data);
        } catch (QueryException $exception) {
            report($exception);
            return response()->json($this->getQueryExceptionMessageData($exception));
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
     * Get Physical Tab Data
     *
     * @param Request $request
     * @return Json
     */
    public function getPhysicalTabData(Request $request)
    {
        try {
            $payload = $request->all();
            $tier    = $payload['tier'] ?? 1;
            $data    = [];
            switch ($tier) {
                case 1:
                    // Physical category and sub-categories data of health score
                    $data = $this->dashboard->getPhysicalTabTier1Data($payload);
                    break;

                case 2:
                    // Steps Range, Exercise Range
                    $data = $this->dashboard->getPhysicalTabTier2Data($payload);
                    break;

                case 3:
                    // Popular exercises
                    $data = $this->dashboard->getPhysicalTabTier3Data($payload);
                    break;

                case 4:
                    // Recipe views, BMI
                    $data = $this->dashboard->getPhysicalTabTier4Data($payload);
                    break;

                default:
                    $data = [];
                    break;
            }

            return response()->json($data);
        } catch (QueryException $exception) {
            report($exception);
            return response()->json($this->getQueryExceptionMessageData($exception));
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
     * Get Psychological Tab Data
     *
     * @param Request $request
     * @return Json
     */
    public function getPsychologicalTabData(Request $request)
    {
        try {
            $payload = $request->all();
            $tier    = $payload['tier'] ?? 1;

            $data = [];
            switch ($tier) {
                case 1:
                    // Psychological category and sub-categories data of health score
                    $data = $this->dashboard->getPsychologicalTabTier1Data($payload);
                    break;

                case 2:
                    // Meditation hours chart
                    $data = $this->dashboard->getPsychologicalTabTier2Data($payload);
                    break;

                case 3:
                    // Popular meditation categories and top 10 meditations chart
                    $data = $this->dashboard->getPsychologicalTabTier3Data($payload);
                    break;

                case 4:
                    // Moods analysis
                    $data = $this->dashboard->getPsychologicalTabTier4Data($payload);
                    break;

                default:
                    $data = [];
                    break;
            }

            return response()->json($data);
        } catch (QueryException $exception) {
            report($exception);
            return response()->json($this->getQueryExceptionMessageData($exception));
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
     * Get Audit Tab Data
     *
     * @param Request $request
     * @return Json
     */
    public function getAuditTabData(Request $request)
    {
        try {
            $payload = $request->all();
            $tier    = $payload['tier'] ?? 1;
            $data    = [];
            switch ($tier) {
                case 1:
                    // Audit tab company score gauge and line charts
                    $data = $this->dashboard->getAuditTabTier1Data($payload);
                    break;
                case 2:
                    // Audit tab category wise tabs
                    $data = $this->dashboard->getAuditTabTier2Data($payload);
                    break;
                case 3:
                    // Audit tab category wise company score gauge and line charts
                    $data = $this->dashboard->getAuditTabTier3Data($payload);
                    break;
                case 4:
                    // Audit tab subcategory wise company score gauge charts
                    $data = $this->dashboard->getAuditTabTier4Data($payload);
                    break;
                default:
                    $data = [];
                    break;
            }

            return response()->json($data);
        } catch (QueryException $exception) {
            report($exception);
            return response()->json($this->getQueryExceptionMessageData($exception));
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
     * Get question report data
     *
     * @param Request $request
     * @return view
     */
    public function questionReport(SurveyCategory $category, Request $request)
    {
        if (!access()->allow('view-dashboard')) {
            abort(401);
        }

        try {
            $user     = auth()->user();
            $role     = getUserRole();
            $timezone = !empty($user->timezone) ? $user->timezone : config('app.timezone');
            $company  = $user->company->first();

            $companies = Company::select('id', 'name');
            if ($role->group == 'reseller' && $company->parent_id == null) {
                $companies->where(function ($where) use ($company) {
                    $where->where('id', $company->id)->orWhere('parent_id', $company->id);
                });
            }
            $companies = $companies->pluck('name', 'id')->toArray();

            $company_id    = (($role->group == 'zevo') ? null : $company->id);
            $rquestCompany = ($request->company ?? null);
            $fromDate      = (!empty($request->from) && strtotime($request->from) !== false ? $request->from : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subMonths(5)->format('Y-m-d 00:00:00'));
            $toDate        = (!empty($request->to) && strtotime($request->to) !== false ? $request->to : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->format('Y-m-d H:i:s'));
            $companyIds    = [];
            if ($role->group == 'company' || ($role->group == 'reseller' && $company->parent_id != null)) {
                if ($company_id != $rquestCompany) {
                    return \view('errors.401');
                }
            } else {
                if (!empty($company_id) && !array_key_exists($company_id, $companies)) {
                    return \view('errors.404');
                }
                $company_id = $rquestCompany;
            }

            if ($role->group == 'reseller' && $company->parent_id == null) {
                $companyIds = company::select('id')->where('id', $company->id)->orWhere('parent_id', $company->id)->get()->pluck('id')->toArray();
            }

            $categories = ZcSurveyResponse::select('zc_survey_responses.category_id AS id', 'zc_categories.display_name AS category_name')
                ->join('users', function ($join) {
                    $join->on('users.id', '=', 'zc_survey_responses.user_id');
                })
                ->join('zc_categories', function ($join) {
                    $join->on('zc_categories.id', '=', 'zc_survey_responses.category_id');
                })
                ->where(function ($query) use ($timezone, $fromDate, $toDate, $company_id, $companyIds) {
                    $query
                        ->where('users.is_blocked', 0)
                        ->where('zc_categories.status', 1)
                        ->whereRaw("(CONVERT_TZ(zc_survey_responses.created_at, ?, ?) BETWEEN ? AND ?)", ['UTC',$timezone, $fromDate, $toDate]);
                    if (!empty($companyIds)) {
                        $query->whereIn('zc_survey_responses.company_id', $companyIds);
                    } elseif (!empty($company_id)) {
                        $query->where('zc_survey_responses.company_id', $company_id);
                    }
                })
                ->groupBy('zc_survey_responses.category_id')
                ->get()
                ->pluck('category_name', 'id')
                ->toArray();

            if (!empty($categories) && !array_key_exists($category->id, $categories)) {
                return \view('errors.401');
            }

            $finalCategories = [];
            foreach ($categories as $key => $value) {
                $subCategory           = SurveyCategory::find($key, ['id']);
                $finalCategories[$key] = [
                    'name'  => $value,
                    'image' => $subCategory->logo,
                ];
            }

            $resellerType = null;
            if ($role->group == 'reseller' && $company->parent_id == null) {
                $resellerType = 1;
            }

            $departments = [];
            if ($company) {
                $departments = $company->departments->pluck('name', 'id');
            }

            $locations = [];
            if ($locations) {
                $locations = $company->locations->pluck('name', 'id');
            }
            
            $data = [
                'role'                 => $role,
                'companies'            => $companies,
                'departments'          => $departments,
                'locations'            => $locations,
                'company'              => $company,
                'companiesId'          => (!empty($companies)) ? implode(',', array_keys($companies)) : [],
                'resellerType'         => $resellerType,
                'company_id'           => $company_id,
                'categories'           => $finalCategories,
                'categoriesVisibility' => (sizeof($finalCategories) > 0 ? 'block' : 'none'),
                'requestParams'        => json_encode($request->only('from', 'to', 'company') + ['category' => $category->id]),
                'ga_title'             => trans('page_title.dashboard.question_report'),
            ];

            return view('dashboard.question-report', $data);
        } catch (QueryException $exception) {
            report($exception);
            return response()->json($this->getQueryExceptionMessageData($exception));
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * Get Question Report Data
     *
     * @param Request $request
     * @return Json
     */
    public function getQuestionReportData(Request $request)
    {
        try {
            $payload = $request->all();
            $tier    = ($payload['tier'] ?? 1);

            $data = [];
            switch ($tier) {
                case 1:
                    // Question report categorys tabs
                    $data = $this->dashboard->getQuestionReportTier1Data($payload);
                    break;
                case 2:
                    // Question report category score data and subcategory progressars and tabs
                    $data = $this->dashboard->getQuestionReportTier2Data($payload);
                    break;
                case 3:
                    // Subcategory waise question table data
                    return $this->dashboard->getQuestionReportTier3Data($payload);
                    break;
                default:
                    $data = [];
                    break;
            }

            return response()->json($data);
        } catch (QueryException $exception) {
            report($exception);
            return response()->json($this->getQueryExceptionMessageData($exception));
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
     * Get Question responses
     *
     * @param Request $request
     * @return view
     */
    public function questionReportDetails(ZcQuestion $question, Request $request)
    {
        $user     = auth()->user();
        $role     = getUserRole();
        $company  = $user->company->first();

        $companies = Company::select('id', 'name');
        if ($role->group == 'reseller' && $company->parent_id == null) {
            $companies->where(function ($where) use ($company) {
                $where->where('id', $company->id)->orWhere('parent_id', $company->id);
            });
        }
        $companies = $companies->pluck('name', 'id')->toArray();

        $company_id    = (($role->group == 'zevo') ? null : $company->id);
        $rquestCompany = ($request->company ?? null);

        if ($role->group == 'company' || ($role->group == 'reseller' && $company->parent_id != null)) {
            if ($company_id != $rquestCompany) {
                return \view('errors.401');
            }
            $companies = [$company->id => $company->name];
        } else {
            if (!empty($rquestCompany) && !array_key_exists($rquestCompany, $companies)) {
                return \view('errors.404');
            }
            $company_id = $rquestCompany;
        }

        $question = $question
            ->with(['category' => function ($query) {
                $query->select('id', 'display_name');
            }, 'subcategory' => function ($query) {
                $query->select('id', 'display_name');
            }])
            ->where('id', $question->id)
            ->first();

        $data = [
            'role'          => $role,
            'companies'     => $companies,
            'company'       => $company,
            'company_id'    => $company_id,
            'question'      => $question,
            'requestParams' => $request->only('from', 'to', 'company'),
            'pagination'    => config('zevolifesettings.datatable.pagination.long'),
            'ga_title'      => trans('page_title.dashboard.review_free_text_question'),
        ];

        return view('dashboard.question-details', $data);
    }

    /**
     * To get an answers of question
     *
     * @param ZcQuestion $question
     * @param Request $request
     * @return JSON
     */
    public function questionAnswers(ZcQuestion $question, Request $request)
    {
        try {
            return $this->dashboard->getQuestionReportTier4Data($question, $request->all());
        } catch (QueryException $exception) {
            report($exception);
            return response()->json($this->getQueryExceptionMessageData($exception));
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
     * Get Booking Data
     *
     * @param Request $request
     * @return Json
     */
    public function getBookingTabData(Request $request)
    {
        try {
            $payload = $request->all();
            $tier    = $payload['tier'] ?? 1;
            $data    = [];

            switch ($tier) {
                case 1:
                    // Booking tab Upcoming Events Data [ Total / Today / Next 7 Days / Next 30 Days ]
                    $data = $this->dashboard->getBookingTabTier1Data($payload);
                    break;
                case 2:
                    // Booking tab Events Revenue Data [ Completed Amount / Complated total / Booked Total / Cancelled Total ]
                    $data = $this->dashboard->getBookingTabTier2Data($payload);
                    break;
                case 3:
                    // Today's Event calendar
                    $data = $this->dashboard->getBookingTabTier3Data($payload);
                    break;
                case 4:
                    // Today's Event calendar
                    $data = $this->dashboard->getBookingTabTier4Data($payload);
                    break;
                default:
                    $data = [];
                    break;
            }

            return response()->json($data);
        } catch (QueryException $exception) {
            report($exception);
            return response()->json($this->getQueryExceptionMessageData($exception));
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
     * Get EAP Activity Data
     *
     * @param Request $request
     * @return Json
     */
    public function getEapActivityTabData(Request $request)
    {
        try {
            $payload = $request->all();
            $tier    = $payload['tier'] ?? 1;
            $data    = [];

            switch ($tier) {
                case 1:
                    // EAP Activity First Block Data [ Today's / Upcoming / Completed / Cancelled ]
                    $data = $this->dashboard->getEapActivityTabTier1Data($payload);
                    break;
                case 2:
                    // Appointment Trend
                    $data = $this->dashboard->getEapActivityTabTier2Data($payload);
                    break;
                case 3:
                    // Appointment Trend
                    $data = $this->dashboard->getEapActivityTabTier3Data($payload);
                    break;
                case 4:
                    // Appointment Trend
                    $data = $this->dashboard->getEapActivityTabTier4Data($payload);
                    break;
                default:
                    $data = [];
                    break;
            }

            return response()->json($data);
        } catch (QueryException $exception) {
            report($exception);
            return response()->json($this->getQueryExceptionMessageData($exception));
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
     * Get Digital Therapy Activity Data
     *
     * @param Request $request
     * @return Json
     */
    public function getDigitalTherapyTabData(Request $request)
    {
        try {
            $payload = $request->all();
            $tier    = $payload['tier'] ?? 1;
            $data    = [];

            switch ($tier) {
                case 1:
                    // EAP Activity First Block Data [ Today's / Upcoming / Completed / Cancelled ]
                    $data = $this->dashboard->getDigitalTherapyTabTier1Data($payload);
                    break;
                case 2:
                    // Appointment Trend
                    $data = $this->dashboard->getDigitalTherapyTabTier2Data($payload);
                    break;
                case 3:
                    // Appointment Trend
                    $data = $this->dashboard->getDigitalTherapyTabTier3Data($payload);
                    break;
                case 4:
                    // Appointment Trend
                    $data = $this->dashboard->getDigitalTherapyTabTier4Data($payload);
                    break;
                default:
                    $data = [];
                    break;
            }

            return response()->json($data);
        } catch (QueryException $exception) {
            report($exception);
            return response()->json($this->getQueryExceptionMessageData($exception));
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }
}
