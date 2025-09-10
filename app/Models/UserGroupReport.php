<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserGroupReport extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user_group_reports';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'group_id', 'reason', 'message'];

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
    protected $dates = [];
}
