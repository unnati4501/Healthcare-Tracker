<?php

namespace App\Models;

use App\Models\ZcSurveyResponse;
use App\Models\ZcSurveyReviewSuggestion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZcSurveyUserLog extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'zc_survey_user_log';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'survey_log_id',
        'retake',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'user_id'       => 'integer',
        'survey_log_id' => 'integer',
        'retake'        => 'integer',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['survey_submitted_at', 'created_at', 'updated_at'];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * @return BelongsTo
     */
    public function surveyLog(): BelongsTo
    {
        return $this->belongsTo('App\Models\ZcSurveyLog');
    }

    /**
     * To store response of survey
     *
     * @param $payload - Array of answers
     *
     * @return Bool
     */
    public function storeResponse(array $payload)
    {
        $responseArray      = [];
        $user_company_id    = $this->user->company->first()->id;
        $user_department_id = $this->user->department->first()->id;
        $aleadySubmitted    = false;
        // If already submitted survey then remove old survey score and answers
        if (!is_null($this->survey_submitted_at)) {
            ZcSurveyResponse::where('survey_log_id', $this->survey_log_id)->where('user_id', $this->user_id)->delete();
            $aleadySubmitted = true;
        }

        foreach ($payload['answers'] as $question_id => $answer) {
            $responseArray[] = [
                'user_id'         => $this->user_id,
                'company_id'      => $user_company_id,
                'department_id'   => $user_department_id,
                'survey_log_id'   => $this->survey_log_id,
                'category_id'     => (int) $payload['category'][$question_id],
                'sub_category_id' => (int) $payload['subcategory'][$question_id],
                'question_id'     => (int) $question_id,
                'option_id'       => (!empty($payload['option_id'][$question_id]) ? (int) $payload['option_id'][$question_id] : null),
                'score'           => (($payload['qtype'][$question_id] == 2) ? (int) $answer : null),
                'max_score'       => (!empty($payload['max_score'][$question_id]) ? (int) $payload['max_score'][$question_id] : null),
                'answer_value'    => (($payload['qtype'][$question_id] == 1) ? $answer : null),
                'created_at'      => \now(config('app.timezone'))->toDateTimeString(),
                'updated_at'      => \now(config('app.timezone'))->toDateTimeString(),
            ];
        }
        $stored = ZcSurveyResponse::insert($responseArray);
        if ($stored) {
            $this->survey_submitted_at = \now(config('app.timezone'))->toDateTimeString();
            if ($aleadySubmitted) {
                $this->retake = $this->retake + 1;
            }
            $this->save();
            return true;
        }
        return false;
    }

    /**
     * To store survey suggestion
     *
     * @param $payload - request data
     *
     * @return Bool
     */
    public function storeSurveyReview($payload)
    {
        $user_company_id    = $this->user->company->first()->id;
        $user_department_id = $this->user->department->first()->id;
        $stored             = ZcSurveyReviewSuggestion::create([
            'user_id'       => $this->user_id,
            'company_id'    => $user_company_id,
            'department_id' => $user_department_id,
            'survey_log_id' => $this->survey_log_id,
            'comment'       => $payload['survey_comments'],
        ]);
        if ($stored) {
            return true;
        }
        return false;
    }
}
