<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WsUser extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'ws_user';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'language',
        'conferencing_mode',
        'video_link',
        'shift',
        'years_of_experience',
        'is_profile',
        'is_authenticate',
        'is_availability',
        'is_cronofy',
        'responsibilities',
    ];

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
    protected $dates = [
        'created_at',
        'updated_at',
    ];
}
