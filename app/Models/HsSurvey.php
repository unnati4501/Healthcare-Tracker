<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $company_id
 * @property integer $department_id
 * @property integer $team_id
 * @property integer $location_id
 * @property integer $user_id
 * @property string $title
 * @property string $rolled_out_to_user
 * @property string $physical_survey_complete_time
 * @property string $physcological_survey_complete_time
 * @property string $created_at
 * @property string $updated_at
 */
class HsSurvey extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hs_survey';

    /**
     * @var array
     */
    protected $fillable = [
        'company_id',
        'department_id',
        'team_id',
        'user_id',
        'title',
        'rolled_out_to_user',
        'physical_survey_complete_time',
        'physcological_survey_complete_time',
        'physical_survey_score',
        'physcological_survey_score',
        'survey_complete_time',
        'created_at',
        'updated_at',
        'physical_survey_started',
        'physcological_survey_started',
    ];
}
