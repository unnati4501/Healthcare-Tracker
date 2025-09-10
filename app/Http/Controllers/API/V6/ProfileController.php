<?php
declare (strict_types = 1);

namespace App\Http\Controllers\API\V6;

use App\Http\Controllers\API\V1\ProfileController as v1ProfileController;
use App\Http\Resources\V1\SurveyListResource;
use App\Http\Resources\V6\UserProfileResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\HsSurvey;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends v1ProfileController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Get user profile details
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail(Request $request)
    {
        try {
            $data = new \stdClass();

            // get logged in user and retrieve profile
            $data->user = $user = $this->user();

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

                if (!empty($surveyHistoryBody)) {
                    $data->lastSubmittedSurvey = new SurveyListResource($surveyHistoryBody);
                }
            }

            return $this->successResponse(['data' => new UserProfileResource($data)], 'Profile details retrieved successfully.');
        } catch (\Exception $e) {
            if ($e instanceof AuthenticationException) {
                report($e);
                return $this->unauthorizedResponse(trans('labels.common_title.something_wrong'));
            }
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
