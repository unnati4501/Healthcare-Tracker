<?php

namespace App\Http\Resources\V1;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class SurveyListResource extends JsonResource
{
    use ProvidesAuthGuardTrait;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if (isset($this->result)) {
            $survey   = $this->result->data;
            $physical = [
                'submitted_at' => $survey->physical->completed_at,
                'score'        => $survey->physical->score,
            ];

            $psychological = [
                'submitted_at' => $survey->psychological->completed_at,
                'score'        => $survey->psychological->score,
            ];

            $totalScore    = $survey->physical->totalScore + $survey->psychological->totalScore;
            $totalMaxScore = $survey->physical->totalMaxScore + $survey->psychological->totalMaxScore;

            $totalScorePercent = $totalMaxScore > 0 ? (float) number_format(($totalScore * 100) / $totalMaxScore, 1, '.', '') : 0.0;

            return [
                'surveyId'      => $survey->surveyId,
                'physical'      => $physical,
                'psychological' => $psychological,
                'totalScore'    => $totalScorePercent,
            ];
        } else {
            $headers = $request->headers->all();
            $payload = $request->all();

            $surveyId                                  = $this->id;
            $version                                   = config('zevolifesettings.version.api_version');
            $surveyHistoryRequest                      = \Request::create("api/" . $version . "/healthscore/report/" . $surveyId, 'GET', $headers, $payload);
            $surveyHistoryResponse                     = \Route::dispatch($surveyHistoryRequest);
            $surveyHistoryBody                         = json_decode($surveyHistoryResponse->getContent());
            $surveyHistoryBody->result->data->surveyId = $surveyId;

            return new self($surveyHistoryBody);
        }
    }
}
