<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCourse extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user_course';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'course_id',
        'user_id',
        'saved',
        'saved_at',
        'liked',
        'ratings',
        'review',
        'joined',
        'post_survey_completed',
        'post_survey_completed_on',
        'pre_survey_completed',
        'pre_survey_completed_on',
        'completed',
        'completed_on',
        'started_course'
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
    protected $casts = ['saved' => 'boolean', 'liked' => 'boolean', 'joined' => 'boolean'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['saved_at'];
}
