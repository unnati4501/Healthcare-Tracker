<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCourseWeek extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user_course_week';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'course_id',
        'course_week_id',
        'user_id',
        'status',
        'completed_at'
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
    protected $casts = ['status' => 'string'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['completed_at'];
}
