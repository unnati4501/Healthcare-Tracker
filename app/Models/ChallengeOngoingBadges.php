<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChallengeOngoingBadges extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'challenge_ongoing_badges';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['challenge_id', 'challenge_target_id', 'badge_id', 'target', 'in_days'];

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
