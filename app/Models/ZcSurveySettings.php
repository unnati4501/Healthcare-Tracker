<?php

namespace App\Models;

use App\Models\Company;
use App\Models\ZcSurvey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZcSurveySettings extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'zc_survey_settings';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'survey_id',
        'survey_frequency',
        'survey_roll_out_day',
        'survey_roll_out_time',
        'is_premium',
        'survey_to_all',
        'team_ids',
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
        'is_premium' => 'boolean',
        'team_ids'   => 'object',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [];

    /**
     * "BelongsTo" relation to `companies` table
     * via `company_id` field.
     *
     * @return BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * "BelongsTo" relation to `zc_survey` table
     * via `survey_id` field.
     *
     * @return BelongsTo
     */
    public function survey(): BelongsTo
    {
        return $this->belongsTo(ZcSurvey::class, 'survey_id');
    }
}
