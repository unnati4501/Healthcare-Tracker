<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $survey_id
 * @property integer $question_id
 * @property integer $sub_category_id
 * @property integer $category_id
 * @property string $answer_value
 * @property float $score
 * @property string $created_at
 * @property string $updated_at
 */
class HsSurveyResponse extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hs_survey_responses';

    /**
     * @var array
     */
    protected $fillable = [
        'survey_id',
        'question_id',
        'sub_category_id',
        'category_id',
        'answer_value',
        'score',
        'created_at',
        'updated_at',
    ];
}
