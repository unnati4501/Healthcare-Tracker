<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMeditationTrack extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user_meditation_track_logs';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'meditation_track_id',
        'user_id',
        'saved',
        'saved_at',
        'liked',
        'favourited',
        'favourited_at',
        'view_count'
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
    protected $casts = ['saved' => 'boolean', 'liked' => 'boolean', 'favourited' => 'boolean'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [];
}
