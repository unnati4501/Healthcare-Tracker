<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V8;

use App\Http\Controllers\API\V1\HealthScoreController as v1HealthScoreController;
use App\Http\Requests\Api\V8\GetHealthscoreStatisticsRequest;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\HsSurvey;
use App\Models\HsCategories;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HealthScoreController extends v1HealthScoreController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    public function getHealthscoreStatistics(GetHealthscoreStatisticsRequest $request)
    {
        try {
            // logged-in user
            $user     = $this->user();
            $monthArr = getMonths($request->year);
            // app timezone and user timezone
            $appTimezone      = config('app.timezone');
            $timezone         = $user->timezone ?? $appTimezone;
            $userCreatedMonth = Carbon::parse($user->created_at, config('app.timezone'))->setTimezone($timezone)->format("m");
            $userCreatedYear  = Carbon::parse($user->created_at, config('app.timezone'))->setTimezone($timezone)->format("Y");
            $minDate          = HsSurvey::where('user_id', $user->id)
                ->whereNotNull("survey_complete_time")
                ->orderBy("id", "ASC")
                ->first();

            $maxDate          = HsSurvey::where('user_id', $user->id)
                ->whereNotNull("survey_complete_time")
                ->orderBy("id", "DESC")
                ->first();
            $data           = [];
            $monthWiesValue = 0;
            if ($userCreatedYear <= $request->year && !empty($minDate)) {
                $monthArr = getMonths($request->year);
                foreach ($monthArr as $month => $monthName) {
                    if ((date("Y-m", strtotime($userCreatedYear . "-" . $userCreatedMonth)) <= date("Y-m", strtotime($request->year . "-" . $month))) && (Carbon::parse($minDate->survey_complete_time, config('app.timezone'))->setTimezone($timezone)->format("Y-m") <= date("Y-m", strtotime($request->year . "-" . $month))) && (Carbon::parse($maxDate->survey_complete_time, config('app.timezone'))->setTimezone($timezone)->format("Y-m") >= date("Y-m", strtotime($request->year . "-" . $month)))) {
                        $usersurveyData = HsSurvey::where('user_id', $user->id)
                                            ->where(\DB::raw("MONTH(CONVERT_TZ(survey_complete_time, '{$appTimezone}', '{$user->timezone}'))"), '=', $month)
                                            ->where(\DB::raw("YEAR(CONVERT_TZ(survey_complete_time, '{$appTimezone}', '{$user->timezone}'))"), '=', $request->year)
                                            ->orderBy("id", "DESC")
                                            ->first();

                        if (!empty($usersurveyData)) {
                            $headers = $request->headers->all();
                            $payload = [];

                            $version                                   = config('zevolifesettings.version.api_version');
                            $surveyHistoryRequest                      = Request::create("api/" . $version . "/healthscore/report/" . $usersurveyData->id, 'GET', $headers, $payload);
                            $surveyHistoryResponse                     = \Route::dispatch($surveyHistoryRequest);
                            $surveyHistoryBody                         = json_decode($surveyHistoryResponse->getContent());
                            $surveyHistoryBody->result->data->surveyId = $usersurveyData->id;

                            if (!empty($surveyHistoryBody->result)) {
                                $survey   = $surveyHistoryBody->result->data;
                                $totalScore    = $survey->physical->totalScore + $survey->psychological->totalScore;
                                $totalMaxScore = $survey->physical->totalMaxScore + $survey->psychological->totalMaxScore;
                                $monthWiesValue = $totalMaxScore > 0 ? (float) number_format(($totalScore * 100) / $totalMaxScore, 1, '.', '') : 0.0;
                            }
                        }

                        $monthData          = [];
                        $monthData['key']   = ucfirst($monthName);
                        $monthData['value'] = (float) $monthWiesValue;

                        array_push($data, $monthData);
                    }
                }
            }

            $resultData = [
                'data'        => $data,
                'minimumDate' => (!empty($minDate)) ? Carbon::parse($minDate->survey_complete_time, config('app.timezone'))->setTimezone($timezone)->toDateString() : null,
            ];

            return $this->successResponse($resultData, 'Healthscore statistics retrived successfully.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    public function getCategoriesHealthscoreStatistics(GetHealthscoreStatisticsRequest $request)
    {
        try {
            // logged-in user
            $user     = $this->user();
            $monthArr = getMonths($request->year);
            // app timezone and user timezone
            $appTimezone      = config('app.timezone');
            $timezone         = $user->timezone ?? $appTimezone;
            $userCreatedMonth = Carbon::parse($user->created_at, config('app.timezone'))->setTimezone($timezone)->format("m");
            $userCreatedYear  = Carbon::parse($user->created_at, config('app.timezone'))->setTimezone($timezone)->format("Y");

            $minDate = HsSurvey::where('user_id', $user->id)
                ->whereNotNull("survey_complete_time")
                ->orderBy("id", "ASC")
                ->first();

            $maxDate = HsSurvey::where('user_id', $user->id)
                ->whereNotNull("survey_complete_time")
                ->orderBy("id", "DESC")
                ->first();

            $data               = [];
            $physicalValue      = 0;
            $psychologicalValue = 0;
            if ($userCreatedYear <= $request->year && !empty($minDate)) {
                $monthArr = getMonths($request->year);
                foreach ($monthArr as $month => $monthName) {
                    if ((date("Y-m", strtotime($userCreatedYear . "-" . $userCreatedMonth)) <= date("Y-m", strtotime($request->year . "-" . $month))) && (Carbon::parse($minDate->survey_complete_time, config('app.timezone'))->setTimezone($timezone)->format("Y-m") <= date("Y-m", strtotime($request->year . "-" . $month))) && (Carbon::parse($maxDate->survey_complete_time, config('app.timezone'))->setTimezone($timezone)->format("Y-m") >= date("Y-m", strtotime($request->year . "-" . $month)))) {
                        $usersurveyData = HsSurvey::where('user_id', $user->id)
                            ->where(\DB::raw("MONTH(CONVERT_TZ(survey_complete_time, '{$appTimezone}', '{$user->timezone}'))"), '=', $month)
                            ->where(\DB::raw("YEAR(CONVERT_TZ(survey_complete_time, '{$appTimezone}', '{$user->timezone}'))"), '=', $request->year)
                            ->orderBy("id", "DESC")
                            ->first();

                        if (!empty($usersurveyData)) {
                            $physicalValue      = (float) number_format($usersurveyData->physical_survey_score, 1, '.', '');
                            $psychologicalValue = (float) number_format($usersurveyData->physcological_survey_score, 1, '.', '');
                        }

                        $monthData                  = [];
                        $monthData['key']           = ucfirst($monthName);
                        $monthData['physical']      = (float) $physicalValue;
                        $monthData['psychological'] = (float) $psychologicalValue;

                        array_push($data, $monthData);
                    }
                }
            }

            $resultData = [
                'data'        => $data,
                'minimumDate' => (!empty($minDate)) ? Carbon::parse($minDate->survey_complete_time, config('app.timezone'))->setTimezone($timezone)->toDateString() : null,
            ];

            return $this->successResponse($resultData, 'Categories Healthscore statistics retrived successfully.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    public function changeSurveyFlag(Request $request, HsSurvey $survey, HsCategories $hscategory)
    {
        try {
            // logged-in user
            $user     = $this->user();
            $usersurveyData = HsSurvey::where('user_id', $user->id)->where('id', $survey->id)->first();

            if (empty($usersurveyData)) {
                return $this->notFoundResponse("Requested data not found");
            }

            \DB::beginTransaction();

            if ($hscategory->getKey() == 1) {
                $usersurveyData->physical_survey_started = 1;
            } else {
                $usersurveyData->physcological_survey_started = 1;
            }
            $usersurveyData->save();

            \DB::commit();

            return $this->successResponse([], 'Survey status updated successfully.');
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
