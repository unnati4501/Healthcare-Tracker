<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V20;

use App\Http\Collections\V10\PortalSurveyCollection;
use App\Http\Controllers\API\V18\OnboardController as v18nboardController;
use App\Http\Requests\Api\V15\SubmitSurveyFeedbackRequest;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Company;
use App\Models\ZcQuestion;
use App\Models\ZcSurveyLog;
use App\Models\ZcSurveyReviewSuggestion;
use App\Models\ZcSurveyUserLog;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnboardController extends v18nboardController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Retrieve survey by survey log id
     *
     * @param ZcSurveyLog $surveyLog
     * @return \Illuminate\Http\JsonResponse
     */
    public function survey(? ZcSurveyLog $surveyLog, Request $request)
    {
        try {
            // check if surveyLog is not passed then show error
            if (is_null($surveyLog->getKey())) {
                return $this->invalidResponse([
                    'surveyLogId' => [
                        'The survey log id field is required.',
                    ],
                ], 'The given data is invalid.', 422);
            }

            $user    = $this->user();
            $company = $user->company()
                ->select('companies.id', 'companies.subscription_start_date', 'companies.subscription_end_date')
                ->first();

            // check company id of survey and logged in users both are same
            if ($surveyLog->company_id != $company->id) {
                return $this->notFoundResponse('It seems survey is not available for your company.');
            }

            $timezone                = (!empty($user->timezone) ? $user->timezone : config('app.timezone'));
            $now                     = now($timezone)->toDateTimeString();
            $subscription_start_date = Carbon::parse($company->subscription_start_date, config('app.timezone'))
                ->setTimezone($timezone)->toDateTimeString();
            $subscription_end_date = Carbon::parse($company->subscription_end_date, config('app.timezone'))
                ->setTimezone($timezone)->toDateTimeString();

            // check company subscription is active or not
            if ($now > $subscription_start_date && $now < $subscription_end_date) {
                $surveyExpireDate = Carbon::parse($surveyLog->expire_date, config('app.timezone'))
                    ->setTimezone($timezone)->toDateTimeString();
                // check survey is active or not
                if ($surveyExpireDate > $now) {
                    $userSurveyLog = $surveyLog->surveyUserLogs()->where('user_id', $user->id)->first();

                    // check if survey_to_all is set to false and user isn't present in survey user logs then prevent user to avail the survey as user isn't selected in survey config
                    if (!$surveyLog->survey_to_all && is_null($userSurveyLog)) {
                        return $this->notFoundResponse('It seems survey is not available for you.');
                    }

                    // check user already submitted survey or not
                    // if (isset($userSurveyLog) && !is_null($userSurveyLog->survey_submitted_at)) {
                    //     return $this->notFoundResponse('Survey already submitted.');
                    // }

                    $surveyQuestions = ZcQuestion::leftJoin('zc_survey_questions', 'zc_questions.id', '=', 'zc_survey_questions.question_id')
                        ->Join('zc_question_types', 'zc_question_types.id', '=', 'zc_survey_questions.question_type_id')
                        ->where('zc_survey_questions.survey_id', $surveyLog->survey_id)
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

                    $userSurveyCount = ZcSurveyUserLog::where('user_id', $user->id)->count();

                    return $this->successResponse([
                        'data' => [
                            'surveyId'          => $surveyLog->id,
                            'alreadySubmitted'  => (isset($userSurveyLog) && !is_null($userSurveyLog->survey_submitted_at)) ,
                            'questions'         => (($surveyQuestions->count() > 0) ? new PortalSurveyCollection($surveyQuestions) : []),
                            'feedbackAvailable' => ($userSurveyCount > 1)
                        ],
                    ], 'Survey retrieved successfully.');
                } else {
                    return $this->notFoundResponse('This survey has expired.');
                }
            } else {
                return $this->notFoundResponse('Your company subscription has expired. Please contact your admin or the Zevo Account Manager');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Submit Survey Feedback Response
     *
     * @param SubmitSurveyFeedbackRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitSurveyFeedback(SubmitSurveyFeedbackRequest $request)
    {
        try {
            \DB::beginTransaction();
            $user         = $this->user();
            $company      = $user->company->first();
            $departmentId = $company->departments->first()->id;

            $zcSurveyLog = ZcSurveyLog::select('id')
                ->where('id', $request->surveyId)
                ->where('company_id', $company->id)
                ->orderBy('id', 'DESC')
                ->first();

            if (!$zcSurveyLog) {
                return $this->notFoundResponse('It seems survey is not available for your company.');
            }

            // $checkAlreadySubmitted = ZcSurveyReviewSuggestion::select('id')
            //     ->where('survey_log_id', $zcSurveyLog->id)
            //     ->where('company_id', $company->id)
            //     ->where('user_id', $user->id)
            //     ->first();

            // if ($checkAlreadySubmitted) {
            //     return $this->notFoundResponse('Survey feedback already submitted.');
            // }

            $stored = ZcSurveyReviewSuggestion::create([
                'user_id'       => $user->id,
                'company_id'    => $company->id,
                'department_id' => $departmentId,
                'survey_log_id' => $zcSurveyLog->id,
                'comment'       => $request->feedback,
            ]);

            if ($stored) {
                \DB::commit();
                return $this->successResponse(['data' => []], 'Feedback has been submitted successfully!');
            }
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
