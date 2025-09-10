<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZcSurveyReviewSuggestionLog extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'zc_survey_review_suggestion_log';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'suggestion_id',
        'is_favorite',
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
        'company_id'    => 'integer',
        'suggestion_id' => 'integer',
        'is_favorite'   => 'bool',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];
}
