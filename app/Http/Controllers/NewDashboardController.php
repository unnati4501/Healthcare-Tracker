<?php declare (strict_types = 1);

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Industry;
use App\Models\NewDashboard;
use App\Models\SurveyCategory;
use App\Models\ZcQuestion;
use App\Models\ZcSurveyResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NewDashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(NewDashboard $newDashboard)
    {
        $this->middleware('auth');
        $this->newDashboard = $newDashboard;
    }

    /**
     * Show the application dashboard.
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $data['ga_title'] = trans('page_title.dashboard.index');
        if (!access()->allow('view-dashboard')) {
            return view('dashboard.app-user-index', $data);
        }

        try {
            $user        = auth()->user();
            $companyData = $user->company->first();
            $role        = getUserRole($user);
            $age         = config('zevolifesettings.age');

            $companies = Company::select('id', 'name');
            if ($role->group == 'reseller' && $companyData->parent_id == null) {
                $companies->where('id', $companyData->id)->orWhere('parent_id', $companyData->id);
            }
            $companies  = $companies->pluck('name', 'id')->toArray();
            $industries = [];

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
            }
            $company        = $user->company->first();
            $tab4Visibility = false;

            $departments = [];
            if ($company) {
                $departments = $company->departments->pluck('name', 'id');
            }

            if ($role->group == 'zevo') {
                $tab4Visibility = (!is_null(ZcSurveyResponse::first()));
            } elseif ($role->group == 'reseller' && $company->parent_id == null) {
                $companiesIds   = company::select('id')->where('id', $company->id)->orWhere('parent_id', $company->parent_id)->get()->pluck('id')->toArray();
                $tab4Visibility = (!is_null(ZcSurveyResponse::whereIn('company_id', $companiesIds)->first()));
            } else {
                $tab4Visibility = (!is_null(ZcSurveyResponse::where('company_id', $company->id)->first()));
            }

            $data = [
                'role'              => $role,
                'age'               => $age,
                'company'           => $company,
                'companies'         => $companies,
                'companiesId'       => (!empty($companies)) ? implode(',', array_keys($companies)) : [],
                'industry'          => $industries,
                'resellerType'      => $resellerType,
                'childResellerType' => $childResellerType,
                'departments'       => $departments,
                'tab4Visibility'    => $tab4Visibility,
                'genders'           => config('zevolifesettings.gender'),
                'ga_title'          => trans('page_title.dashboard.index'),
            ];

            return view('newDashboard.index', $data);
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
                    // Users, Teams, Challenges Blocks
                    $data = $this->newDashboard->getAppUsageTabTier1Data($payload);
                    break;

                case 2:
                    // Steps Period, Calories Period
                    $data = $this->newDashboard->getAppUsageTabTier2Data($payload);
                    break;

                case 3:
                    // Popular feeds, Sync details
                    $data = $this->newDashboard->getAppUsageTabTier3Data($payload);
                    break;

                case 4:
                    // Superstars blocks
                    $data = $this->newDashboard->getAppUsageTabTier4Data($payload);
                    break;

                default:
                    $data = [];
                    break;
            }

            return response()->json($data);
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

            $data = [];
            switch ($tier) {
                case 1:
                    // Physical category and sub-categories data of health score
                    $data = $this->newDashboard->getPhysicalTabTier1Data($payload);
                    break;

                case 2:
                    // Steps Range, Exercise Range
                    $data = $this->newDashboard->getPhysicalTabTier2Data($payload);
                    break;

                case 3:
                    // Popular exercises
                    $data = $this->newDashboard->getPhysicalTabTier3Data($payload);
                    break;

                case 4:
                    // Recipe views, BMI
                    $data = $this->newDashboard->getPhysicalTabTier4Data($payload);
                    break;

                default:
                    $data = [];
                    break;
            }

            return response()->json($data);
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
                    $data = $this->newDashboard->getPsychologicalTabTier1Data($payload);
                    break;

                case 2:
                    // Meditation hours chart
                    $data = $this->newDashboard->getPsychologicalTabTier2Data($payload);
                    break;

                case 3:
                    // Popular meditation categories and top 10 meditations chart
                    $data = $this->newDashboard->getPsychologicalTabTier3Data($payload);
                    break;

                case 4:
                    // Moods analysis
                    $data = $this->newDashboard->getPsychologicalTabTier4Data($payload);
                    break;

                default:
                    $data = [];
                    break;
            }

            return response()->json($data);
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
                    $data = $this->newDashboard->getAuditTabTier1Data($payload);
                    break;
                case 2:
                    // Audit tab category wise tabs
                    $data = $this->newDashboard->getAuditTabTier2Data($payload);
                    break;
                case 3:
                    // Audit tab category wise company score gauge and line charts
                    $data = $this->newDashboard->getAuditTabTier3Data($payload);
                    break;
                case 4:
                    // Audit tab subcategory wise company score gauge charts
                    $data = $this->newDashboard->getAuditTabTier4Data($payload);
                    break;
                default:
                    $data = [];
                    break;
            }

            return response()->json($data);
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
            $fromDate      = (!empty($request->from) ? $request->from : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subMonths(5)->format('Y-m-d 00:00:00'));
            $toDate        = (!empty($request->to) ? $request->to : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->format('Y-m-d H:i:s'));
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
                        ->whereRaw("(CONVERT_TZ(zc_survey_responses.created_at, ?, ?) BETWEEN ? AND ?)",[
                            'UTC', $timezone, $fromDate, $toDate,
                        ]);
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

            $resellerType = null;
            if ($role->group == 'reseller' && $company->parent_id == null) {
                $resellerType = 1;
            }

            $data = [
                'role'          => $role,
                'companies'     => $companies,
                'company'       => $company,
                'companiesId'   => (!empty($companies)) ? implode(',', array_keys($companies)) : [],
                'resellerType'  => $resellerType,
                'company_id'    => $company_id,
                'categories'    => $categories,
                'requestParams' => json_encode($request->only('from', 'to', 'company') + ['category' => $category->id]),
                'ga_title'      => trans('page_title.dashboard.question_report'),
            ];

            return view('newDashboard.question-report', $data);
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
                    $data = $this->newDashboard->getQuestionReportTier1Data($payload);
                    break;
                case 2:
                    // Question report category score data and subcategory progressars and tabs
                    $data = $this->newDashboard->getQuestionReportTier2Data($payload);
                    break;
                case 3:
                    // Subcategory waise question table data
                    return $this->newDashboard->getQuestionReportTier3Data($payload);
                    break;
                default:
                    $data = [];
                    break;
            }

            return response()->json($data);
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

        return view('newDashboard.question-details', $data);
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
            return $this->newDashboard->getQuestionReportTier4Data($question, $request->all());
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
                    $data = $this->newDashboard->getBookingTabTier1Data($payload);
                    break;
                case 2:
                    // Booking tab Events Revenue Data [ Completed Amount / Complated total / Booked Total / Cancelled Total ]
                    $data = $this->newDashboard->getBookingTabTier2Data($payload);
                    break;
                case 3:
                    // Today's Event calendar
                    $data = $this->newDashboard->getBookingTabTier3Data($payload);
                    break;
                default:
                    $data = [];
                    break;
            }

            return response()->json($data);
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
