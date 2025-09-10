<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V18;

use App\Http\Collections\V6\CompanyDepartmentsCollection;
use App\Http\Collections\V10\PortalSurveyCollection;
use App\Http\Controllers\API\V15\OnboardController as v15nboardController;
use App\Http\Requests\Api\V10\PortalSurveyRequest;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Company;
use App\Models\CompanyLocation;
use App\Models\Department;
use App\Models\ZcQuestion;
use App\Models\ZcSurveyLog;
use App\Models\ZcSurveyResponse;
use Carbon\Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OnboardController extends v15nboardController
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

                    return $this->successResponse([
                        'data' => [
                            'surveyId'         => $surveyLog->id,
                            'alreadySubmitted' => (isset($userSurveyLog) && !is_null($userSurveyLog->survey_submitted_at)) ,
                            'questions'        => (($surveyQuestions->count() > 0) ? new PortalSurveyCollection($surveyQuestions) : [])
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

    /**
     * Get department wise teams
     *
     * @param CompanyLocation $location
     * @param Department $department
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTemas(CompanyLocation $location, Department $department, Request $request)
    {
        try {
            $appTimezone     = config('app.timezone');
            $timezone        = ($request->header('X-User-Timezone') ?? $appTimezone);
            $now             = now($appTimezone);
            $company         = $department->company()->select('id', 'auto_team_creation', 'team_limit')->first();
            $chInvolvedTeams = [];
            $user            = Auth::guard('api')->user();
            $teamId          = (!empty($user) ? $user->teams()->select('teams.id')->first() : null);

            // get ongoing + upcoming challenge ids
            $challenge = $company->challenge()
                ->select('challenges.id', 'challenges.challenge_type')
                ->where('challenges.cancelled', false)
                ->where('challenges.finished', false)
                ->whereNotIn('challenges.challenge_type', ['inter_company', 'individual'])
                ->where(function ($query) use ($now, $appTimezone, $timezone) {
                    $query
                        ->whereRaw("CONVERT_TZ(challenges.start_date, '{$appTimezone}', '{$timezone}')  <= '{$now}' AND CONVERT_TZ(challenges.end_date, '{$appTimezone}', '{$timezone}') >= '{$now}'")
                        ->orWhereRaw("CONVERT_TZ(challenges.start_date, '{$appTimezone}', '{$timezone}')  >= '{$now}' AND CONVERT_TZ(challenges.end_date, '{$appTimezone}', '{$timezone}') >= '{$now}'");
                })
                ->get();

            // get ongoing + upcoming inter_company challenge ids
            $icChallenge = $company->icChallenge()
                ->select('challenges.id', 'challenges.challenge_type')
                ->where('challenges.cancelled', false)
                ->where('challenges.finished', false)
                ->where(function ($query) use ($now, $appTimezone, $timezone) {
                    $query
                        ->whereRaw("CONVERT_TZ(challenges.start_date, '{$appTimezone}', '{$timezone}')  <= '{$now}' AND CONVERT_TZ(challenges.end_date, '{$appTimezone}', '{$timezone}') >= '{$now}'")
                        ->orWhereRaw("CONVERT_TZ(challenges.start_date, '{$appTimezone}', '{$timezone}')  >= '{$now}' AND CONVERT_TZ(challenges.end_date, '{$appTimezone}', '{$timezone}') >= '{$now}'");
                })
                ->get();
            $challenge = $challenge->merge($icChallenge);

            // get involved team ids
            if (!empty($challenge)) {
                foreach ($challenge as $key => $challenge) {
                    $chTeams = $challenge->memberTeams()
                        ->select('teams.id')
                        ->where('teams.default', false)
                        ->where('teams.company_id', $company->id)
                        ->get()->pluck('', 'id')->toArray();
                    $chInvolvedTeams = ($chInvolvedTeams + $chTeams);
                }
                $chInvolvedTeams = array_keys($chInvolvedTeams);
            }

            // get teams list
            $teams = $department->teams()
                ->select('teams.id', 'teams.name', 'teams.default')
                ->whereHas('teamlocation', function ($query) use ($location) {
                    $query->where('company_locations.id', $location->id);
                })
                ->when($company->auto_team_creation, function ($query, $value) use ($company, $teamId) {
                    $query
                        ->withCount('users')
                        ->having('users_count', '<', $company->team_limit, 'or')
                        ->having('teams.default', '=', true, 'or');
                    if (!empty($teamId)) {
                        $query->having('teams.id', '=', $teamId->id, 'or');
                    }
                })
                ->where(function ($query) use ($chInvolvedTeams, $teamId) {
                    $query->whereNotIn('teams.id', $chInvolvedTeams);
                    if (!empty($teamId)) {
                        $query->orWhere('teams.id', '=', $teamId->id);
                    }
                })
                ->orderBy('teams.name')
                ->get();

            return $this->successResponse(
                ($teams->count() > 0) ? new CompanyDepartmentsCollection($teams) : ['data' => []],
                ($teams->count() > 0) ? 'Teams list retrieved successfully.' : 'No results'
            );
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
