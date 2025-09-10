<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V1;

use App\Http\Collections\V1\SurveyHistoryListCollection;
use App\Http\Collections\V1\SurveyQuestionListCollection;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\SurveyListResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\HsCategories;
use App\Models\HsQuestions;
use App\Models\HsSurvey;
use App\Models\HsSurveyResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HealthScoreController extends Controller
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Get survey question (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getQuestions(Request $request, HsSurvey $survey, HsCategories $hscategory)
    {
        try {
            $user           = $this->user();
            $usersurveyData = HsSurvey::where('user_id', $user->id)->where('id', $survey->id)->first();

            if (empty($usersurveyData)) {
                return $this->notFoundResponse("Requested data not found");
            }

            $surveyQuestion = HsQuestions::select('hs_questions.*', 'hs_question_type.name as questionType', 'hs_sub_categories.display_name as subCatName')
                ->join('hs_categories', 'hs_categories.id', '=', 'hs_questions.category_id')
                ->join('hs_sub_categories', 'hs_sub_categories.id', '=', 'hs_questions.sub_category_id')
                ->join('hs_question_type', 'hs_question_type.id', '=', 'hs_questions.question_type_id')
                ->where('hs_categories.id', $hscategory->id)
                ->where('hs_questions.status', true)
                ->get();

            if ($surveyQuestion->count() > 0) {
                // collect required data and return response
                return $this->successResponse(['data' => new SurveyQuestionListCollection($surveyQuestion)], 'Question listed successfully.');
            } else {
                // return empty response
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get current survey flag (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSurveyFlag(Request $request)
    {
        try {
            $user           = $this->user();
            $usersurveyData = HsSurvey::where('user_id', $user->id)->orderBy('id', 'DESC')->first();

            if ($user->hs_remind_survey) {
                return $this->notFoundResponse("Your wellbeing survey reminder has already been set.");
            }

            if (empty($usersurveyData)) {
                return $this->notFoundResponse("Requested data not found");
            } else {
                if (!empty($usersurveyData->survey_complete_time)) {
                    return $this->notFoundResponse("Your wellbeing survey has already been submitted.");
                } else {
                    $data                  = array();
                    $data['surveyId']      = $usersurveyData->id;
                    $data['physical']      = false;
                    $data['psychological'] = false;

                    if (!empty($usersurveyData->physical_survey_complete_time)) {
                        $data['physical'] = true;
                    }

                    if (!empty($usersurveyData->physcological_survey_complete_time)) {
                        $data['psychological'] = true;
                    }

                    return $this->successResponse(['data' => $data], 'Current survey retrieved successfully.');
                }
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get current survey flag (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLastSubmitedSurvey(Request $request)
    {
        try {
            $user           = $this->user();
            $usersurveyData = HsSurvey::where('user_id', $user->id)
                ->whereNotNull('survey_complete_time')
                ->orderBy('id', 'DESC')
                ->first();

            $headers = $request->headers->all();
            $payload = $request->all();

            if (!empty($usersurveyData)) {
                $version                                   = config('zevolifesettings.version.api_version');
                $surveyHistoryRequest                      = Request::create("api/" . $version . "/healthscore/report/" . $usersurveyData->id, 'GET', $headers, $payload);
                $surveyHistoryResponse                     = \Route::dispatch($surveyHistoryRequest);
                $surveyHistoryBody                         = json_decode($surveyHistoryResponse->getContent());
                $surveyHistoryBody->result->data->surveyId = $usersurveyData->id;
            }

            if (empty($surveyHistoryBody)) {
                return $this->successResponse([], "Requested data not found");
            } else {
                return $this->successResponse(['data' => new SurveyListResource($surveyHistoryBody)], 'Last submission retrieved successfully.');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get current survey flag (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubmitedSurveyHistory(Request $request)
    {
        try {
            $user           = $this->user();
            $usersurveyData = HsSurvey::where('user_id', $user->id)
                ->whereNotNull('survey_complete_time')
                ->orderBy('id', 'DESC')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            if ($usersurveyData->count() > 0) {
                // collect required data and return response
                return $this->successResponse(new SurveyHistoryListCollection($usersurveyData, true), 'History retrieved successfully.');
            } else {
                // return empty response
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Submit health score survey and store details in db.
     *
     * @param Request $request, HsSurvey $survey, HsCategories $hscategory
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitSurvey(Request $request, HsSurvey $survey, HsCategories $hscategory)
    {
        try {
            $payload        = $request->all();
            $user           = $this->user();
            $usersurveyData = HsSurvey::where('user_id', $user->id)->where('id', $survey->id)->first();

            $surveyData = $payload;

            if (empty($usersurveyData) || empty($surveyData)) {
                return $this->notFoundResponse("Requested data not found");
            }

            if ($hscategory->getKey() == 1) {
                if (!is_null($usersurveyData->physical_survey_complete_time)) {
                    return $this->notFoundResponse("Physical survey already submitted");
                }
            } else {
                if (!is_null($usersurveyData->physcological_survey_complete_time)) {
                    return $this->notFoundResponse("Physcological survey already submitted");
                }
            }

            $surveyReponseData = [];
            $score             = 0;
            $maxScore          = 0;

            $surveyReponseData = collect($surveyData)->map(function ($value, $key) use ($survey, $hscategory, &$score, &$maxScore) {
                $optionScore              = 0;
                $surveyQuestionData       = HsQuestions::where('id', $value['questionId'])->first();
                $surveyOptionsReponseData = collect($value['optionsData'])->map(function ($val, $k) use ($survey, $hscategory, $value, $surveyQuestionData, &$optionScore) {
                    $optionScore += $val['optionScore'] / count($value['optionsData']);
                    return [
                        'survey_id'       => $survey->getKey(),
                        'question_id'     => $value['questionId'],
                        'sub_category_id' => $surveyQuestionData->sub_category_id,
                        'category_id'     => $hscategory->getKey(),
                        'answer_value'    => $val['optionText'],
                        'score'           => $val['optionScore'],
                    ];
                });
                $score += $optionScore;
                $maxScore += $surveyQuestionData->max_score;
                return $surveyOptionsReponseData;
            });

            \DB::beginTransaction();

            $surveyReponseData = \Arr::collapse($surveyReponseData);
            $data              = HsSurveyResponse::insert($surveyReponseData);

            $categoryScore = !empty($maxScore) ? $score * 100 / $maxScore : 0.0;

            if ($categoryScore < 0) {
                $categoryScore = 0.0;
            }

            if ($data) {
                if ($hscategory->getKey() == 1) {
                    $usersurveyData->physical_survey_complete_time = \now()->toDateTimeString();
                    $usersurveyData->physical_survey_score         = $categoryScore;
                } else {
                    $usersurveyData->physcological_survey_complete_time = \now()->toDateTimeString();
                    $usersurveyData->physcological_survey_score         = $categoryScore;
                }
                if (!empty($usersurveyData->physcological_survey_complete_time) && !empty($usersurveyData->physical_survey_complete_time)) {
                    $usersurveyData->survey_complete_time = \now()->toDateTimeString();
                    $user->hs_show_banner                 = false;
                    $user->hs_remind_survey               = false;
                    $user->save();
                }
                $usersurveyData->save();

                \DB::commit();
                // collect required data and return response
                return $this->successResponse([], 'Survey submitted successfully.');
            } else {
                \DB::rollback();
                // return empty response
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get current survey flag (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function remindSurveyLater(Request $request)
    {
        try {
            \DB::beginTransaction();

            $user = $this->user();

            $user->hs_show_banner   = false;
            $user->hs_remind_survey = true;
            $user->hs_reminded_at   = now()->addHours(23);

            $user->save();

            // if ($user) {
            //     \dispatch(new SendHealthScoreReminder($user))->delay(now()->addHours(1));
            // }

            \DB::commit();

            // return empty response
            return $this->successResponse([], 'Reminder set successfully. We\'ll notify you after 24 Hours from now.');
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get health score report
     *
     * @param Request $request, HsSurvey $survey
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHealthScoreReport(Request $request, HsSurvey $survey)
    {
        try {
            $user = $this->user();

            $procedureData = [
                null,
                null,
                $survey->getKey(),
                null,
                null,
                null,
            ];

            $surveyData = \DB::select('CALL sp_health_score(?, ?, ?, ?, ?, ?)', $procedureData);

            $result                = [];
            $physicalScore         = 0;
            $physicalMaxScore      = 0;
            $psychologicalScore    = 0;
            $psychologicalMaxScore = 0;
            collect($surveyData)->each(function ($value, $key) use (&$result, &$physicalScore, &$physicalMaxScore, &$psychologicalScore, &$psychologicalMaxScore) {
                $subCategoryPercent = !empty($value->totalMaxScore) ? (float) number_format(($value->totalScore * 100 / $value->totalMaxScore), 1, '.', '') : 0.0;

                if ($subCategoryPercent < 0) {
                    $subCategoryPercent = 0.0;
                }

                if ($value->category_id == 1) {
                    $physicalScore += $value->totalScore;
                    $physicalMaxScore += $value->totalMaxScore;

                    $result['physical']['subcategories'][] = [
                        'subCategoryId'    => $value->sub_category_id,
                        'subCategoryName'  => $value->display_name,
                        'subCategoryScore' => $subCategoryPercent,
                    ];
                } else {
                    $psychologicalScore += $value->totalScore;
                    $psychologicalMaxScore += $value->totalMaxScore;

                    $result['psychological']['subcategories'][] = [
                        'subCategoryId'    => $value->sub_category_id,
                        'subCategoryName'  => $value->display_name,
                        'subCategoryScore' => $subCategoryPercent,
                    ];
                }
            });

            if ($physicalScore < 0) {
                $physicalScore = 0.0;
            }

            if ($psychologicalScore < 0) {
                $psychologicalScore = 0.0;
            }

            $result['physical']['score']         = !empty($physicalMaxScore) ? (float) number_format(($physicalScore * 100 / $physicalMaxScore), 1, '.', '') : 0.0;
            $result['physical']['totalScore']    = !empty($physicalScore) ? (float) number_format(($physicalScore), 1, '.', '') : 0.0;
            $result['physical']['totalMaxScore'] = !empty($physicalMaxScore) ? (float) number_format(($physicalMaxScore), 1, '.', '') : 0.0;
            $result['physical']['completed_at']  = Carbon::parse($survey->physical_survey_complete_time, config('app.timezone'))->setTimezone($user->timezone)->toAtomString();

            $result['psychological']['score']         = !empty($psychologicalMaxScore) ? (float) number_format(($psychologicalScore * 100 / $psychologicalMaxScore), 1, '.', '') : 0.0;
            $result['psychological']['totalScore']    = !empty($psychologicalScore) ? (float) number_format(($psychologicalScore), 1, '.', '') : 0.0;
            $result['psychological']['totalMaxScore'] = !empty($psychologicalMaxScore) ? (float) number_format(($psychologicalMaxScore), 1, '.', '') : 0.0;
            $result['psychological']['completed_at']  = Carbon::parse($survey->physcological_survey_complete_time, config('app.timezone'))->setTimezone($user->timezone)->toAtomString();

            if (count($result) > 0) {
                // collect required data and return response
                return $this->successResponse(['data' => $result], 'Survey report retrieved successfully.');
            } else {
                // return empty response
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        } catch (\Exception $e) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }
}
