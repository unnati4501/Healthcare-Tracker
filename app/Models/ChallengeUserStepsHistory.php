<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChallengeUserStepsHistory extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'challenge_user_steps_history';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'challenge_id',
        'user_id',
        'tracker',
        'steps',
        'distance',
        'calories',
        'points',
        'log_date'
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
    protected $casts = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['log_date'];

    /**
     * @return BelongsTo
     */
    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }
}
