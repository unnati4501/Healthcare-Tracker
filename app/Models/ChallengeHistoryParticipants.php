<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChallengeHistoryParticipants extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'freezed_challenge_participents';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'challenge_id',
        'user_id',
        'team_id',
        'company_id',
        'participant_name',
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
    protected $dates = [];

    /**
     * @return BelongsTo
     */
    public function challenge(): BelongsTo
    {
        return $this->belongsTo('App\Models\Challenge');
    }
}
