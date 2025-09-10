<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\hasMany;

class ZcSurveyQuestion extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'zc_survey_questions';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'survey_id',
        'category_id',
        'sub_category_id',
        'question_id',
        'question_type_id',
        'order_priority',
        'created_at',
        'updated_at',
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
    protected $casts = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    /**
     * Custom builder instantiator. newEloquentBuilder is part
     * of Laravel.
     */
    public function newEloquentBuilder($query)
    {
        return new \App\Builders\BaseBuilder($query);
    }

    /**
     * @return BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo('App\Models\Company', 'company_id');
    }

    /**
     * @return BelongsTo
     */
    public function survey(): BelongsTo
    {
        return $this->belongsTo('App\Models\ZcSurvey', 'survey_id');
    }

    /**
     * @return BelongsTo
     */
    public function category()
    {
        return $this->belongsTo('App\Models\SurveyCategory', 'category_id');
    }

    /**
     * @return BelongsTo
     */
    public function subcategory()
    {
        return $this->belongsTo('App\Models\SurveySubCategory', 'sub_category_id');
    }

    /**
     * @return BelongsTo
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo('App\Models\ZcQuestion', 'question_id');
    }

    /**
     * @return BelongsTo
     */
    public function questiontype(): BelongsTo
    {
        return $this->belongsTo('App\Models\ZcQuestionType', 'question_type_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function questionoptions(): hasMany
    {
        return $this->hasMany('App\Models\ZcQuestionOption', 'question_id', 'question_id');
    }
}
