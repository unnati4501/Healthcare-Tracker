<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V12;

use App\Http\Collections\V10\PortalSurveyCollection;
use App\Http\Controllers\API\V10\OnboardController as v1OnboardController;
use App\Http\Requests\Api\V10\PortalSurveyRequest;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
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
            $timezone   = (!empty($user->timezone) ? $user->timezone : config('app.timezone'));
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

                if ($zcSurveyResponse <= 0) {
                    if ($response) {
                        // Check survey user log select('id')->
                        $zcSurveyUserLogRecords = ZcSurveyUserLog::where('user_id', $user->id)->where('survey_log_id', $zcSurveyLogId)->first();

                        if (!$zcSurveyUserLogRecords) {
                            // Submitted survey log data
                            $zcSurveyUserLog                      = new ZcSurveyUserLog;
                            $zcSurveyUserLog->user_id             = $user->id;
                            $zcSurveyUserLog->survey_log_id       = $zcSurveyLogId;
                            $zcSurveyUserLog->survey_submitted_at = $todayDate;
                            $zcSurveyUserLog->created_at          = $todayDate;
                            $zcSurveyUserLog->updated_at          = $todayDate;
                            $zcSurveyUserLog->save();
                        } else {
                            $zcSurveyUserLogRecords->survey_submitted_at = $todayDate;
                            $zcSurveyUserLogRecords->updated_at          = $todayDate;
                            $zcSurveyUserLogRecords->save();
                        }

                        // Submitted survey response data
                        foreach ($response as $key => $value) {
                            $surveyQuestion = ZcSurveyLog::join('zc_survey_questions', 'zc_survey_questions.survey_id', '=', 'zc_survey_log.survey_id')
                                ->where('zc_survey_questions.question_id', $value['questionId'])
                                ->where('zc_survey_log.id', $survey_id)
                                ->select('category_id', 'sub_category_id', 'question_type_id')
                                ->get()->first();

                            $responseArray[] = [
                                'user_id'         => $user->id,
                                'company_id'      => $company->id,
                                'department_id'   => $department->id,
                                'survey_log_id'   => $survey_id,
                                'category_id'     => (int) $surveyQuestion->category_id,
                                'sub_category_id' => (int) $surveyQuestion->sub_category_id,
                                'question_id'     => (int) $value['questionId'],
                                'score'           => (($surveyQuestion->question_type_id == 2) ? $value['score'] : 0),
                                'answer_value'    => (($surveyQuestion->question_type_id == 1) ? $value['answer'] : null),
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
                return $this->invalidResponse($data, 'This survey already submitted.', 404);
            }
            return $this->invalidResponse($data, 'This survey has been expired.', 404);
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

                if ($zcSurveyResponse <= 0 && $user->start_date <= $todayDate) {
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

                    return $this->invalidResponse($data, 'This survey has been expired.', 404);
                }
                return $this->invalidResponse($data, 'Survey already submitted.', 404);
            }
            return $this->invalidResponse($data, 'This survey has been expired.', 404);
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
