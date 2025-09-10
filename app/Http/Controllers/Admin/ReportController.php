<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NpsReportExportRequest;
use App\Http\Requests\Admin\RealtimeAvailabilityRequest;
use App\Models\Challenge;
use App\Models\Company;
use App\Models\CompanyLocation;
use App\Models\Course;
use App\Models\CronofySchedule;
use App\Models\EapCsatLogs;
use App\Models\MasterclassCsatLogs;
use App\Models\NpsProject;
use App\Models\OccupationalHealthReferral;
use App\Models\Role;
use App\Models\SubCategory;
use App\Models\User;
use App\Models\UserTeam;
use Breadcrumbs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use App\Http\Traits\ServesApiTrait;
use App\Jobs\GenerateRealtimeAvailabilityJob;

/**
 * Class ReportController
 *
 * @package App\Http\Controllers\Admin
 */
class ReportController extends Controller
{
    use ServesApiTrait;

    /**
     * variable to store the model object
     * @var User
     */
    protected $model;

    /**
     * variable to store the model object
     * @var Course
     */
    protected $courseModel;

    /**
     * variable to store the model object
     * @var MasterclassCsatLogs
     */
    protected $McCsat;

    /**
     * contructor to initialize model object
     * @param User $model ;
     */

    /**
     * variable to store the model object
     * @var EapCsatLogs
     */
    protected $EapCsat;

    /**
     * variable to store the model object
     * @var CoursCronofySchedule
     */
    protected $cronofySchedule;

    /**
     * variable to store the model object
     * @var Challenge
     */
    protected $challenge;

    public function __construct(User $model, Course $courseModel, MasterclassCsatLogs $McCsat, EapCsatLogs $EapCsat, CronofySchedule $cronofySchedule)
    {
        $this->model           = $model;
        $this->challenge       = new Challenge();
        $this->McCsat          = $McCsat;
        $this->courseModel     = $courseModel;
        $this->EapCsat         = $EapCsat;
        $this->cronofySchedule = $cronofySchedule;
        $this->bindBreadcrumbs();
    }

    /*
     * Bind breadcrumbs of role module
     */
    public function bindBreadcrumbs()
    {
        Breadcrumbs::for('npssurvey.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Customer Satisfaction');
        });
        Breadcrumbs::for('npssurvey.useractivity', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('User Activity');
        });
        Breadcrumbs::for('report.intercompany', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Inter-Company report');
        });
        Breadcrumbs::for('report.challengeactivity', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Challenge Activity');
        });
        Breadcrumbs::for('report.challengeactivityhistory', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Challenge Activity', route('admin.reports.challengeactivityreport'));
            $trail->push('Challenge Activity History');
        });
        Breadcrumbs::for('masterclassfeedback.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Masterclass Feedback Report');
        });
        Breadcrumbs::for('eapfeedback.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Counsellor Feedback');
        });
        Breadcrumbs::for('userregistration.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('User Registration');
        });
        Breadcrumbs::for('digitaltherapy.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Digital Therapy');
        });
        Breadcrumbs::for('occupational-health.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('occupationalHealthReport.title.index_title'));
        });
        Breadcrumbs::for('usage-report.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('usage_report.title.index_title'));
        });
        Breadcrumbs::for('realtime-availability.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('realtime_wbs_availability.title.index_title'));
        });
    }

    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $role       = getUserRole();
        $user       = auth()->user();
        $loginemail = ($user->email ?? "");
        if (!access()->allow('view-user-activities') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            $data                = array();
            $data['timezone']    = (auth()->user()->timezone ?? config('app.timezone'));
            $data['date_format'] = config('zevolifesettings.date_format.moment_default_datetime');
            $data['pagination']  = config('zevolifesettings.datatable.pagination.long');
            $data['ga_title']    = trans('page_title.reports.users-activities');
            $data['loginemail']  = $loginemail;
            return \view('admin.report.useractivities', $data);
        } catch (\Exception $exception) {
            report($exception);
            return response(trans('customersatisfaction.message.something_wrong'), 400)
                ->header('Content-Type', 'text/plain');
        }
    }

    /**
     * @param Request $request
     *
     * @return View
     */

    public function getUserStepsData(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('view-user-activities') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            return $this->model->getUserStepsTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.reports.users-activities')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     *
     * @return View
     */

    public function getUserExercisesData(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('view-user-activities') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            return $this->model->getUserExercisesTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.reports.users-activities')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     *
     * @return View
     */

    public function getUserMeditationsData(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('view-user-activities') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            return $this->model->getUserMeditationsTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.reports.users-activities')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function getNPSFeedBack(Request $request)
    {
        $role        = getUserRole();
        $companyData = auth()->user()->company()->get()->first();
        if (!access()->allow('view-nps-feedbacks') && !access()->allow('manage-project-survey') && !access()->allow('manage-portal-survey')) {
            abort(403);
        }
        try {
            $now                = \now(config('app.timezone'))->toDateTimeString();
            $appTimeZone        = config('app.timezone');
            $data               = array();
            $data['pagination'] = config('zevolifesettings.datatable.pagination.long');
            $data['companies']  = Company::get()->pluck('name', 'id')->toArray();
            if ($role->group == 'zevo') {
                $data['portalCompanies'] = Company::where('allow_portal', 1)->get()->pluck('name', 'id')->toArray();
            } else {
                $data['portalCompanies'] = Company::where('id', $companyData->id)->orWhere('parent_id', $companyData->id)->get()->pluck('name', 'id')->toArray();
            }
            $data['timezone']       = (auth()->user()->timezone ?? config('app.timezone'));
            $data['date_format']    = config('zevolifesettings.date_format.moment_default_date');
            $feedBackType           = config('zevolifesettings.nps_feedback_type');
            $feedBackType           = array_reverse($feedBackType);
            $feedbackTypesWithClass = config('zevolifesettings.feedback_class_color');
            $projectChartJson       = [];
            $chartData       = [];
            $chartDataPortal = [];

            $surveyQuery = \DB::table(DB::raw("(SELECT * from user_nps_survey_logs where feedback_type is not null) as unsl1"))
                ->leftJoin(DB::raw("(SELECT * from user_nps_survey_logs where feedback_type is not null) as unsl2"), function ($join) {
                    $join->on("unsl1.user_id", "=", "unsl2.user_id")
                        ->where("unsl1.id", "<", DB::raw("`unsl2`.`id`"));
                })
                ->whereNull('unsl2.id')
                ->select("unsl1.*");

            $surveyTotalResponse       = $surveyQuery->count();
            $totalFeedBackTypeResponse = $surveyQuery
                ->select("unsl1.feedback_type", DB::raw("count(unsl1.feedback_type) as responseCount"))
                ->groupBy("unsl1.feedback_type")
                ->get()
                ->pluck("responseCount", "feedback_type")
                ->toArray();
            $j = 0;
            foreach ($feedBackType as $key => $value) {
                $avgScore = 0;
                if (array_key_exists($key, $totalFeedBackTypeResponse)) {
                    $avgScore = ($totalFeedBackTypeResponse[$key] / $surveyTotalResponse) * 100;
                }
                if ($avgScore > 0) {
                    $chartData[$j]['name']  = $value;
                    $chartData[$j]['class'] = $feedbackTypesWithClass[$key];
                    $chartData[$j++]['y']   = $avgScore;
                }
            }

            $chartJson = json_encode($chartData);
            $chartJson = str_replace('"name"', 'name', $chartJson);
            $chartJson = str_replace('"y"', 'y', $chartJson);

            $userIds = [];
            if ($role->group == 'reseller' && $companyData->parent_id == null) {
                $childCompany = Company::select('id')->where('parent_id', $companyData->id)->get()->pluck('id')->toArray();
                $userIds      = UserTeam::select('user_id')->where('company_id', $companyData->id)->orWhereIn('company_id', $childCompany)->get()->pluck('user_id')->toArray();
            }

            $surveyPortalQuery = \DB::table(DB::raw("(SELECT * from user_nps_survey_logs where is_portal = '1' AND feedback_type is not null) as unsl1"))
                ->leftJoin(DB::raw("(SELECT * from user_nps_survey_logs where is_portal = '1' AND feedback_type is not null) as unsl2"), function ($join) {
                    $join->on("unsl1.user_id", "=", "unsl2.user_id")
                        ->where("unsl1.id", "<", DB::raw("`unsl2`.`id`"));
                });

            if ($role->group == 'reseller' && $companyData->parent_id == null) {
                $surveyPortalQuery->whereIn('unsl1.user_id', $userIds);
            }
            $surveyPortalQuery = $surveyPortalQuery->whereNull('unsl2.id')
                ->select("unsl1.*");

            $surveyPortalTotalResponse       = $surveyPortalQuery->count();
            $totalPortalFeedBackTypeResponse = $surveyPortalQuery
                ->select("unsl1.feedback_type", DB::raw("count(unsl1.feedback_type) as responseCount"))
                ->groupBy("unsl1.feedback_type")
                ->get()
                ->pluck("responseCount", "feedback_type")
                ->toArray();
            $j = 0;
            foreach ($feedBackType as $key => $value) {
                $avgScore = 0;
                if (array_key_exists($key, $totalPortalFeedBackTypeResponse)) {
                    $avgScore = ($totalPortalFeedBackTypeResponse[$key] / $surveyPortalTotalResponse) * 100;
                }
                if ($avgScore != 0) {
                    $chartDataPortal[$j]['name']  = $value;
                    $chartDataPortal[$j]['class'] = $feedbackTypesWithClass[$key];
                    $chartDataPortal[$j++]['y']   = $avgScore;
                }
            }

            $chartJsonPortal = json_encode($chartDataPortal);
            $chartJsonPortal = str_replace('"name"', 'name', $chartJsonPortal);
            $chartJsonPortal = str_replace('"y"', 'y', $chartJsonPortal);

            $data['chartJson']        = $chartJson;
            $data['chartJsonPortal']  = $chartJsonPortal;
            $data['projectChartJson'] = $projectChartJson;
            $data['feedBackType']     = array("all" => "All") + $feedBackType;
            $data['projectStatus']    = array("all" => "All", "active" => "Active", "upcoming" => "Upcoming", "expired" => "Expired");

            $user                 = auth()->user();
            $userCompany          = $user->company()->first();
            $userRole             = $user->roles()->whereIn('slug', ['super_admin', 'company_admin'])->first();
            $loginemail           = ($user->email ?? "");
            $data['isSuperAdmin'] = false;
            $projectList          = [];
            if (!empty($userRole)) {
                $projectList = NpsProject::where("nps_project.status", true);
                if ($userRole->slug == "super_admin") {
                    $data['isSuperAdmin'] = true;
                    $projectList          = $projectList->join("companies", "companies.id", "=", "nps_project.company_id")
                        ->join("company_locations", function ($join) {
                            $join->on("company_locations.company_id", "=", "companies.id")
                                ->where("company_locations.default", true);
                        })
                        ->whereRaw(
                            "nps_project.start_date <= DATE(CONVERT_TZ(?, ?, company_locations.timezone))"
                        ,[$now,$appTimeZone])
                        ->select("nps_project.id as npsId", DB::raw(" CONCAT(nps_project.title,' (',companies.name,')') as prName"))
                        ->get()
                        ->pluck("prName", "npsId")
                        ->toArray();
                } else {
                    $projectList = $projectList->where("company_id", $userCompany->id)
                        ->pluck("title", "id")
                        ->toArray();
                }
            }
            $data['projectList'] = $projectList;
            $data['ga_title']    = trans('page_title.reports.nps');
            $data['loginemail']  = $loginemail;
            return \view('admin.report.nps', $data);
        } catch (\Exception $exception) {
            report($exception);
            return response(trans('customersatisfaction.message.something_wrong'), 400)
                ->header('Content-Type', 'text/plain');
        }
    }

    /**
     * @param Request $request
     *
     * @return View
     */

    public function getNpsData(Request $request)
    {
        $user    = Auth()->user();
        $role    = getUserRole();
        $company = $user->company()->first();
        if (!access()->allow('view-nps-feedbacks') && !access()->allow('manage-portal-survey')) {
            abort(403);
        } elseif ($role->group == 'reseller' && $company->parent_id != null) {
            abort(403);
        }

        try {
            return $this->model->getUserNPSTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.reports.users-activities')->with('message', $messageData);
        }
    }

    public function interCompanyReport()
    {
        $role = getUserRole();
        if (!access()->allow('inter-company-report') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            $timezone           = config('app.timezone');
            $data               = [];
            $data['pagination'] = config('zevolifesettings.datatable.pagination.long');
            $user               = auth()->user();
            $loginemail         = ($user->email ?? "");
            $challenges         = [];
            $ongoingLbl         = config('zevolifesettings.ICReportChallengeOptGroupLable.ongoing');
            $completedLbl       = config('zevolifesettings.ICReportChallengeOptGroupLable.completed');
            $this->challenge
                ->where(function ($query) use ($timezone) {
                    $query
                        ->where([['start_date', '<=', now($timezone)->toDateTimeString()], ['end_date', '>=', now($timezone)->toDateTimeString()]])
                        ->orWhere([['end_date', '<', now($timezone)->toDateTimeString()]]);
                })
                ->where(['challenge_type' => 'inter_company', 'cancelled' => false, 'company_id' => null])
                ->orderBy('updated_at', 'DESC')
                ->get()->each(function ($challenge) use (&$challenges, $ongoingLbl, $completedLbl) {
                    $group                                    = ((!$challenge->finished) ? $ongoingLbl : $completedLbl);
                    $challenges[$group][$challenge->getKey()] = $challenge->title;
                });

            $data['challenges'] = $challenges;
            $data['ga_title']   = trans('page_title.reports.inter-company');
            $data['loginemail'] = $loginemail;
            $data['timezone']   = $timezone;
            return \view('admin.report.inter-company-report', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.reports.intercompanyreport')->with('message', $messageData);
        }
    }

    public function getICReportChallengeData(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('inter-company-report') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            return $this->challenge->getICReportTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.reports.intercompanyreport')->with('message', $messageData);
        }
    }

    public function getICReportChallengeComapnies(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('inter-company-report') || $role->group != 'zevo') {
            abort(403);
        }

        $returnData = ['status' => 0, 'data' => ''];

        try {
            $companies  = $this->challenge->getICCompanies($request->all());
            $returnData = [
                'data'   => $companies,
                'status' => 1,
            ];
        } catch (\Exception $exception) {
            report($exception);
            $returnData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
        }

        return response()->json($returnData);
    }

    public function challengeActivityReport(Request $request)
    {
        if (!access()->allow('challenge-activity-report')) {
            abort(403);
        }

        try {
            $timezone                = config('app.timezone');
            $data                    = [];
            $user                    = auth()->user();
            $loginemail              = ($user->email ?? "");
            $data['pagination']      = config('zevolifesettings.datatable.pagination.long');
            $data['challengeStatus'] = array("ongoing" => "Ongoing", "completed" => "Completed");
            $data['challengeType']   = array(
                "inter_company" => "Intercompany challenge",
                "team"          => "Team challenge",
                "company_goal"  => "Company goal",
                "individual"    => "Individual challenge",
            );
            $data['ga_title']    = trans('page_title.reports.challenge-activity');
            $data['timezone']    = (auth()->user()->timezone ?? config('app.timezone'));
            $data['date_format'] = config('zevolifesettings.date_format.moment_default_datetime');
            $data['loginemail']  = $loginemail;
            $challengeList       = [];
            if (!empty($request->challengeStatus) && !empty($request->challengeType)) {
                $timezone        = config('app.timezone');
                $challengeStatus = ($request->challengeStatus ?? "");
                $challengeType   = ($request->challengeType ?? "");

                $challengeList = $this->challenge->where("cancelled", false)
                    ->where("challenge_type", $challengeType);

                if ($challengeStatus == "ongoing") {
                    $challengeList->where('start_date', '<=', now($timezone)->toDateTimeString())->where('end_date', '>=', now($timezone)->toDateTimeString());
                } else {
                    $challengeList->where('finished', true);
                }

                $challengeList = $challengeList->select("id", "title")
                    ->pluck("title", "id")
                    ->toArray();

                $data['challengeList'] = $challengeList;
            }

            if (!empty($request->challenge) || !empty($request->team)) {
                $challengeParticipantsList     = $this->challenge->getChallengeParticipantList($request);
                $data['challengeParticipants'] = $challengeParticipantsList;
                $teamList                      = [];
                foreach ($data['challengeParticipants']['teamList'] as  $teamData) {
                    $teamList = $teamData;
                }
                $data['teamList'] = $teamList;
            }

            return \view('admin.report.challenge-activity-report', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('dashboard')->with('message', $messageData);
        }
    }

    public function getChallenges(Request $request)
    {
        if (!access()->allow('inter-company-report')) {
            abort(403);
        }

        $returnData = ['status' => 0, 'data' => ''];

        try {
            $challengeList = $this->challenge->getChallengeList($request->all());
            $returnData    = [
                'data'   => $challengeList,
                'status' => 1,
            ];
        } catch (\Exception $exception) {
            report($exception);
            $returnData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
        }

        return response()->json($returnData);
    }

    public function getChallengeParticipant(Request $request)
    {
        if (!access()->allow('inter-company-report')) {
            abort(403);
        }

        $returnData = ['status' => 0, 'data' => ''];

        try {
            $challengeList = $this->challenge->getChallengeParticipantList($request->all());
            $returnData    = [
                'data'   => $challengeList,
                'status' => 1,
            ];
        } catch (\Exception $exception) {
            report($exception);
            $returnData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
        }

        return response()->json($returnData);
    }

    /**
     * @param Request $request
     *
     * @return View
     */

    public function getChallengeSummaryData(Request $request)
    {
        if (!access()->allow('inter-company-report')) {
            abort(403);
        }
        try {
            return $this->challenge->getChallengeSummaryTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('dashboard')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     *
     * @return View
     */

    public function getChallengeDetailsData(Request $request)
    {
        if (!access()->allow('inter-company-report')) {
            abort(403);
        }
        try {
            return $this->challenge->getChallengeDetailsTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('dashboard')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     *
     * @return View
     */

    public function getChallengeDailySummaryData(Request $request)
    {
        if (!access()->allow('inter-company-report')) {
            abort(403);
        }
        try {
            return $this->challenge->getChallengeDailySummaryTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('dashboard')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     *
     * @return View
     */

    public function getUserDailyHistoryData(Request $request)
    {
        if (!access()->allow('inter-company-report')) {
            abort(403);
        }

        try {
            $timezone                = config('app.timezone');
            $user                    = auth()->user();
            $loginemail              = ($user->email ?? "");
            $data                    = [];
            $data['pagination']      = config('zevolifesettings.datatable.pagination.long');
            $data['tracker']         = config('zevolifesettings.tracker_list');
            $data['user']            = User::find($request->get('user_id'));
            $data['challenge']       = Challenge::find($request->get('challenge_id'));
            $data['logdate']         = $request->get('logdate');
            $data['type']            = $request->get('type');
            $data['columnName']      = $request->get('columnName');
            $data['modelId']         = $request->get('model_id', 0);
            $data['challengeStatus'] = $request->get('challengeStatus', 'ongoing');
            $data['uom']             = $request->get('uom', '');
            $data['ga_title']        = trans('page_title.reports.challenge-activity');
            $data['loginemail']      = $loginemail;
            $data['timezone']        = $timezone;

            return \view('admin.report.challenge-user-activity-report', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('dashboard')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     *
     * @return View
     */

    public function getUserDailyHistoryTableData(Request $request)
    {
        if (!access()->allow('inter-company-report')) {
            abort(403);
        }
        try {
            return $this->challenge->getUserDailyHistoryTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('dashboard')->with('message', $messageData);
        }
    }

    /**
     * Masterclass feedback page
     *
     * @param Request $request
     * @return View
     * @throws Exception
     */
    public function masterclassFeedbackIndex(Request $request)
    {
        if (!access()->allow('masterclass-feedback')) {
            abort(403);
        }

        try {
            $user       = auth()->user();
            $timezone   = (!empty($user->timezone) ? $user->timezone : config('app.timezone'));
            $loginemail = ($user->email ?? "");

            $categories = SubCategory::select('name', 'id')
                ->where('category_id', 1)
                ->get()->pluck('name', 'id')->toArray();
            $companies = Company::select('name', 'id')
                ->get()->pluck('name', 'id')->toArray();
            $author = User::select('id', DB::raw("CONCAT(first_name, ' ', last_name) AS name"))
                ->withCount('masterclassAuthor')
                ->where('is_coach', true)
                ->where('is_blocked', false)
                ->having('masterclass_author_count', '>', 0)
                ->get()->pluck('name', 'id')->toArray();
            $masterclasses = $this->courseModel->select('title', 'id')
                ->when($request->get('category', null), function ($query, $category) {
                    $query->where('sub_category_id', $category);
                })
                ->get()->pluck('title', 'id')->toArray();

            $data = [
                'timezone'      => $timezone,
                'categories'    => $categories,
                'companies'     => $companies,
                'masterclasses' => $masterclasses,
                'author'        => [1 => 'Zevo Admin'] + $author,
                'feedback'      => ['all' => 'All'] + config('zevolifesettings.nps_feedback_type'),
                'date_format'   => config('zevolifesettings.date_format.moment_default_datetime'),
                'pagination'    => config('zevolifesettings.datatable.pagination.long'),
                'ga_title'      => trans('page_title.masterclass-feedback.index'),
                'loginemail'    => $loginemail,
            ];

            return \view('admin.report.masterclass.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * To get masterclass feedback list
     *
     * @param Request $request
     * @return Mixed JSON
     */
    public function getMasterclassFeedback(Request $request)
    {
        try {
            if (!empty($request->type) && $request->type == "graph") {
                return $this->McCsat->getCsatGraph($request->all());
            } else {
                return $this->McCsat->getTableData($request->all());
            }
        } catch (\Exception $exception) {
            report($exception);
            return response()->json([
                'data'   => trans('customersatisfaction.message.something_wrong'),
                'status' => 0,
            ], 500);
        }
    }

    /**
     * EAP feedback page
     *
     * @param Request $request
     * @return View
     * @throws Exception
     */
    public function eapFeedbackIndex(Request $request)
    {
        if (!access()->allow('eap-feedback')) {
            abort(403);
        }

        try {
            $user       = auth()->user();
            $timezone   = (!empty($user->timezone) ? $user->timezone : config('app.timezone'));
            $loginemail = ($user->email ?? "");
            $companies  = Company::select('name', 'id')
                ->get()->pluck('name', 'id')->toArray();
            $timeDuration = [
                'all'     => 'All',
                'last_24' => 'Last 24 Hours',
                'last_7'  => 'Last 7 Days',
                'last_30' => 'Last 30 Days',
            ];
            $data = [
                'timezone'     => $timezone,
                'companies'    => $companies,
                'timeDuration' => $timeDuration,
                'feedback'     => ['all' => 'All'] + config('zevolifesettings.nps_feedback_type'),
                'date_format'  => config('zevolifesettings.date_format.moment_default_datetime'),
                'pagination'   => config('zevolifesettings.datatable.pagination.long'),
                'ga_title'     => trans('page_title.eap-feedback.index'),
                'loginemail'   => $loginemail,

            ];
            return \view('admin.report.eap.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * To get masterclass feedback list
     *
     * @param Request $request
     * @return Mixed JSON
     */
    public function getEapFeedback(Request $request)
    {
        try {
            if (!empty($request->type) && $request->type == "graph") {
                return $this->EapCsat->getCsatGraph($request->all());
            } else {
                return $this->EapCsat->getTableData($request->all());
            }
        } catch (\Exception $exception) {
            report($exception);
            return response()->json([
                'data'   => trans('customersatisfaction.message.something_wrong'),
                'status' => 0,
            ], 500);
        }
    }

    /**
     * Display the user registration report view
     * @param Request $request
     * @return View
     */
    public function userRegistrationIndex(Request $request)
    {
        $user = auth()->user();
        $role = getUserRole($user);
        if (!access()->allow('view-user-registrations')) {
            abort(403);
        }
        $loginemail     = ($user->email ?? "");
        $company        = $user->company()->first();
        try {
            $data = [
                'isSA'        => ($role->group == 'zevo'),
                'isRSA'       => ($role->group == 'reseller' && isset($company) && is_null($company->parent_id)),
                'pagination'  => config('zevolifesettings.datatable.pagination.short'),
                'companies'   => [],
                'userCompany' => $company,
                'timezone'    => (auth()->user()->timezone ?? config('app.timezone')),
                'date_format' => config('zevolifesettings.date_format.moment_default_datetime'),
                'ga_title'    => trans('page_title.reports.user-registration'),
            ];
            if ($role->group != 'zevo') {
                if ($role->group == 'company') {
                    $data['companies'] = [$company->id => $company->name];
                } elseif ($role->group == 'reseller') {
                    if (is_null($company->parent_id)) {
                        $data['companies'] = Company::select('id', 'name')
                            ->where(function ($query) use ($company) {
                                $query->where('parent_id', $company->id)
                                    ->orWhere('id', $company->id);
                            })
                            ->get()
                            ->pluck('name', 'id')
                            ->toArray();
                    } elseif (!is_null($company->parent_id)) {
                        $data['companies'] = [$company->id => $company->name];
                    }
                }
            } else {
                $data['companies'] = Company::get()->pluck('name', 'id')->toArray();
            }

            $data['role_name']  = Role::all()->pluck('name', 'id')->toArray();
            $data['role_group'] = Role::all()->pluck('group', 'group')->toArray();
            $data['loginemail'] = $loginemail;
            return \view('admin.report.userregistration.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * Display list of registered users data
     * @param Request $request
     *
     * @return View
     */

    public function getUserRegistration(Request $request)
    {
        if (!access()->allow('view-user-registrations')) {
            abort(403);
        }
        try {
            return $this->model->getUserRegistrationTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response($messageData, 500)->header('Content-Type', 'application/json');
        }
    }

    /**
     * @param ChallengeExportRequest $request
     * @return RedirectResponse
     */
    public function exportNpsReport(NpsReportExportRequest $request)
    {
        $user    = auth()->user();
        $role    = getUserRole($user);
        $company = $user->company()->first();
        if (!access()->allow('view-nps-feedbacks') && !access()->allow('manage-portal-survey')) {
            abort(403);
        } elseif ($role->group == 'reseller' && $company->parent_id != null) {
            abort(403);
        }

        try {
            \DB::beginTransaction();
            $data = $this->model->exportNpsDataEntity($request->all());
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
            return \Redirect::route('admin.reports.nps')->with('message', $messageData);
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
     * @param ChallengeExportRequest $request
     * @return RedirectResponse
     */
    public function exportUserRegistrationReport(NpsReportExportRequest $request)
    {
        if (!access()->allow('view-user-registrations')) {
            abort(403);
        }

        try {
            \DB::beginTransaction();
            $data = $this->model->exportUserRegistrationDataEntity($request->all());
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
            return \Redirect::route('admin.reports.user-registration')->with('message', $messageData);
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
     * @param ChallengeExportRequest $request
     * @return RedirectResponse
     */
    public function exportCounsellorFeedbackReport(NpsReportExportRequest $request)
    {
        if (!access()->allow('eap-feedback')) {
            abort(403);
        }

        try {
            \DB::beginTransaction();
            $data = $this->EapCsat->exportCounsellorFeedbackDataEntity($request->all());
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
            return \Redirect::route('admin.reports.eap-feedback')->with('message', $messageData);
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
     * @param ChallengeExportRequest $request
     * @return RedirectResponse
     */
    public function exportMasterclassFeedbackReport(NpsReportExportRequest $request)
    {
        if (!access()->allow('masterclass-feedback')) {
            abort(403);
        }

        try {
            \DB::beginTransaction();
            $data = $this->McCsat->exportMasterclassFeedbackDataEntity($request->all());
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
            return \Redirect::route('admin.reports.masterclass-feedback')->with('message', $messageData);
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
     * @param ChallengeExportRequest $request
     * @return RedirectResponse
     */
    public function exportIntercompanyReport(NpsReportExportRequest $request)
    {
        if (!access()->allow('inter-company-report')) {
            abort(403);
        }

        try {
            \DB::beginTransaction();
            $data = $this->challenge->exportICDataEntity($request->all());
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
            return \Redirect::route('admin.reports.intercompanyreport')->with('message', $messageData);
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
     * @param ChallengeExportRequest $request
     * @return RedirectResponse
     */
    public function exportUserActivityReport(NpsReportExportRequest $request)
    {
        $user = auth()->user();
        $role = getUserRole($user);
        if (!access()->allow('view-user-activities') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            \DB::beginTransaction();
            $data = $this->model->exportUserActivityDataEntity($request->all());
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
            return \Redirect::route('admin.reports.users-activities')->with('message', $messageData);
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
     * @param ChallengeExportRequest $request
     * @return RedirectResponse
     */
    public function exportChallengeActivityReport(NpsReportExportRequest $request)
    {
        if (!access()->allow('challenge-activity-report')) {
            abort(403);
        }

        try {
            \DB::beginTransaction();
            $data = $this->challenge->exportChallengeActivityDataEntity($request->all());
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
            return \Redirect::route('admin.reports.challengeactivityreport')->with('message', $messageData);
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
     * @param Request $request
     * @return Array
     * @throws Exception
     */
    public function exportChallengeUserActivityReport(Request $request)
    {
        if (!access()->allow('inter-company-report')) {
            abort(403);
        }
        try {
            \DB::beginTransaction();
            $data = $this->challenge->exportChallengeUserActivity($request->all());
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('contentreport.message.report_generate_in_background'),
                    'status' => 1,
                ];
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('contentreport.message.no_records_found'),
                    'status' => 0,
                ];
            }
            return $messageData;
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('contentreport.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData, 500);
        }
    }

    /**
     * @param Request $request
     * @return Array
     * @throws Exception
     */
    public function digitalTherapyIndex(Request $request)
    {
        if (!access()->allow('view-digital-therapy')) {
            abort(403);
        }
        try {
            $user      = auth()->user();
            $role      = getUserRole($user);
            $companies = CronofySchedule::select('cronofy_schedule.company_id', 'companies.name')
                ->join('companies', 'companies.id', '=', 'cronofy_schedule.company_id')
                ->groupBy('cronofy_schedule.company_id')
                ->pluck('name', 'company_id')
                ->toArray();
            $status  = config('zevolifesettings.calendly_session_status');
            $service = CronofySchedule::select('services.id', 'services.name')
                ->join('services', 'services.id', '=', 'cronofy_schedule.service_id')
                ->groupBy('cronofy_schedule.service_id')
                ->pluck('services.name', 'services.id')
                ->toArray();

            $wellbeingSpecialists = User::select('users.id', \DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS name"))
                ->join('ws_user', 'ws_user.user_id', '=', 'users.id')
                ->where('ws_user.responsibilities', '!=', 2)
                ->whereNull('users.deleted_at')
                ->where('ws_user.is_cronofy', true)
                ->pluck('name', 'id')
                ->toArray();

            $createdByArray = [
                'user' => 'User',
                'wellbeing_specialist' => 'Wellbeing Specialist'
            ];
            $loginemail = ($user->email ?? "");

            $data       = [
                'isSA'                 => ($role->group == 'zevo'),
                'pagination'           => config('zevolifesettings.datatable.pagination.long'),
                'companies'            => $companies,
                'status'               => $status,
                'service'              => $service,
                'userCompany'          => [],
                'loginemail'           => $loginemail,
                'timezone'             => (auth()->user()->timezone ?? config('app.timezone')),
                'date_format'          => config('zevolifesettings.date_format.digital_therapy_datetime'),
                'ga_title'             => trans('page_title.reports.digital-therapy-report'),
                'wellbeingSpecialists' => $wellbeingSpecialists,
                'loggedInUserRole'     => $role,
                'createdByArray'       => $createdByArray
            ];
            return \view('admin.report.digitaltherapy.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('digitaltherapyreport.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData, 500);
        }
    }

    /**
     * @param Request $request
     * @return Array
     * @throws Exception
     */
    public function getDigitalTherapyReport(Request $request)
    {
        if (!access()->allow('view-digital-therapy')) {
            $messageData = [
                'data'   => trans('digitaltherapyreport.messages.unauthorized_access'),
                'status' => 0,
            ];
            return response()->json($messageData, 401);
        }
        try {
            return $this->cronofySchedule->getDigitalTherapyReport($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('digitaltheraphyreport.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData, 500);
        }
    }

    /**
     * @param Request $request
     * @return Array
     * @throws Exception
     */
    public function exportDigitalTherapyReport(Request $request)
    {
        if (!access()->allow('view-digital-therapy')) {
            $messageData = [
                'data'   => trans('digitaltherapyreport.messages.unauthorized_access'),
                'status' => 0,
            ];
            return response()->json($messageData, 401);
        }
        try {
            \DB::beginTransaction();
            $data = $this->cronofySchedule->exportDigitalTherapyReport($request->all());
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
                'data'   => trans('digitaltherapyreport.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData, 500);
        }
    }

    /**
     * @param Request $request
     * @return Array
     * @throws Exception
     */
    public function occupationalHealthIndex(Request $request)
    {
        if (!access()->allow('occupational-health-report')) {
            abort(403);
        }
        try {
            $user      = auth()->user();
            $role      = getUserRole($user);
            $companies = CronofySchedule::select('cronofy_schedule.company_id', 'companies.name')
                ->join('companies', 'companies.id', '=', 'cronofy_schedule.company_id')
                ->groupBy('cronofy_schedule.company_id')
                ->pluck('name', 'company_id')
                ->toArray();
            $wellbeingSpecialists = User::select('users.id', \DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS name"))
                ->join('ws_user', 'ws_user.user_id', '=', 'users.id')
                ->where('ws_user.responsibilities', '!=', 2)
                ->whereNull('users.deleted_at')
                ->where('ws_user.is_cronofy', true)
                ->pluck('name', 'id')
                ->toArray();

            $loginemail = ($user->email ?? "");
            $data       = [
                'isSA'                 => ($role->group == 'zevo'),
                'pagination'           => config('zevolifesettings.datatable.pagination.long'),
                'companies'            => $companies,
                'wellbeingSpecialists' => $wellbeingSpecialists,
                'userCompany'          => [],
                'loginemail'           => $loginemail,
                'timezone'             => (auth()->user()->timezone ?? config('app.timezone')),
                'date_format'          => config('zevolifesettings.date_format.moment_default_datetime'),
                'ga_title'             => trans('page_title.reports.occupational-health-report'),
            ];
            return \view('admin.report.occupational-health.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('occupationalHealthReport.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData, 500);
        }
    }

    /**
     * @param Request $request
     * @return Array
     * @throws Exception
     */
    public function getOccupationalHealthReport(Request $request, OccupationalHealthReferral $occupationalHealthReferral)
    {
        if (!access()->allow('occupational-health-report')) {
            $messageData = [
                'data'   => trans('occupationalHealthReport.messages.unauthorized_access'),
                'status' => 0,
            ];
            return response()->json($messageData, 401);
        }
        try {
            return $occupationalHealthReferral->getOccupationalHealthReport($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('occupationalHealthReport.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData, 500);
        }
    }

    /**
     * @param Request $request
     * @return Array
     * @throws Exception
     */
    public function exportOccupationalHealthReport(Request $request, OccupationalHealthReferral $occupationalHealthReferral)
    {
        if (!access()->allow('occupational-health-report')) {
            $messageData = [
                'data'   => trans('occupationalHealthReport.messages.unauthorized_access'),
                'status' => 0,
            ];
            return response()->json($messageData, 401);
        }
        try {
            \DB::beginTransaction();
            $data = $occupationalHealthReferral->exportOccupationalHealthReport($request->all());
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('occupationalHealthReport.messages.report_success'),
                    'status' => 1,
                ];
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('occupationalHealthReport.messages.no_records_found'),
                    'status' => 0,
                ];
            }
            return $messageData;
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('occupationalHealthReport.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData, 500);
        }
    }

    /**
     * @param Request $request
     * @return Array
     * @throws Exception
     */
    public function usageReportIndex(Request $request)
    {
        if (!access()->allow('usage-report')) {
            abort(403);
        }
        try {
            $user      = auth()->user();
            $role      = getUserRole($user);
            $companies = Company::select('companies.id', 'companies.name')
                ->groupBy('companies.id')
                ->pluck('name', 'id')
                ->toArray();

            $companyId = $request->get('company');
            $location  = [];
            if (!is_null($companyId)) {
                $location = CompanyLocation::select('company_locations.id', 'company_locations.name')
                    ->groupBy('company_locations.id')
                    ->where('company_locations.company_id', $companyId)
                    ->pluck('name', 'id')
                    ->toArray();
            }

            $loginemail = ($user->email ?? "");
            $data       = [
                'isSA'       => ($role->group == 'zevo'),
                'pagination' => config('zevolifesettings.datatable.pagination.long'),
                'companies'  => $companies,
                'loginemail' => $loginemail,
                'location'   => $location,
                'ga_title'   => trans('page_title.reports.usage-report'),
            ];
            return \view('admin.report.usage-report.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('usage_report.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData, 500);
        }
    }

    /**
     * @param Request $request
     * @return Array
     * @throws Exception
     */
    public function getUsageReport(Request $request, Company $company)
    {
        if (!access()->allow('usage-report')) {
            $messageData = [
                'data'   => trans('usage_report.messages.unauthorized_access'),
                'status' => 0,
            ];
            return response()->json($messageData, 401);
        }
        try {
            return $this->model->getUsageReport($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('usage_report.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData, 500);
        }
    }

    /**
     * @param Request $request
     * @return Array
     * @throws Exception
     */
    public function exportUsageReport(Request $request)
    {
        if (!access()->allow('usage-report')) {
            $messageData = [
                'data'   => trans('usage_report.messages.unauthorized_access'),
                'status' => 0,
            ];
            return response()->json($messageData, 401);
        }
        try {
            \DB::beginTransaction();
            $data = $this->model->ExportUsageReport($request->all());
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('usage_report.modal.export.message'),
                    'status' => 1,
                ];
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('usage_report.messages.no_records_found'),
                    'status' => 0,
                ];
            }
            return $messageData;
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('usage_report.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData, 500);
        }
    }

    /**
     * Find Realtime Availability of WBS based on company
     * @param Request $request
     * @return Array
     * @throws Exception
     */
    public function realtimeWbsAvailability(Request $request)
    {
        $role       = getUserRole();
        $user       = auth()->user();
        $loginemail = ($user->email ?? "");
        if (!access()->allow('realtime-availability') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            $data                = array();
            $data['timezone']    = (auth()->user()->timezone ?? config('app.timezone'));
            $data['date_format'] = config('zevolifesettings.date_format.moment_default_datetime');
            $nowInUTC            = now(config('app.timezone'))->toDateTimeString();
            $data['loginEmail']  = $user->email ?? null;
            $data['pagination']  = config('zevolifesettings.datatable.pagination.long');
            $data['ga_title']    = trans('page_title.reports.realtime-wbs-report');
            $data['companies']   = Company::select('companies.id', 'companies.name')
                ->join('company_digital_therapy', 'company_digital_therapy.company_id', '=', 'companies.id')
                ->where('companies.subscription_start_date', '<=', $nowInUTC)
                ->where('companies.subscription_end_date', '>=', $nowInUTC)
                ->get()->pluck('name', 'id')->toArray();
            $data['loginemail']  = $loginemail;
            return \view('admin.report.realtime-wbs-availability.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            return response(trans('customersatisfaction.message.something_wrong'), 400)
                ->header('Content-Type', 'text/plain');
        }
    }

    /**
     * Get Location List based on company select if location enable for perticular company
     * @param Company $company
     * @return Array
     * @throws Exception
     */
    public function getLocationList(Company $company): JsonResponse
    {
        $returnArr = [];
        $companyDigitalTherapy = $company->digitalTherapy;
        if (!empty($companyDigitalTherapy) && $companyDigitalTherapy->set_hours_by == 2) {
            $records   = $company->locations;
            if ($records) {
                foreach ($records as $key => $record) {
                    $returnArr[$key]['id']   = $record->getKey();
                    $returnArr[$key]['name'] = $record->name;
                }
            }
            return $this->successResponse($returnArr);
        }
        return $this->notFoundResponse("Data not found!!");
    }

    /**
     * Get Wellbeing Specialist if don't have location setting.
     * @param Company $company
     * @return Array
     */
    public function getWellbeingSpecialist(Company $company): JsonResponse
    {
        $returnArr = [];
        $companyDigitalTherapy = $company->digitalTherapy;
        if (!empty($companyDigitalTherapy) && $companyDigitalTherapy->set_hours_by != 2) {
            // Get Wellbeing Specialist Data
            $companyWbsArray = [];
            if ($companyDigitalTherapy->set_availability_by == 1) {
                $companyWbs = $company->digitalTherapySlots()->select(DB::raw('group_concat(ws_id) AS ws_id'))->whereNull('location_id')->groupBy('company_id')->first();
                $companyWbsArray = (!empty($companyWbs)) ? array_unique(explode(',', $companyWbs->ws_id)) : [];
            } else {
                $companySpecificWbs = $company->digitalTherapySpecificSlots()->select(DB::raw('group_concat(ws_id) AS ws_id'))->whereNull('location_id')->groupBy('company_id')->first();
                $companyWbsArray = (!empty($companySpecificWbs)) ? array_unique(explode(',', $companySpecificWbs->ws_id)) : [];
            }
            
            if (!empty($companyWbsArray)) {
                $getWbsRecords = User::whereIn('id', $companyWbsArray)->select('id', DB::raw('CONCAT(first_name, " ",last_name) AS name'))->whereNull('deleted_at')->get();

                if ($getWbsRecords) {
                    foreach ($getWbsRecords as $key => $record) {
                        $returnArr[$key]['id']   = $record->getKey();
                        $returnArr[$key]['name'] = $record->name;
                    }
                }
            }
            return $this->successResponse($returnArr);
        }
        return $this->notFoundResponse("Data not found!!");
    }

    /**
     * Get Wellbeing Specialist based on location
     * @param Company $company
     * @return Array
     */
    public function getWellbeingSpecialistLocation(Company $company, CompanyLocation $location): JsonResponse
    {
        $returnArr = [];
        $companyDigitalTherapy = $company->digitalTherapy;
        if (!empty($companyDigitalTherapy) && $companyDigitalTherapy->set_hours_by == 2) {
            // Get Wellbeing Specialist Data
            $companyWbsArray = [];
            if ($companyDigitalTherapy->set_availability_by == 1) {
                $companyWbs = $company->digitalTherapySlots()->where('location_id', $location->id)->select(DB::raw('group_concat(ws_id) AS ws_id'))->groupBy('company_id')->first();
                $companyWbsArray = (!empty($companyWbs)) ? array_unique(explode(',', $companyWbs->ws_id)) : [];
            } else {
                $companySpecificWbs = $company->digitalTherapySpecificSlots()->where('location_id', $location->id)->select(DB::raw('group_concat(ws_id) AS ws_id'))->groupBy('company_id')->first();
                $companyWbsArray = (!empty($companySpecificWbs)) ? array_unique(explode(',', $companySpecificWbs->ws_id)) : [];
            }
            
            if (!empty($companyWbsArray)) {
                $getWbsRecords = User::whereIn('id', $companyWbsArray)->select('id', DB::raw('CONCAT(first_name, " ",last_name) AS name'))->whereNull('deleted_at')->get();
                if ($getWbsRecords) {
                    foreach ($getWbsRecords as $key => $record) {
                        $returnArr[$key]['id']   = $record->getKey();
                        $returnArr[$key]['name'] = $record->name;
                    }
                }
            }
            return $this->successResponse($returnArr);
        }
        return $this->notFoundResponse("Data not found!!");
    }

    /**
     * Generate Realtime Wbs Availability
     * @param RealtimeAvailabilityRequest $request
     * @return boolean
     */
    public function generateRealtimeWbsAvailability(RealtimeAvailabilityRequest $request)
    {
        $role       = getUserRole();
        $user       = auth()->user();
        $loginemail = ($user->email ?? "");
        if (!access()->allow('realtime-availability') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            $payload = [
                'companyId' => $request->company,
                'locationId'=> $request->location,
                'email' => $request->email,
                'wbsId' => $request->wellbeing_specialist
            ];

            // Get all selected WBS records
            $getWbsRecords = User::whereIn('id', $request->wellbeing_specialist)->select('id', DB::raw('CONCAT(first_name, " ",last_name) AS name'), 'timezone')->get()->toArray();

            // Extract Job for Event Booking Data Extract
            \dispatch(new GenerateRealtimeAvailabilityJob($payload, $user, $getWbsRecords))->onQueue('default');
            if ($getWbsRecords) {
                $messageData = [
                    'data'   => 'The report is being generated. We will email it to you once its ready.',
                    'status' => 1,
                ];
                return \Redirect::route('admin.reports.realtime-availability')->with('message', $messageData);
            } else {
                $messageData = [
                    'data'   => 'Something went wrong please try again.',
                    'status' => 0,
                ];
                return \Redirect::route('admin.reports.realtime-availability')->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            report($exception);
            return response(trans('customersatisfaction.message.something_wrong'), 400)
                ->header('Content-Type', 'text/plain');
        }
    }
}
