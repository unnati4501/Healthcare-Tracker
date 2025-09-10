<?php

namespace App\Models;

use App\Models\CourseSurveyQuestions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property integer $id
 * @property integer $question_id
 * @property string $choice
 * @property integer $score
 * @property string $created_at
 * @property string $updated_at
 */
class CourseSurveyQuestionOptions extends Model
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'course_survey_question_options';

    /**
     * @var fillable array
     */
    protected $fillable = [
        'question_id',
        'choice',
        'score',
    ];

    /**
     * 'BelongsTo' relation with 'course_survey_questions'
     * via 'question_id' column.
     *
     * @return BelongsTo
     */
    public function courseSurveyQuestions(): BelongsTo
    {
        return $this->belongsTo(CourseSurveyQuestions::class, 'question_id');
    }
}
