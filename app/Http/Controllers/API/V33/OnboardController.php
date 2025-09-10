<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V33;

use App\Http\Controllers\API\V32\OnboardController as v32OnboardController;
use App\Http\Requests\Api\V10\PortalSurveyRequest;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Company;
use App\Models\Department;
use App\Models\ZcQuestion;
use App\Models\ZcSurveyLog;
use App\Models\ZcSurveyResponse;
use Carbon\Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnboardController extends v32OnboardController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;
    /**
     * Submit onboarding survey by survey log id
     *
     * @param PortalSurveyRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitSurvey(PortalSurveyRequest $request)
    {
        try {
            $appTimezone = config('app.timezone');
            $surveyId    = $request->input('surveyId');
            $response    = $request->input('response');
            $surveyLog   = ZcSurveyLog::find($surveyId);
            $user        = $this->user();
            $company     = $user->company()
                ->select('companies.id', 'companies.subscription_start_date', 'companies.subscription_end_date')
                ->first();
            $department = $user->department()->select('departments.id')->first();

            // check company id of survey and logged in users both are same
            if ($surveyLog->company_id != $company->id) {
                return $this->notFoundResponse('It seems survey is not available for your company.');
            }

            $timezone                = (!empty($user->timezone) ? $user->timezone : $appTimezone);
            $now                     = now($timezone)->toDateTimeString();
            $subscription_start_date = Carbon::parse($company->subscription_start_date, $appTimezone)
                ->setTimezone($timezone)->toDateTimeString();
            $subscription_end_date = Carbon::parse($company->subscription_end_date, $appTimezone)
                ->setTimezone($timezone)->toDateTimeString();

            // check company subscription is active or not
            if ($now > $subscription_start_date && $now < $subscription_end_date) {
                $surveyExpireDate = Carbon::parse($surveyLog->expire_date, $appTimezone)
                    ->setTimezone($timezone)->toDateTimeString();
                // check survey is active or not
                if ($surveyExpireDate > $now) {
                    $userSurveyLog = $surveyLog->surveyUserLogs()->where('user_id', $user->id)->first();

                    // check if survey_to_all is set to false and user isn't present in survey user logs then prevent user to avail the survey as user isn't selected in survey config
                    if (!$surveyLog->survey_to_all && is_null($userSurveyLog)) {
                        return $this->notFoundResponse('It seems survey is not available for you.');
                    }

                    $alreadySubmitted = (isset($userSurveyLog) && !is_null($userSurveyLog->survey_submitted_at)) ;

                    // If already submitted survey then remove old survey score and answers
                    if (isset($userSurveyLog) && !is_null($userSurveyLog->survey_submitted_at)) {
                        ZcSurveyResponse::where('survey_log_id', $surveyLog->id)->where('user_id', $user->id)->delete();
                    }

                    \DB::beginTransaction();
                    // Submitted survey response data
                    $responseArray = [];
                    foreach ($response as $value) {
                        $surveyQuestion = ZcQuestion::select('id', 'category_id', 'sub_category_id', 'question_type_id')
                            ->find($value['questionId']);
                        $maxScore  = $surveyQuestion->questionoptions()->max('score');
                        $option_id = null;
                        if ($surveyQuestion->question_type_id == 2) {
                            $option = $surveyQuestion->questionoptions()
                                ->select('zc_questions_options.id')
                                ->where('score', $value['score'])
                                ->where('choice', $value['answer'])
                                ->first();
                            $option_id = (!empty($option->id) ? (int) $option->id : null);
                        }
                        $responseArray[] = [
                            'user_id'         => $user->id,
                            'company_id'      => $company->id,
                            'department_id'   => $department->id,
                            'survey_log_id'   => $surveyLog->id,
                            'category_id'     => (int) $surveyQuestion->category_id,
                            'sub_category_id' => (int) $surveyQuestion->sub_category_id,
                            'question_id'     => (int) $value['questionId'],
                            'option_id'       => $option_id,
                            'score'           => (($surveyQuestion->question_type_id == 2) ? (int) $value['score'] : null),
                            'max_score'       => (!empty($maxScore) ? (int) $maxScore : null),
                            'answer_value'    => (($surveyQuestion->question_type_id == 1) ? $value['answer'] : null),
                            'created_at'      => \now(config('app.timezone'))->toDateTimeString(),
                            'updated_at'      => \now(config('app.timezone'))->toDateTimeString(),
                        ];
                    }

                    $stored = ZcSurveyResponse::insert($responseArray);
                    if ($stored) {
                        if (is_null($userSurveyLog)) {
                            $surveyLog->surveyUserLogs()->insert([
                                'user_id'             => $user->id,
                                'survey_log_id'       => $surveyLog->id,
                                'survey_submitted_at' => $now,
                            ]);
                        } else {
                            $userSurveyLog->survey_submitted_at = $now;
                            if ($alreadySubmitted) {
                                $userSurveyLog->retake = $userSurveyLog->retake + 1;
                            }
                            $userSurveyLog->save();
                        }

                        if (!is_null($company)) {
                            $surveyLog->rewardPortalPointsToUser($user, $company, 'audit_survey', [
                                'survey_id' => $surveyLog->survey_id,
                            ]);
                        }

                        UpdatePointContentActivities('wellbeing_score', $surveyLog->id, $user->id, 'completing_wellbeing_survey');

                        \DB::commit();
                        return $this->successResponse(['data' => []], 'Survey has been submitted successfully!');
                    } else {
                        \DB::rollback();
                        $this->internalErrorResponse("Something went wrong, Please try again!");
                    }
                } else {
                    \DB::rollback();
                    return $this->notFoundResponse('This survey has expired.');
                }
            } else {
                \DB::rollback();
                return $this->notFoundResponse('Your company subscription has expired. Please contact your admin or the Zevo Account Manager');
            }
        } catch (\Exception $e) {
            report($e);
            \DB::rollback();
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
