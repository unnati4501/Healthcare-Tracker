<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChallengeTarget extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'challenge_targets';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'short_name', 'is_excluded'];

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
    protected $casts = ['is_excluded' => 'boolean'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [];
}
