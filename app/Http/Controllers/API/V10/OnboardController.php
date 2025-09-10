<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V10;

use App\Http\Collections\V8\GoalTagCollection;
use App\Http\Collections\V10\AppSlideCollection;
use App\Http\Collections\V10\PortalSurveyCollection;
use App\Http\Controllers\API\V1\OnboardController as v1OnboardController;
use App\Http\Requests\Api\V10\PortalSurveyRequest;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\AppSlide;
use App\Models\Goal;
use App\Models\ZcQuestion;
use App\Models\ZcSurveyLog;
use App\Models\ZcSurveyResponse;
use App\Models\ZcSurveyUserLog;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnboardController extends v1OnboardController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Returns onboarding slides
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sliders(Request $request)
    {
        try {
            $userSelectedGoal = [];
            $xDeviceOs        = strtolower($request->header('X-Device-Os', ""));
            $type             = ($xDeviceOs == config('zevolifesettings.PORTAL')) ? "portal" : "app";

            $slideRecords = AppSlide::where('type', $type)->orderBy("order_priority", "ASC")->paginate(3);

            $goalObj     = new Goal();
            $goalRecords = $goalObj->getAssociatedGoalTags();
            if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
                // logged-in user
                $user             = $this->user();
                $userSelectedGoal = $user->userGoalTags()->pluck("goals.id")->toArray();
            }
            $data = array();

            $data['data']['sliders'] = ($slideRecords->count() > 0) ? new AppSlideCollection($slideRecords) : [];
            $data['data']['goals']   = ($goalRecords->count() > 0) ? new GoalTagCollection($goalRecords, $userSelectedGoal) : [];

            return $this->successResponse($data, 'Data retrieved successfully.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Returns onboarding survey
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function survey(? ZcSurveyLog $surveyLog, Request $request)
    {
        try {
            // logged-in user
            $user    = $this->user();
            $company = $user->company->first();

            $timezone  = (!empty($user->timezone) ? $user->timezone : config('app.timezone'));
            $todayDate = Carbon::now()->setTimezone($timezone)->toDateTimeString();

            $zcsurveylog = ZcSurveyLog::where('company_id', $company->id)
                ->where('roll_out_date', '<=', $todayDate)
                ->where('expire_date', '>=', $todayDate)
                ->orderBy('id', 'DESC')
                ->get()
                ->first();

            $data = array();
            if ($zcsurveylog) {
                $zcSurveyLogId = $zcsurveylog->id;

                $zcSurveyResponse = ZcSurveyResponse::where('survey_log_id', $zcSurveyLogId)
                    ->where('user_id', $user->id)
                    ->where('company_id', $company->id)
                    ->count();

                if ($zcSurveyResponse == 0) {
                    $surveyQuestions = ZcQuestion::leftJoin('zc_survey_questions', 'zc_questions.id', '=', 'zc_survey_questions.question_id')
                        ->Join('zc_question_types', 'zc_question_types.id', '=', 'zc_survey_questions.question_type_id')
                        ->where('zc_survey_questions.survey_id', $zcsurveylog->survey_id)
                        ->orderBy("zc_survey_questions.order_priority", "ASC")
                        ->select(
                            'zc_questions.id',
                            'zc_survey_questions.survey_id',
                            'zc_survey_questions.question_id',
                            'zc_questions.question_type_id',
                            'zc_questions.title',
                            'zc_questions.status',
                            'zc_questions.created_at',
                            'zc_questions.updated_at'
                        )
                        ->get();

                    if (!empty($surveyQuestions)) {
                        $data['data']['surveyId']  = $zcSurveyLogId;
                        $data['data']['questions'] = ($surveyQuestions->count() > 0) ? new PortalSurveyCollection($surveyQuestions) : [];

                        return $this->successResponse($data, 'Data retrieved successfully.');
                    }

                    return $this->invalidResponse($data, 'Survey not found.', 404);
                }
                return $this->invalidResponse($data, 'Survey already submitted.', 404);
            }
            return $this->invalidResponse($data, 'Survey not found.', 404);
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Submit onboarding Survey
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitSurvey(PortalSurveyRequest $request)
    {
        try {
            // logged-in user
            $user = $this->user();

            $company    = $user->company->first();
            $department = $user->department->first();
            $survey_id  = $request->input('surveyId');
            $response   = $request->input('response');
            $timezone  = (!empty($user->timezone) ? $user->timezone : config('app.timezone'));
            $todayDate  = Carbon::now()->setTimezone($timezone)->toDateTimeString();

            $zcsurveylog = ZcSurveyLog::where('company_id', $company->id)
                ->where('roll_out_date', '<=', $todayDate)
                ->where('expire_date', '>=', $todayDate)
                ->orderBy('id', 'DESC')
                ->get()
                ->first();

            $data = array();
            if ($zcsurveylog) {
                $zcSurveyLogId = $zcsurveylog->id;

                $zcSurveyResponse = ZcSurveyResponse::where('survey_log_id', $zcSurveyLogId)
                    ->where('user_id', $user->id)
                    ->where('company_id', $company->id)
                    ->count();

                if ($zcSurveyResponse == 0) {
                    if ($response) {
                        // Submitted survey log data
                        $zcSurveyUserLog = new ZcSurveyUserLog;

                        $zcSurveyUserLog->user_id             = $user->id;
                        $zcSurveyUserLog->survey_log_id       = $survey_id;
                        $zcSurveyUserLog->survey_submitted_at = $todayDate;
                        $zcSurveyUserLog->created_at          = $todayDate;
                        $zcSurveyUserLog->updated_at          = $todayDate;

                        $zcSurveyUserLog->save();

                        if ($zcSurveyUserLog) {
                            // Submitted survey response data
                            foreach ($response as $key => $value) {
                                $surveyQuestion = ZcSurveyLog::join('zc_survey_questions', 'zc_survey_questions.survey_id', '=', 'zc_survey_log.survey_id')
                                    ->where('zc_survey_questions.question_id', $value['questionId'])
                                    ->where('zc_survey_log.id', $survey_id)
                                    ->select('category_id', 'sub_category_id')
                                    ->get()->first();

                                $responseArray[] = [
                                    'user_id'         => $user->id,
                                    'company_id'      => $company->id,
                                    'department_id'   => $department->id,
                                    'survey_log_id'   => $survey_id,
                                    'category_id'     => (int) $surveyQuestion->category_id,
                                    'sub_category_id' => (int) $surveyQuestion->sub_category_id,
                                    'question_id'     => (int) $value['questionId'],
                                    'score'           => $value['score'],
                                    'answer_value'    => $value['answer'],
                                    'created_at'      => $todayDate,
                                    'updated_at'      => $todayDate,
                                ];
                            }

                            $stored = ZcSurveyResponse::insert($responseArray);
                            if ($stored) {
                                return $this->successResponse(['data' => []], 'Survey submited successfully!!!');
                            }
                        }
                    }
                }
                return $this->invalidResponse($data, 'Survey already submitted.', 404);
            }
            return $this->invalidResponse($data, 'Survey not found.', 404);
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
