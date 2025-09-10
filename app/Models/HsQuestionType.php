<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $name
 * @property string $display_name
 * @property boolean $status
 * @property string $created_at
 * @property string $updated_at
 * @property HsQuestion[] $hsQuestions
 */
class HsQuestionType extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hs_question_type';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'display_name',
        'status',
        'created_at',
        'updated_at',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hsQuestions()
    {
        return $this->hasMany('App\Models\HsQuestions', 'question_type_id');
    }
}
