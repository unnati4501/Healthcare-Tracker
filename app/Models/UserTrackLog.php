<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTrackLog extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user_track_log';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'meditation_track_id',
        'user_id',
        'saved',
        'liked',
        'is_favourite'
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
        'saved' => 'boolean',
        'liked' => 'boolean',
        'is_favourite' => 'boolean'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [];
}
