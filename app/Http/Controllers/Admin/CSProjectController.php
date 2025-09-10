<?php declare (strict_types = 1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateProjectSurveyRequest;
use App\Http\Requests\Admin\EditProjectSurveyRequest;
use App\Http\Requests\Admin\StoreProjectSurveyResponse;
use App\Http\Requests\Admin\NpsProjectExportRequest;
use App\Models\Company;
use App\Models\NpsProject;
use App\Models\User;
use App\Models\UserNpsProjectLogs;
use Breadcrumbs;
use Carbon\Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Class ZcSurveyController
 *
 * @package App\Http\Controllers\Admin
 */
class CSProjectController extends Controller
{
    /**
     * variable to store the model object
     * @var npsProjectModel
     */
    protected $npsProjectModel;

    /**
     * contructor to initialize model object
     * @param NpsProject $npsProjectModel
     */
    public function __construct(NpsProject $npsProjectModel)
    {
        $this->npsProjectModel = $npsProjectModel;
        $this->bindBreadcrumbs();
    }

    /*
     * Bind breadcrumbs of role module
     */
    public function bindBreadcrumbs()
    {
        Breadcrumbs::for('projectsurvey.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Customer Satisfaction', route('admin.reports.nps', '#projectTab'));
            $trail->push('Project Survey', '');
        });
        Breadcrumbs::for('projectsurvey.create', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Customer Satisfaction', route('admin.reports.nps', '#projectTab'));
            $trail->push('Add Project', '');
        });
        Breadcrumbs::for('projectsurvey.edit', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Customer Satisfaction', route('admin.reports.nps', '#projectTab'));
            $trail->push('Edit Project', '');
        });
    }

    /**
     * @param Request $request
     *
     * @return View
     */

    public function getProjectData(Request $request)
    {
        if (!access()->allow('manage-project-survey')) {
            abort(403);
        }
        try {
            return $this->npsProjectModel->getNPSProjectTableData($request->all());
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
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        if (!access()->allow('create-project-survey')) {
            abort(403);
        }

        try {
            $data                      = [];
            $data['projectSurveyType'] = array("public" => "Public", "system" => "System");
            $data['ga_title']          = trans('page_title.projectsurvey.create');
            return \view('admin.projectsurvey.create', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('customersatisfaction.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.reports.nps')->with('message', $messageData);
        }
    }

    /**
     * @param CreateProjectSurveyRequest $request
     *
     * @return RedirectResponse
     */
    public function store(CreateProjectSurveyRequest $request)
    {
        if (!access()->allow('create-project-survey')) {
            abort(403);
        }

        try {
            \DB::beginTransaction();
            $data = $this->npsProjectModel->storeEntity($request->all());
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('customersatisfaction.message.data_add_success'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.reports.nps')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('customersatisfaction.message.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.projectsurvey.create')->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.reports.nps')->with('message', $messageData);
        }
    }

    /**
     * @param ZcSurvey $zcsurvey
     * @return View
     */
    public function edit(NpsProject $npsProject)
    {
        if (!access()->allow('update-project-survey')) {
            abort(403);
        }

        $userCompanyId = \Auth::user()->company()->first() != null ? \Auth::user()->company()->first()->id : null;

        if ($userCompanyId != null && $userCompanyId != $npsProject->company_id) {
            abort(403);
        }

        $companyLocation = $npsProject->company->locations()->where("company_locations.default", true)->first();
        if (!empty($companyLocation)) {
            $now = now($companyLocation->timezone)->toDateString();
            if (Carbon::parse($npsProject->start_date)->toDateString() <= $now && Carbon::parse($npsProject->end_date)->toDateString() >= $now) {
                abort(403);
            } elseif ($now > Carbon::parse($npsProject->end_date)->toDateString()) {
                abort(403);
            }
        }

        try {
            $data                      = [];
            $data['surveyData']        = $npsProject;
            $data['projectSurveyType'] = array("public" => "Public", "system" => "System");
            $data['ga_title']          = trans('page_title.projectsurvey.edit');
            return \view('admin.projectsurvey.edit', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('customersatisfaction.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.reports.nps')->with('message', $messageData);
        }
    }

    /**
     * @param EditProjectSurveyRequest $request
     *
     * @return RedirectResponse
     */
    public function update(EditProjectSurveyRequest $request, NpsProject $npsProject)
    {
        if (!access()->allow('update-project-survey')) {
            abort(403);
        }

        try {
            \DB::beginTransaction();

            $data = $npsProject->updateEntity($request->all());
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('customersatisfaction.message.data_update_success'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.reports.nps')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('customersatisfaction.message.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.reports.nps')->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.reports.nps')->with('message', $messageData);
        }
    }

    /**
     * @param  NpsProject $npsProject
     * @return json
     */
    public function delete(NpsProject $npsProject)
    {
        if (!access()->allow('delete-project-survey')) {
            abort(403);
        }

        try {
            return $npsProject->deleteRecord();
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('customersatisfaction.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @param  ZcSurvey $zcSurvey
     * @return json
     */
    public function view(NpsProject $npsProject)
    {
        if (!access()->allow('view-project-survey')) {
            abort(403);
        }

        try {
            $data                 = [];
            $data['timezone']     = (auth()->user()->timezone ?? config('app.timezone'));
            $data['date_format']  = config('zevolifesettings.date_format.moment_default_date');
            $data['pagination']   = config('zevolifesettings.datatable.pagination.long');
            $data['npsProject']   = $npsProject;
            $data['company']      = $npsProject->company()->first();
            $feedBackType         = config('zevolifesettings.nps_feedback_type');
            $data['feedBackType'] = array("all" => "All") + $feedBackType;
            $data['ga_title']     = trans('page_title.projectsurvey.details') . "(" . $npsProject->title . ")";
            return \view('admin.projectsurvey.view', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('customersatisfaction.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @param Request $request
     *
     * @return View
     */

    public function getNpsProjectUserFeedBackTableData(Request $request, NpsProject $npsProject)
    {
        if (!access()->allow('view-project-survey')) {
            abort(403);
        }
        try {
            return $npsProject->getNpsProjectUserFeedBackTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.reports.nps')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     *
     * @return View
     */

    public function getGraphData(Request $request, NpsProject $npsProject): JsonResponse
    {
        if (!access()->allow('manage-project-survey')) {
            abort(403);
        }
        $surveyTotalResponse       = $npsProject->userNpsProjectLogs()->count();
        $totalFeedBackTypeResponse = $npsProject->userNpsProjectLogs()
            ->select("feedback_type", DB::raw("count(feedback_type) as responseCount"))
            ->groupBy("feedback_type")
            ->get()
            ->pluck("responseCount", "feedback_type")
            ->toArray();

        $feedBackType           = config('zevolifesettings.nps_feedback_type');
        $feedbackTypesWithClass = config('zevolifesettings.feedback_class_color');
        $i                      = 0;
        $chartJson              = [];
        $chartData              = [];
        foreach ($feedBackType as $key => $value) {
            $avgScore = 0;
            if (array_key_exists($key, $totalFeedBackTypeResponse)) {
                $avgScore = ($totalFeedBackTypeResponse[$key] / $surveyTotalResponse) * 100;
            }
            if ($avgScore > 0) {
                $chartData[$i]['name']  = $value;
                $chartData[$i]['class'] = $feedbackTypesWithClass[$key];
                $chartData[$i++]['y']   = $avgScore;
            }
        }
        $chartJson = json_encode($chartData);

        $data['result'] = $chartJson;

        $options = JSON_PRESERVE_ZERO_FRACTION;
        return response()->json($data, 200, [], $options);
    }

    public function projectSurveyResponse($surveyId)
    {
        try {
            $decryptedSurveydata = decrypt($surveyId);
            $decryptedSurveydata = explode(':', strval($decryptedSurveydata));
            $surveyLogId         = (!empty($decryptedSurveydata[0])) ? intval($decryptedSurveydata[0]) : 0;
            $email               = (!empty($decryptedSurveydata[1])) ? $decryptedSurveydata[1] : "";
            $surveyLog           = NpsProject::join("companies", "companies.id", "=", "nps_project.company_id")
                ->join("company_locations", function ($join) {
                    $join->on("company_locations.company_id", "=", "companies.id")
                        ->where("company_locations.default", true);
                })
                ->select("nps_project.*", "company_locations.timezone")
                ->where("nps_project.id", $surveyLogId)
                ->first();
            $data                 = [];
            $data['surveyId']     = $surveyId;
            $data['branding']     = getBrandingData();
            $data['enableSurvey'] = $data['companyExpired'] = false;
            $data['feedBackType'] = config('zevolifesettings.nps_feedback_type');

            if (!empty($surveyLog)) {
                $company = $surveyLog->company;

                if ($surveyLog->type == "system" && empty($email)) {
                    return \view('errors.400');
                }
                $platformDomain = config('zevolifesettings.domain_branding.PLATFORM_DOMAIN');
                if (!empty($data['branding']->sub_domain) && !in_array($data['branding']->sub_domain, $platformDomain)) {
                    $branding = \App\Models\CompanyBranding::where('company_id', $company->id)->where('status', 1)->first();

                    if (!$company->is_branding || empty($branding) || (!empty($branding) && $branding->sub_domain != $data['branding']->sub_domain)) {
                        return \view('errors.400');
                    }
                }

                $timezone = (!empty($surveyLog->timezone)) ? $surveyLog->timezone : config('app.timezone');

                $now                     = \now($timezone)->toDateString();
                $subscription_start_date = Carbon::parse($company->subscription_start_date, config('app.timezone'))->setTimezone($timezone)->toDateString();
                $subscription_end_date   = Carbon::parse($company->subscription_end_date, config('app.timezone'))->setTimezone($timezone)->toDateString();

                if ($now > $subscription_start_date && $now < $subscription_end_date) {
                    if (Carbon::parse($surveyLog->start_date)->toDateString() <= $now && Carbon::parse($surveyLog->end_date)->toDateString() >= $now) {
                        if ($surveyLog->type == "system") {
                            $user = User::where('email', $email)->first();
                            if (!empty($user)) {
                                $userSurveyLog = UserNpsProjectLogs::where(['user_id' => $user->id, 'nps_project_id' => $surveyLogId])->first();
                                if (!empty($userSurveyLog)) {
                                    $data['message']     = "This survey has been submitted previously";
                                    $data['home_button'] = true;
                                    return \view('errors.survey_filled', $data);
                                }
                            } else {
                                return \view('errors.400');
                            }
                        }
                        $data['enableSurvey'] = true;
                        $data['ga_title']     = $surveyLog->title . " - " . trans('page_title.projectsurveyrollout');
                        return \view('admin.projectsurvey.survey-submission', $data);
                    } elseif (Carbon::parse($surveyLog->start_date)->toDateString() > $now) {
                        $data['message'] = "Hey, This survey will be started on " . Carbon::parse($surveyLog->start_date)->format(config('zevolifesettings.date_format.default_date'));
                        return \view('errors.survey_notstarted', $data);
                    } else {
                        $data['message']     = "This survey has expired. Please try next time.";
                        $data['home_button'] = true;
                        return \view('errors.survey_expired', $data);
                    }
                } else {
                    return \view('errors.company-expired', ['home_button' => true, "sub_message" => "Your company subscription has expired. Please contact your admin or the Zevo Account Manager"]);
                }
            }
            return \view('errors.400');
        } catch (\Illuminate\Contracts\Encryption\DecryptException $exception) {
            report($exception);
            abort(400);
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    public function submitProjectSurvey($surveyId, StoreProjectSurveyResponse $request)
    {
        try {
            $decryptedSurveydata = decrypt($surveyId);
            $decryptedSurveydata = explode(':', strval($decryptedSurveydata));
            $surveyLogId         = (!empty($decryptedSurveydata[0])) ? intval($decryptedSurveydata[0]) : 0;
            $email               = (!empty($decryptedSurveydata[1])) ? $decryptedSurveydata[1] : "";
            $surveyLog           = NpsProject::find($surveyLogId);

            if (!empty($surveyLog)) {
                if ($surveyLog->type == "system" && empty($email)) {
                    return redirect()->back()->withInput()->withErrors("It seems request is malformed or bad.");
                }

                $surveyInput                       = [];
                $surveyInput['nps_project_id']     = $surveyLogId;
                $surveyInput['feedback_type']      = $request->get("feedBack");
                $surveyInput['feedback']           = $request->get("feedBackNote");
                $surveyInput['survey_received_on'] = \now(config('app.timezone'))->toDateTimeString();
                if ($surveyLog->type == "system") {
                    $user = User::where('email', $email)->first();
                    if (!empty($user)) {
                        $userSurveyLog = UserNpsProjectLogs::where(['user_id' => $user->id, 'nps_project_id' => $surveyLogId])->first();
                        if (!empty($userSurveyLog)) {
                            $data['message'] = "This survey has been submitted previously";
                            return \view('errors.survey_filled', $data);
                        }
                        $surveyInput['user_id'] = $user->id;
                    }
                }
                $surveyLog->userNpsProjectLogs()->create($surveyInput);

                return \Redirect::route('survey-submited');
            } else {
                return redirect()->back()->withInput()->withErrors("It seems request is malformed or bad.");
            }
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    public function surveySubmited(Request $request)
    {
        $data                  = [];
        $data['notShowHeader'] = true;
        return \view('admin.projectsurvey.survey-submited', $data);
    }

    /**
     * @param ChallengeExportRequest $request
     * @return RedirectResponse
     */
    public function exportNpsProjectData(NpsProjectExportRequest $request)
    {
        if (!access()->allow('manage-project-survey')) {
            abort(403);
        }

        try {
            \DB::beginTransaction();
            $data = $this->npsProjectModel->exportNpsProjectDataEntity($request->all());
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
}
