<?php

namespace App\Models;

use App\Models\Company;
use App\Models\Course;
use App\Models\CourseSurvey;
use App\Models\CourseSurveyQuestionOptions;
use App\Models\CourseSurveyQuestions;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property integer $course_id
 * @property string $type
 * @property string $title
 * @property boolean $status
 * @property string $created_at
 * @property string $updated_at
 */

class CourseSurveyQuestionAnswers extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'course_survey_question_answers';

    /**
     * @var array
     */
    protected $fillable = [
        'user_id',
        'company_id',
        'course_id',
        'survey_id',
        'question_id',
        'question_option_id',
        'created_at',
        'updated_at',
    ];

    /**
     * "BelongsTo" relation to `users` table
     * using `user_id` field.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * "BelongsTo" relation to `companies` table
     * using `company_id` field.
     *
     * @return BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * "BelongsTo" relation to `courses` table
     * using `course_id` field.
     *
     * @return BelongsTo
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    /**
     * "BelongsTo" relation to `courses` table
     * using `course_id` field.
     *
     * @return BelongsTo
     */
    public function survey(): BelongsTo
    {
        return $this->belongsTo(CourseSurvey::class, 'survey_id');
    }

    /**
     * "BelongsTo" relation to `course_survey_questions` table
     * using `question_id` field.
     *
     * @return BelongsTo
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(CourseSurveyQuestions::class, 'question_id');
    }

    /**
     * "BelongsTo" relation to `companies` table
     * using `company_id` field.
     *
     * @return BelongsTo
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(CourseSurveyQuestionOptions::class, 'question_option_id');
    }
}
