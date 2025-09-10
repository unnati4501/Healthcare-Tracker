<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $question_id
 * @property float $score
 * @property string $choice
 * @property string $created_at
 * @property string $updated_at
 * @property HsQuestion $hsQuestion
 */
class HsQuestionsOption extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hs_questions_options';

    /**
     * @var array
     */
    protected $fillable = [
        'question_id',
        'score',
        'choice',
        'created_at',
        'updated_at',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function hsQuestion()
    {
        return $this->belongsTo('App\Models\HsQuestions', 'question_id');
    }
}
