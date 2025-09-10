<?php

namespace App\Models;

use App\Models\CourseSurveyQuestionOptions;
use App\Models\CourseSurveyQuestions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property integer $course_id
 * @property string $type
 * @property string $title
 * @property boolean $status
 * @property string $created_at
 * @property string $updated_at
 */

class CourseSurvey extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'course_survey';

    /**
     * @var array
     */
    protected $fillable = [
        'course_id',
        'type',
        'title',
        'status',
        'created_at',
        'updated_at',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function surveyCourse()
    {
        return $this->belongsTo('App\Models\Course', 'course_id');
    }

    /**
     * @return HasMany
     */
    public function surveyQuestions(): HasMany
    {
        return $this->hasMany('App\Models\CourseSurveyQuestions', 'survey_id');
    }

    public function updateSurvey($payload)
    {
        $this->title = $payload['title'];
        $this->save();

        $deleted_question_ids = [];
        $deleted_option_ids   = [];

        if (!empty($payload['deleted_questions'])) {
            $deleted_question_ids = explode(',', $payload['deleted_questions']);
            CourseSurveyQuestions::destroy($deleted_question_ids);
        }

        if (!empty($payload['deleted_options'])) {
            $deleted_option_ids = explode(',', $payload['deleted_options']);
            CourseSurveyQuestionOptions::whereIn('id', $deleted_option_ids)->delete();
        }

        $courseDetails = $this->surveyCourse()->first();

        foreach ($payload['question'] as $key => $question) {
            if ($courseDetails->status) {
                $addd_question = $this->surveyQuestions()->updateOrCreate([
                    'survey_id' => $this->getKey(),
                    'id'        => $key,
                ], [
                    'survey_id' => $this->getKey(),
                    'title'     => $question,
                    'status'    => 1,
                ]);
            } else {
                $addd_question = $this->surveyQuestions()->updateOrCreate([
                    'survey_id' => $this->getKey(),
                    'id'        => $key,
                ], [
                    'survey_id' => $this->getKey(),
                    'title'     => $question,
                    'type'      => $payload['type'][$key],
                    'status'    => 1,
                ]);
            }

            if ($addd_question) {
                if (isset($payload['logo'][$key]) && !empty($payload['logo'][$key])) {
                    $name = $addd_question->id . '_' . \time();
                    $addd_question
                        ->clearMediaCollection('logo')
                        ->addMedia($payload['logo'][$key])
                        ->usingName($payload['logo'][$key]->getClientOriginalName())
                        ->usingFileName($name . '.' . $payload['logo'][$key]->extension())
                        ->toMediaCollection('logo', config('medialibrary.disk_name'));
                }

                foreach ($payload['option'][$key] as $op_key => $option) {
                    if ($courseDetails->status) {
                        $addd_question->courseSurveyOptions()->updateOrCreate([
                            'question_id' => $addd_question->getKey(),
                            'id'          => $op_key,
                        ], [
                            'question_id' => $addd_question->getKey(),
                            'choice'      => $option,
                        ]);
                    } else {
                        $addd_question->courseSurveyOptions()->updateOrCreate([
                            'question_id' => $addd_question->getKey(),
                            'id'          => $op_key,
                        ], [
                            'question_id' => $addd_question->getKey(),
                            'choice'      => $option,
                            'score'       => $payload['score'][$key][$op_key],
                        ]);
                    }
                }
            }
        }

        return true;
    }
}
