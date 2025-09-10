<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleUsers extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'session_group_users';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'session_id',
        'user_id',
        'is_cancelled',
        'cancelled_at',
        'cancelled_reason',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];
}
