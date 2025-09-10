<?php declare (strict_types = 1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateZCSurveyRequest;
use App\Http\Requests\Admin\EditZCSurveyRequest;
use App\Http\Requests\Admin\StoreZcSurveyResponse;
use App\Http\Requests\Admin\StoreZcSurveyReviewSuggestion;
use App\Models\Company;
use App\Models\Department;
use App\Models\SurveyCategory;
use App\Models\User;
use App\Models\ZcQuestion;
use App\Models\ZcSurvey;
use App\Models\ZcSurveyLog;
use App\Models\ZcSurveyQuestion;
use App\Models\ZcSurveyResponse;
use App\Models\ZcSurveyReviewSuggestion;
use App\Models\ZcSurveySettings;
use App\Models\ZcSurveyUserLog;
use Breadcrumbs;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Class ZcSurveyController
 *
 * @package App\Http\Controllers\Admin
 */
class ZcSurveyController extends Controller
{
    /**
     * variable to store the model object
     * @var zcSurveyModel
     */
    protected $zcSurveyModel;

    /**
     * variable to store the model object
     * @var zcSurveyReviewSuggestion
     */
    protected $zcSurveyReviewSuggestion;

    /**
     * variable to store the model object
     * @var zcSurveyLog
     */
    protected $zcSurveyLog;

    /**
     * variable to store the model object
     * @var zcSurveyResponse
     */
    protected $zcSurveyResponse;

    /**
     * variable to store the model object
     * @var surveyCategory
     */
    protected $surveyCategory;

    /**
     * contructor to initialize model object
     * @param ZcSurvey $zcSurveyModel
     * @param ZcSurveyReviewSuggestion $zcSurveyReviewSuggestion
     * @param ZcSurveyResponse $zcSurveyResponse
     * @param SurveyCategory $surveyCategory
     */
    public function __construct(ZcSurvey $zcSurveyModel, ZcSurveyReviewSuggestion $zcSurveyReviewSuggestion, ZcSurveyLog $zcSurveyLog, ZcSurveyResponse $zcSurveyResponse, SurveyCategory $surveyCategory)
    {
        $this->zcSurveyModel            = $zcSurveyModel;
        $this->zcSurveyReviewSuggestion = $zcSurveyReviewSuggestion;
        $this->zcSurveyLog              = $zcSurveyLog;
        $this->zcSurveyResponse         = $zcSurveyResponse;
        $this->surveyCategory           = $surveyCategory;
        $this->bindBreadcrumbs();
    }

    /**
     * bind breadcrumbs of role module
     */
    private function bindBreadcrumbs()
    {
        // review suggestions(feedback)
        Breadcrumbs::for('survey.feedback', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Feedback');
        });
        // insight
        Breadcrumbs::for('survey.insights.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Insights');
        });
        Breadcrumbs::for('survey.insights.details', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Insights', route('admin.surveyInsights.index'));
            $trail->push('Survey Insights Details');
        });
        // hr report
        Breadcrumbs::for('survey.hr_report.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('HR Report details');
        });
        Breadcrumbs::for('survey.hr_report.freetext', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('HR Report details', route('admin.hrReport.index'));
            $trail->push('Review free text questions');
        });
        // survey crud
        Breadcrumbs::for('survey.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Surveys');
        });
        Breadcrumbs::for('survey.create', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Surveys', route('admin.zcsurvey.index'));
            $trail->push('Add Survey');
        });
        Breadcrumbs::for('survey.edit', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Surveys', route('admin.zcsurvey.index'));
            $trail->push('Edit Survey');
        });
        Breadcrumbs::for('survey.preview', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Surveys', route('admin.zcsurvey.index'));
            $trail->push('View Questions');
        });
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-survey') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            $user = auth()->user();
            $data = array();

            $data['pagination']    = config('zevolifesettings.datatable.pagination.short');
            $data['timezone']      = (!empty($user->timezone) ? $user->timezone : config('app.timezone'));
            $data['date_format']   = config('zevolifesettings.date_format.moment_default_datetime');
            $data['survey_status'] = [
                'Draft'     => 'Draft',
                'Published' => 'Published',
                'Assigned'  => 'Assigned',
            ];
            $data['statusColVisibility']  = true;
            $data['companyColVisibility'] = true;
            $data['ga_title']             = trans('page_title.zcsurvey.zcsurvey_list');
            return \view('admin.zcsurvey.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.zcsurvey.index')->with('message', $messageData);
        }
    }

    /**
     *
     * @param Request $request
     * @return Datatable
     */
    public function getSurveys(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-survey') || $role->group != 'zevo') {
            return response()->json([
                'message' => trans('labels.common_title.unauthorized_access'),
            ], 422);
        }
        try {
            return $this->zcSurveyModel->getTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     *
     * @param Request $request
     * @return string
     */
    public function getSurveySubCategories(SurveyCategory $SurveyCategory)
    {
        $role = getUserRole();
        if (!access()->allow('manage-survey') || $role->group != 'zevo') {
            return response()->json([
                'message' => trans('labels.common_title.unauthorized_access'),
            ], 422);
        }
        try {
            $return        = "";
            $subcategories = $SurveyCategory->subcategories()->where('status', 1)->pluck('display_name', 'id');
            $subcategories->each(function ($item, $key) use (&$return) {
                $return .= "<option value='{$key}'>{$item}</option>";
            });
            return $return;
        } catch (\Exception $exception) {
            report($exception);
            return trans('labels.common_title.something_wrong_try_again');
        }
    }

    /**
     *
     * @param Request $request
     * @return Datatable
     */
    public function getQuestions(ZcSurvey $Survey, Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-survey') || $role->group != 'zevo') {
            return response()->json([
                'message' => trans('labels.common_title.unauthorized_access'),
            ], 422);
        }
        try {
            if ($Survey->getKey() != null) {
                return $this->zcSurveyModel->getQuestionsTableData($request->all(), $Survey);
            } else {
                return $this->zcSurveyModel->getQuestionsTableData($request->all());
            }
        } catch (\Exception $exception) {
            report($exception);
            return trans('labels.common_title.something_wrong_try_again');
        }
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        $role = getUserRole();
        if (!access()->allow('create-survey') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            $user                         = auth()->user();
            $data                         = [];
            $data['question_category']    = SurveyCategory::where('status', 1)->pluck('display_name', 'id')->toArray();
            $data['question_subcategory'] = [];
            $data['timezone']             = (!empty($user->timezone) ? $user->timezone : config('app.timezone'));
            $data['date_format']          = config('zevolifesettings.date_format.moment_default_date');
            $data['pagination']           = config('zevolifesettings.datatable.pagination.short');
            $data['ga_title']             = trans('page_title.zcsurvey.create');
            return \view('admin.zcsurvey.create', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.zcsurvey.index')->with('message', $messageData);
        }
    }

    /**
     * @param CreateZCSurveyRequest $request
     *
     * @return RedirectResponse
     */
    public function store(CreateZCSurveyRequest $request)
    {
        $role = getUserRole();
        if (!access()->allow('create-survey') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            \DB::beginTransaction();
            $data = $this->zcSurveyModel->storeEntity($request->all());
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('labels.zcsurvey.data_add_success'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.zcsurvey.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('labels.common_title.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.zcsurvey.create')->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.zcsurvey.index')->with('message', $messageData);
        }
    }

    /**
     * @param ZcSurvey $zcsurvey
     * @return View
     */
    public function edit(ZcSurvey $zcSurvey)
    {
        $role = getUserRole();
        if (!access()->allow('update-survey') || $role->group != 'zevo') {
            abort(403);
        }

        if ($zcSurvey->status != 'Draft') {
            abort(403);
        }

        try {
            $user                         = auth()->user();
            $data                         = [];
            $data['data']                 = $zcSurvey;
            $data['question_category']    = SurveyCategory::where('status', 1)->pluck('display_name', 'id')->toArray();
            $data['question_subcategory'] = [];
            $data['timezone']             = (!empty($user->timezone) ? $user->timezone : config('app.timezone'));
            $data['date_format']          = config('zevolifesettings.date_format.moment_default_date');
            $data['pagination']           = config('zevolifesettings.datatable.pagination.short');
            $data['ga_title']             = trans('page_title.zcsurvey.edit');
            return \view('admin.zcsurvey.edit', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.zcsurvey.index')->with('message', $messageData);
        }
    }

    /**
     * @param EditZCSurveyRequest $request
     *
     * @return RedirectResponse
     */
    public function update(EditZCSurveyRequest $request, ZcSurvey $zcSurvey)
    {
        $role = getUserRole();
        if (!access()->allow('update-survey') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            if ($zcSurvey->status != "Draft") {
                $messageData = [
                    'data'   => 'This action is unauthorized.',
                    'status' => 0,
                ];
                return \Redirect::route('admin.zcsurvey.index')->with('message', $messageData);
            }

            \DB::beginTransaction();

            $data = $zcSurvey->updateEntity($request->all());
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('labels.zcsurvey.data_update_success'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.zcsurvey.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('labels.common_title.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.zcsurvey.edit', $zcSurvey->id)->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.zcsurvey.index')->with('message', $messageData);
        }
    }

    /**
     * @param ZcSurvey $zcsurvey
     * @param Request $request
     * @return json
     */
    public function publish(ZcSurvey $zcSurvey, Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('update-survey') || $role->group != 'zevo') {
            $messageData = [
                'data'   => 'This action is unauthorized.',
                'status' => 0,
            ];
            return response()->json($messageData);
        }

        try {
            $data = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            \DB::beginTransaction();

            if ($request->action == "publish") {
                if ($zcSurvey->status == "Draft") {
                    $zcSurvey->status = "Published";
                    $zcSurvey->save();
                    $data['data']   = "Survey status has been changed to published successfully.";
                    $data['status'] = 1;
                } else {
                    $data['message'] = "Survey status is already published or assigned.";
                }
            } elseif ($request->action == "unpublish") {
                if ($zcSurvey->surveycompany()->count() <= 0) {
                    if ($zcSurvey->status != "Draft") {
                        $zcSurvey->status = "Draft";
                        $zcSurvey->save();
                        $data['data']   = "Survey status has been changed to draft successfully.";
                        $data['status'] = 1;
                    } else {
                        $data['message'] = "Survey is already draft. ";
                    }
                } else {
                    $data['data'] = "Survey has been assigned to company(s) so status can't be changed now.";
                }
            }

            \DB::commit();
            return response()->json($data);
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @param  ZcSurvey $zcSurvey
     * @return json
     */
    public function delete(ZcSurvey $zcSurvey)
    {
        $role = getUserRole();
        if (!access()->allow('delete-survey') || $role->group != 'zevo') {
            abort(403);
        }

        if ($zcSurvey->status != 'Draft') {
            $messageData = [
                'data'   => 'This action is unauthorized.',
                'status' => 0,
            ];
            return response()->json($messageData);
        }

        try {
            return $zcSurvey->deleteRecord();
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @param  ZcSurvey $zcSurvey
     * @return json
     */
    public function view(ZcSurvey $zcSurvey)
    {
        $role = getUserRole();
        if (!access()->allow('view-survey') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            $data                    = [];
            $data['zcSurvey']        = $zcSurvey;
            $data['questions']       = $zcSurvey->surveyQuestions()->orderBy('order_priority', 'ASC')->get();
            $data['total_questions'] = count($data['questions']);
            $data['ga_title']        = trans('page_title.zcsurvey.view');
            return \view('admin.zcsurvey.view', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }
    /**
     * @param  ZcSurvey $ZcSurvey
     * @return json
     */
    public function copy(ZcSurvey $zcSurvey)
    {
        $role = getUserRole();
        if (!access()->allow('create-survey') || $role->group != 'zevo') {
            $messageData = [
                'data'   => "This action is unauthorized.",
                'status' => false,
            ];
            return response()->json($messageData);
        }

        try {
            \DB::beginTransaction();
            $data = $zcSurvey->copy();
            if ($data) {
                \DB::commit();
                return response()->json(['status' => true, 'data' => trans('labels.zcsurvey.copied_success')]);
            } else {
                \DB::rollback();
                return response()->json(['status' => false, 'data' => trans('labels.zcsurvey.copied_error')]);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => false,
            ];
            return response()->json($messageData);
        }
    }

    public function viewQuestion(ZcSurvey $zcSurvey)
    {
        $role = getUserRole();
        if (!access()->allow('view-survey') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            $user                = auth()->user();
            $data                = [];
            $data['zcSurvey']    = $zcSurvey;
            $data['pagination']  = config('zevolifesettings.datatable.pagination.short');
            $data['timezone']    = (!empty($user->timezone) ? $user->timezone : config('app.timezone'));
            $data['date_format'] = config('zevolifesettings.date_format.moment_default_date');
            $data['ga_title']    = trans('page_title.zcsurvey.view_question');

            return \view('admin.zcsurvey.view-question', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    public function responseSurvey($surveyId)
    {
        try {
            $decryptedSurveydata = decrypt($surveyId);
            $decryptedSurveydata = explode(':', $decryptedSurveydata);
            $email               = (!empty($decryptedSurveydata[0]) ? $decryptedSurveydata[0] : 0);
            $surveyLogId         = (!empty($decryptedSurveydata[1]) ? $decryptedSurveydata[1] : 0);
            $surveyLog           = ZcSurveyLog::find($surveyLogId);
            $user                = User::where('email', $email)->first();
            $data                = [];
            $data['user']        = $user;
            $data['branding']    = getBrandingData();

            if (!empty($user) && !empty($surveyLog)) {
                $company                 = $user->company->first();
                $timezone                = (!empty($user->timezone) ? $user->timezone : config('app.timezone'));
                $now                     = \now($timezone)->toDateTimeString();
                $subscription_start_date = Carbon::parse($company->subscription_start_date, config('app.timezone'))->setTimezone($timezone)->toDateTimeString();
                $subscription_end_date   = Carbon::parse($company->subscription_end_date, config('app.timezone'))->setTimezone($timezone)->toDateTimeString();

                if ($now > $subscription_start_date && $now < $subscription_end_date) {
                    $surveyExpireDate = Carbon::parse($surveyLog->expire_date, config('app.timezone'))->setTimezone($timezone)->toDateTimeString();

                    if ($surveyExpireDate > $now) {
                        $userSurveyLog = ZcSurveyUserLog::where(['user_id' => $user->id, 'survey_log_id' => $surveyLogId])->first();
                        $data['surveyId'] = $surveyId;
                        $data['survey']   = $surveyLog->survey;

                        if ($company->is_branding) {
                            $companyId                 = (!$company->is_reseller && !is_null($company->parent_id)) ? $company->parent_id : $company->id;
                            $data['companyBranding']   = getBrandingData($companyId);
                            $data['surveyBrandingURL'] = addhttp(getBrandingUrlSurvey($data['companyBranding']->sub_domain));
                        } else {
                            $data['surveyBrandingURL'] = $data['branding']->url;
                        }

                        $data['alreadySubmitted'] = (isset($userSurveyLog) && !is_null($userSurveyLog->survey_submitted_at));
                        $data['questions']        = $surveyLog->survey->surveyQuestions()
                            ->select('id', 'question_id', 'question_type_id')
                            ->orderBy('order_priority', 'ASC')->get();
                        $data['total_questions'] = $data['questions']->count();
                        $data['ga_title']        = (!empty($data['survey']->title)) ? $data['survey']->title : trans('page_title.zcsurveyrollout');
                        return \view('admin.zcsurvey.survey-submission', $data);
                    } else {
                        return \view('errors.survey_expired', ['home_button' => true, 'message' => "This survey has expired.", "sub_message" => "It's possible you already completed the survey or a new survey has been released. Please close this window."]);
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

    public function getSurveyQuestion($questionString, ZcSurveyQuestion $question, Request $request)
    {
        try {
            if ($request->ajax()) {
                $questionString      = decrypt($questionString);
                $questionString      = explode(":", $questionString);
                $decryptedQuestionId = ($questionString[0] ?? 0);
                $sequence            = ($questionString[1] ?? 0);
                $totalQuestions      = ($questionString[2] ?? 1);
                if ($decryptedQuestionId == $question->id) {
                    $data = [
                        'question'        => $question,
                        'step'            => $sequence,
                        'total_questions' => $totalQuestions,
                    ];
                    if ($question->question_type_id == 1) {
                        return \view('admin.zcsurvey.freetext_question_preview', $data);
                    } elseif ($question->question_type_id == 2) {
                        return \view('admin.zcsurvey.choice_question_preview', $data);
                    }
                }
            }
            return \view('errors.400');
        } catch (\Illuminate\Contracts\Encryption\DecryptException $exception) {
            return \view('errors.400');
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    public function submitSurvey($surveyId, StoreZcSurveyResponse $request)
    {
        try {
            $decryptedSurveydata = decrypt($surveyId);
            $decryptedSurveydata = explode(':', $decryptedSurveydata);
            $email               = (!empty($decryptedSurveydata[0]) ? $decryptedSurveydata[0] : 0);
            $surveyLogId         = (!empty($decryptedSurveydata[1]) ? $decryptedSurveydata[1] : 0);
            $surveyLog           = ZcSurveyLog::find($surveyLogId);
            $user                = User::select('id', 'email', 'timezone')->where('email', $email)->first();

            if (!empty($user) && !empty($surveyLog)) {
                $company                 = $user->company->first();
                $timezone                = (!empty($user->timezone) ? $user->timezone : config('app.timezone'));
                $now                     = \now($timezone)->toDateTimeString();
                $subscription_start_date = Carbon::parse($company->subscription_start_date, config('app.timezone'))->setTimezone($timezone)->toDateTimeString();
                $subscription_end_date   = Carbon::parse($company->subscription_end_date, config('app.timezone'))->setTimezone($timezone)->toDateTimeString();

                if ($now > $subscription_start_date && $now < $subscription_end_date) {
                    $surveyExpireDate = Carbon::parse($surveyLog->expire_date, \config('app.timezone'))->setTimezone($user->timezone)->toDateTimeString();
                    if ($surveyExpireDate > $now) {
                        $userSurveyLog = ZcSurveyUserLog::where(['user_id' => $user->id, 'survey_log_id' => $surveyLogId])->first();

                        \DB::beginTransaction();

                        $submitted = $userSurveyLog->storeResponse($request->all());

                        if ($submitted) {
                            if (!is_null($company)) {
                                $surveyLog->rewardPortalPointsToUser($user, $company, 'audit_survey', [
                                    'survey_id' => $surveyLog->survey_id,
                                ]);
                            }

                            \DB::commit();
                            return response()->json([
                                'status' => 1,
                            ], 200);
                        } else {
                            \DB::rollback();
                            return response()->json([
                                'status'  => 0,
                                'message' => trans('labels.common_title.something_wrong_try_again'),
                            ], 422);
                        }
                    } else {
                        return response()->json([
                            'status'  => 2,
                            'message' => trans("This survey has been expired!"),
                        ], 422);
                    }
                } else {
                    return response()->json([
                        'status'  => 4,
                        'message' => trans("It seems your company's subscription isn't active, Your company subscription has expired. Please contact your admin or the Zevo Account Manager"),
                    ], 422);
                }
            } else {
                return response()->json([
                    'status'  => 0,
                    'message' => trans('It seems request is malformed or bad.'),
                ], 400);
            }
        } catch (\Illuminate\Contracts\Encryption\DecryptException $exception) {
            return response()->json([
                'status'  => 0,
                'message' => trans('It seems request is malformed or bad.'),
            ], 400);
        } catch (\Exception $exception) {
            report($exception);
            return response()->json([
                'status'  => 0,
                'message' => trans('labels.common_title.something_wrong_try_again'),
            ], 500);
        }
    }

    public function storeSurveyReview($surveyId, StoreZcSurveyReviewSuggestion $request)
    {
        try {
            $decryptedSurveydata = decrypt($surveyId);
            $decryptedSurveydata = explode(':', $decryptedSurveydata);
            $email               = (!empty($decryptedSurveydata[0]) ? $decryptedSurveydata[0] : 0);
            $surveyLogId         = (!empty($decryptedSurveydata[1]) ? $decryptedSurveydata[1] : 0);
            $surveyLog           = ZcSurveyLog::find($surveyLogId);
            $user                = User::where('email', $email)->first();

            if (!empty($user) && !empty($surveyLog)) {
                \DB::beginTransaction();
                $userSurveyLog    = ZcSurveyUserLog::where(['user_id' => $user->id, 'survey_log_id' => $surveyLogId])->first();

                $submitted = $userSurveyLog->storeSurveyReview($request->all());

                if ($submitted) {
                    \DB::commit();
                    return response()->json([
                        'status'  => 1,
                        'message' => "Thank you for sharing your thoughts!",
                    ], 200);
                } else {
                    \DB::rollback();
                    return response()->json([
                        'status'  => 0,
                        'message' => trans('labels.common_title.something_wrong_try_again'),
                    ], 422);
                }
            } else {
                return response()->json([
                    'status'  => 0,
                    'message' => trans('It seems request is malformed or bad.'),
                ], 400);
            }
        } catch (\Illuminate\Contracts\Encryption\DecryptException $exception) {
            return response()->json([
                'status'  => 0,
                'message' => trans('It seems request is malformed or bad.'),
            ], 400);
        } catch (\Exception $exception) {
            report($exception);
            return response()->json([
                'status'  => 0,
                'message' => trans('labels.common_title.something_wrong_try_again'),
            ], 500);
        }
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function reviewSuggestion(Request $request)
    {
        $user                       = auth()->user();
        $role                       = getUserRole($user);
        $checkPlanAccess            = getCompanyPlanAccess($user, 'wellbeing-score-card');
        $checkPlanAccessForReseller = getDTAccessForParentsChildCompany($user, 'wellbeing-scorecard');
        if (!access()->allow('review-suggestion')  || ($role->group == 'company' &&  !$checkPlanAccess) || ($role->group == 'reseller' &&  !$checkPlanAccessForReseller)) {
            abort(403);
        }

        try {
            $data = [
                'company_id'  => 0,
                'pagination'  => config('zevolifesettings.datatable.pagination.long'),
                'timezone'    => (!empty($user->timezone) ? $user->timezone : config('app.timezone')),
                'date_format' => config('zevolifesettings.date_format.moment_default_date'),
                'SAOnly'      => ($role->group == 'zevo'),
                'ga_title'    => trans('page_title.review-suggestion'),
            ];

            if ($role->group == 'zevo') {
                $data['company'] = Company::pluck('name', 'id')->toArray();
            } else {
                $companyData        = $user->company->first();
                $data['company_id'] = $companyData->id;
                $data['company']    = [$companyData->id => $companyData->name];
            }

            return \view('admin.zcsurvey.review-suggestion.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.reviewSuggestion.index')->with('message', $messageData);
        }
    }

    /**
     *
     * @param Request $request
     * @return Datatable
     */
    public function getSuggestions(Request $request)
    {
        if (!access()->allow('review-suggestion')) {
            return response()->json([
                'message' => trans('labels.common_title.unauthorized_access'),
            ], 422);
        }
        try {
            return $this->zcSurveyReviewSuggestion->getTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     *
     * @param suggestionId $suggestionId
     * @param Request $request
     *
     * @return Array
     */
    public function suggestionAction(ZcSurveyReviewSuggestion $suggestionId, Request $request)
    {
        if (!access()->allow('favorite-suggestion')) {
            $messageData = [
                'data'   => 'This action is unauthorized.',
                'status' => 0,
            ];
            return response()->json($messageData);
        }
        $return = $suggestionId->suggestionAction($request->all());

        try {
            \DB::beginTransaction();
            if (isset($return['status']) && $return['status'] == 1) {
                \DB::commit();
            } else {
                \DB::rollback();
            }
            return response()->json($return);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function surveyInsights(Request $request)
    {
        $user                       = auth()->user();
        $role                       = getUserRole($user);
        $checkPlanAccess            = getCompanyPlanAccess($user, 'wellbeing-score-card');
        $checkPlanAccessForReseller = getDTAccessForParentsChildCompany($user, 'wellbeing-scorecard');
        if (!access()->allow('survey-insights')  || ($role->group == 'company' &&  !$checkPlanAccess) || ($role->group == 'reseller' &&  !$checkPlanAccessForReseller)) {
            abort(403);
        }

        try {
            $data = [
                'pagination'  => config('zevolifesettings.datatable.pagination.short'),
                'timezone'    => (!empty($user->timezone) ? $user->timezone : config('app.timezone')),
                'date_format' => config('zevolifesettings.date_format.moment_default_date'),
                'SAOnly'      => ($role->group == 'zevo'),
            ];

            if ($role->group == 'zevo') {
                $data['company'] = Company::pluck('name', 'id')->toArray();
            } else {
                $companyData     = $user->company->first();
                $data['company'] = [$companyData->id => $companyData->name];
            }
            $data['ga_title'] = trans('page_title.zcsurvey.survey-insights');
            return \view('admin.zcsurvey.survey-insights.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.surveyInsights.index')->with('message', $messageData);
        }
    }

    /**
     *
     * @param Request $request
     * @return Datatable
     */
    public function getSurveyInsights(Request $request)
    {
        if (!access()->allow('survey-insights')) {
            return response()->json([
                'message' => trans('labels.common_title.unauthorized_access'),
            ], 422);
        }
        try {
            return $this->zcSurveyLog->getSurveyInsightsTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getSurveyInsight(ZcSurveyLog $surveyLogId)
    {
        if (!access()->allow('survey-insights')) {
            abort(403);
        }

        $role    = getUserRole();
        $user    = auth()->user();
        $company = $user->company->first();
        if ($role->group != 'zevo' && $surveyLogId->company_id != $company->id) {
            abort(403);
        }

        try {
            $timezone      = (!empty($user->timezone) ? $user->timezone : config('app.timezone'));
            $date_format   = config('zevolifesettings.date_format.default_date');
            $now           = \now($timezone)->toDateTimeString();
            $categories    = [];
            $upcoming_date = null;

            $data = $surveyLogId
                ->leftJoin('zc_survey', function ($join) {
                    $join->on('zc_survey.id', '=', 'zc_survey_log.survey_id');
                })
                ->leftJoin('zc_survey_user_log', function ($join) {
                    $join->on('zc_survey_user_log.survey_log_id', '=', 'zc_survey_log.id');
                })
                ->leftJoin('companies', function ($join) {
                    $join->on('companies.id', '=', 'zc_survey_log.company_id');
                })
                ->select(
                    'zc_survey_log.*',
                    'zc_survey.title AS survey_title',
                    'companies.name AS company_name',
                    \DB::raw('SUM(zc_survey_user_log.survey_submitted_at IS NOT NULL) AS surveyreponses_count'),
                    \DB::raw("(SELECT FORMAT(IFNULL(((IFNULL(SUM(zc_survey_responses.score), 0) * 100) / IFNULL(SUM(zc_survey_responses.max_score), 0)), 0), 2) FROM zc_survey_responses WHERE zc_survey_responses.survey_log_id = zc_survey_log.id) AS percentage"),
                )->selectRaw(
                    "CONVERT_TZ(zc_survey_log.roll_out_date, ?, ?) AS roll_out_date"
                ,['UTC',$timezone])
                ->selectRaw(
                    "CONVERT_TZ(zc_survey_log.expire_date, ?, ?) AS expire_date"
                ,['UTC',$timezone])
                ->selectRaw(
                    "IF((CONVERT_TZ(zc_survey_log.expire_date, ?, ?) > ?), 1, 0) AS `status`"
                ,['UTC',$timezone,$now])    
                ->where('zc_survey_log.id', $surveyLogId->id)
                ->first();

            $surveyLogId
                ->leftJoin('zc_survey_questions', function ($join) {
                    $join->on('zc_survey_questions.survey_id', '=', 'zc_survey_log.survey_id');
                })
                ->leftJoin('zc_categories', function ($join) {
                    $join->on('zc_categories.id', '=', 'zc_survey_questions.category_id');
                })
                ->select(
                    "zc_categories.display_name AS category_name",
                    "zc_survey_questions.category_id",
                    \DB::raw("MIN(zc_survey_questions.id) AS min_id"),
                    \DB::raw("(SELECT FORMAT(IFNULL(((IFNULL(SUM(zc_survey_responses.score), 0) * 100) / IFNULL(SUM(zc_survey_responses.max_score), 0)), 0), 2) FROM zc_survey_responses WHERE zc_survey_responses.survey_log_id = zc_survey_log.id AND zc_survey_responses.category_id = zc_categories.id) AS percentage")
                )
                ->where('zc_survey_log.id', $surveyLogId->id)
                ->groupBy('zc_survey_questions.category_id')
                ->orderBy('min_id')
                ->each(function ($category) use (&$categories) {
                    $surveyCategoryRecords = SurveyCategory::find($category->category_id, ['id']);
                    $categories[]   = [
                        'category_id'   => $category->category_id,
                        'category_name' => $category->category_name,
                        'percentage'    => $category->percentage,
                        'image'         => $surveyCategoryRecords->logo,
                        'color_code'    => getScoreColor($category->percentage),
                    ];
                });

            // if currently opened survey is running then will show upcoming date accordingly
            if ($data->status) {
                $upcoming_date = $this->surveyUpcomingdate($user, $data);
            } else {
                // if currently opened survey is expired then will show upcoming in last expired survey only not in every expired survey
                $companySetting = ZcSurveySettings::where('company_id', $surveyLogId->company_id)->first();
                $lastSurvey     = $surveyLogId::where('zc_survey_log.company_id', $surveyLogId->company_id)->orderByDesc('id')->first();
                if (!empty($companySetting) && $lastSurvey->id == $surveyLogId->id) {
                    $survey_frequency_day = config('zevolifesettings.survey_frequency_day');
                    $survey_frequency_day = $survey_frequency_day[$companySetting->survey_frequency];
                    
                    $lastSurvey->roll_out_date = Carbon::parse($lastSurvey->roll_out_date);
                    $lastSurvey->expire_date   = Carbon::parse($lastSurvey->expire_date);
                    $diffInDays           = $lastSurvey->roll_out_date->diffInDays($lastSurvey->expire_date);
                    
                    if ($diffInDays == $survey_frequency_day) {
                        $upcoming_date = $this->surveyUpcomingdate($user, $data);
                    } else {
                        $upcoming_date = ($lastSurvey->expire_date->is($companySetting->survey_roll_out_day) ? $lastSurvey->expire_date : $lastSurvey->expire_date->next($companySetting->survey_roll_out_day));
                    }
                }
            }

            $data = [
                'pagination'              => config('zevolifesettings.datatable.pagination.short'),
                'data'                    => $data,
                'date_format'             => $date_format,
                'categories'              => $categories,
                'survey_chart_color_code' => getScoreColor($data->percentage),
                'upcoming_date'           => $upcoming_date,
                'ga_title'                => trans('page_title.zcsurvey.details'),
            ];

            return \view('admin.zcsurvey.survey-insights.details', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.surveyInsights.index')->with('message', $messageData);
        }
    }

    /**
     *
     * @param Request $request
     * @return Datatable
     */
    public function getSurveyInsightQuestionsTableData(ZcSurveyLog $surveyLogId, SurveyCategory $categoryId, Request $request)
    {
        if (!access()->allow('survey-insights')) {
            return response()->json([
                'message' => trans('labels.common_title.unauthorized_access'),
            ], 422);
        }
        try {
            return $surveyLogId->getSurveyInsightQuestionsTableData($categoryId, $request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function hrReport(Request $request)
    {
        $user                       = auth()->user();
        $role                       = getUserRole($user);
        $checkPlanAccess            = getCompanyPlanAccess($user, 'wellbeing-score-card');
        $checkPlanAccessForReseller = getDTAccessForParentsChildCompany($user, 'wellbeing-scorecard');
        if (!access()->allow('hr-report')  || ($role->group == 'company' &&  !$checkPlanAccess) || ($role->group == 'reseller' &&  !$checkPlanAccessForReseller)) {
            abort(403);
        }

        try {
            $timezone = !empty($user->timezone) ? $user->timezone : config('app.timezone');
            $data     = [
                'pagination'           => config('zevolifesettings.datatable.pagination.long'),
                'timezone'             => (!empty($user->timezone) ? $user->timezone : config('app.timezone')),
                'date_format'          => config('zevolifesettings.date_format.moment_default_date'),
                'SAOnly'               => ($role->group == 'zevo'),
                'companyColVisibility' => (($role->group == 'zevo') ? '' : 'd-none'),
            ];
            $company_id = (int) $request->input('company');
            $from       = (!empty($request->input('from')) && strtotime($request->input('from')) !== false) ? $request->input('from') : null;
            $to         = (!empty($request->input('to')) && strtotime($request->input('to')) !== false) ? $request->input('to') : null;

            if ($role->group == 'zevo') {
                $data['company'] = Company::pluck('name', 'id')->toArray();
            } else {
                $company = $user->company->first();

                if (!empty($company_id) && $company_id != $company->id) {
                    return \view('errors.401');
                }

                $company_id      = $company->id;
                $data['company'] = [$company->id => $company->name];
            }

            $categories = $this->zcSurveyResponse
                ->select(
                    'zc_survey_responses.category_id AS id',
                    'zc_categories.name',
                    'zc_categories.display_name',
                    \DB::raw("FORMAT(IFNULL(((IFNULL(SUM(zc_survey_responses.score), 0) * 100) / IFNULL(SUM(zc_survey_responses.max_score), 0)), 0), 2) AS category_percent")
                )
                ->join('zc_categories', function ($join) {
                    $join->on('zc_categories.id', '=', 'zc_survey_responses.category_id');
                })
                ->groupBy('zc_survey_responses.category_id');

            if (!empty($company_id)) {
                $categories->where('zc_survey_responses.company_id', $company_id);
            }

            if (!empty($from) && !empty($to)) {
                $start_date = Carbon::parse($from, $timezone)->setTimeZone($timezone)->toDateTimeString();
                $end_date   = Carbon::parse($to, $timezone)->setTimeZone($timezone)->format('Y-m-d 23:59:59');
                $categories
                    ->whereRaw("CONVERT_TZ(zc_survey_responses.created_at, ? , ?) BETWEEN ? AND ?" , ['UTC', $timezone, $start_date, $end_date]);
            }

            $data['categories']    = $categories->get()->toArray();
            $data['column_width']  = number_format((100 / (count($data['categories']) + 2)), 2);
            $data['requestParams'] = $request->only('from', 'to', 'company');
            $data['ga_title']      = trans('page_title.zcsurvey.hr-report');
            return \view('admin.zcsurvey.hr-report.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            return response('Something wrong', 500)->header('Content-Type', 'text/plain');
        }
    }

    /**
     *
     * @param Request $request
     * @return Datatable
     */
    public function gethrReportsData(Request $request)
    {
        if (!access()->allow('hr-report')) {
            return response()->json([
                'message' => trans('labels.common_title.unauthorized_access'),
            ], 422);
        }
        try {
            return $this->zcSurveyResponse->getHrReportTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     *
     * @param Request $request
     * @return JSON
     */
    public function getHrReporDetails(Company $companyId, Department $departmentId, SurveyCategory $categoryId, Request $request)
    {

        if (!access()->allow('view-hr-report')) {
            $messageData = [
                'data'   => trans('labels.common_title.unauthorized_access'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }

        try {
            return $this->zcSurveyResponse->getHrReportDetails($companyId, $departmentId, $categoryId, $request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function reviewFreeText(Request $request)
    {
        if (!access()->allow('hr-report')) {
            abort(403);
        }

        try {
            $role           = getUserRole();
            $user           = auth()->user();
            $timezone       = !empty($user->timezone) ? $user->timezone : config('app.timezone');
            $category       = $request->get('category');
            $from           = strtotime($request->get('from')) !== false ? $request->get('from') : null;
            $to             = strtotime($request->get('to')) !== false ? $request->get('to') : null;
            $company_id     = (int) $request->input('company');
            $longPagination = config('zevolifesettings.datatable.pagination.long');
            $company        = [];

            if ($role->group == 'zevo') {
                $company = Company::pluck('name', 'id')->toArray();
            } else {
                $company = $user->company->first();
                if (!empty($company_id) && $company_id != $company->id) {
                    return \view('errors.401');
                }
                $company_id = (!empty($company_id) ? $company_id : $company->id);
                $company    = [$company->id => $company->name];
            }

            $questions = ZcSurveyResponse::select(
                'zc_survey_responses.question_id AS id',
                'zc_survey_responses.category_id',
                'zc_questions.title AS question',
                'zc_categories.display_name AS category_name',
                'zc_sub_categories.display_name AS sub_category_name'
            )
                ->join('zc_questions', function ($join) {
                    $join->on('zc_questions.id', '=', 'zc_survey_responses.question_id');
                })
                ->join('zc_categories', function ($join) {
                    $join->on('zc_categories.id', '=', 'zc_questions.category_id');
                })
                ->join('zc_sub_categories', function ($join) {
                    $join->on('zc_sub_categories.id', '=', 'zc_questions.sub_category_id');
                })
                ->whereNull('zc_survey_responses.max_score')
                ->groupBy('zc_survey_responses.question_id')
                ->orderByDesc('zc_survey_responses.question_id');

            if (!empty($company_id)) {
                $questions->where('zc_survey_responses.company_id', $company_id);
            }

            $categoryOptions = $questions->pluck('category_name', 'category_id')->toArray();

            if (!empty($category)) {
                $questions->where('zc_survey_responses.category_id', $category);
            }

            if (!empty($from) && !empty($to)) {
                $startDate = Carbon::parse($from, $timezone)->timezone($timezone)->toDateTimeString();
                $endDate   = Carbon::parse($to, $timezone)->timezone($timezone)->format('Y-m-d 23:59:59');
                $questions->whereRaw("CONVERT_TZ(zc_survey_responses.created_at, ? , ?) BETWEEN ? AND ?" , ['UTC', $timezone, $startDate, $endDate]);
            }

            $questions = $questions->paginate($longPagination);

            $data = [
                'qespagination' => $longPagination,
                'anspagination' => $longPagination,
                'company'       => $company,
                'category'      => $categoryOptions,
                'questions'     => $questions,
                'currPage'      => ($questions->currentPage() - 1),
                'queryString'   => $request->all(),
                'isSA'          => ($role->group == 'zevo'),
                'company_id'    => $company_id,
                'requestParams' => $request->only('from', 'to'),
                'ga_title'      => trans('page_title.zcsurvey.review_free_text_question'),
            ];

            return \view('admin.zcsurvey.hr-report.review-free-text', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.hrReport.index')->with('message', $messageData);
        }
    }

    /**
     * get comapny wise category for free review text
     *
     * @param Company $company
     * @param Request $request
     *
     * @return json
     */
    public function getCompanyWiseCategoryForReviewText(Company $company, Request $request)
    {
        if (!access()->allow('hr-report')) {
            return response()->json([
                'message' => trans('labels.common_title.unauthorized_access'),
            ], 422);
        }
        try {
            $return = "";
            $this->surveyCategory
                ->select('id', 'display_name')
                ->withCount(['responses' => function ($query) use ($company) {
                    $query->whereNull('zc_survey_responses.max_score');
                    if (!empty($company->id)) {
                        $query->where('company_id', $company->id);
                    }
                }])
                ->having('responses_count', '>', 0)
                ->get()
                ->pluck('display_name', 'id')
                ->each(function ($item, $key) use (&$return) {
                    $return .= "<option value='{$key}'>{$item}</option>";
                });
            return $return;
        } catch (\Exception $exception) {
            report($exception);
            return trans('labels.common_title.something_wrong_try_again');
        }
    }

    /**
     *
     * @param Request $request
     * @return Datatable
     */
    public function getFreeTextAnswers(ZcQuestion $question, Request $request)
    {
        if (!access()->allow('hr-report')) {
            return response()->json([
                'message' => trans('labels.common_title.unauthorized_access'),
            ], 422);
        }
        try {
            return $question->getFreeTextAnswersTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @param survey data
     * @param user
     * @return date
     */
    public function surveyUpcomingdate($user, $data = array())
    {
        $companyId   = $data->company_id;
        $rollOutDate = $data->roll_out_date;
        $timezone    = (!empty($user->timezone) ? $user->timezone : config('app.timezone'));
        $companyData = ZcSurveySettings::select('survey_frequency', 'survey_roll_out_day')
            ->where('company_id', $companyId)
            ->get()
            ->first();
        if (!empty($companyData)) {
            $dt = Carbon::parse($rollOutDate, config('app.timezone'))->setTimezone($timezone);
            switch ($companyData->survey_frequency) {
                case '1':
                    # Weekly
                    $udt = $dt->addDay(7);
                    break;
                case '2':
                    # Bi-weekly
                    $udt = $dt->addDay(14);
                    break;
                case '3':
                    # Monthly
                    $udt = $dt->addMonth();
                    break;
                case '4':
                    # Quarterly
                    $udt = $dt->addMonths(3);
                    break;
                default:
                    # Half Yearly
                    $udt = $dt->addMonths(6);
                    break;
            }
            return $udt->is($companyData->survey_roll_out_day) ? $udt : $udt->next($companyData->survey_roll_out_day);
        }
    }
}
