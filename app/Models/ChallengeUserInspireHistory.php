<?php
declare (strict_types = 1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Step
 *
 * @package App\Models
 */
class ChallengeUserInspireHistory extends Model
{
    /**
     * @var string
     */
    protected $table = 'challenge_user_inspire_history';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'challenge_id',
        'meditation_track_id',
        'user_id',
        'log_date',
        'duration_listened',
        'points',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'challenge_id'        => 'integer',
        'meditation_track_id' => 'integer',
        'user_id'             => 'integer',
        'duration_listened'   => 'integer',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'log_date',
    ];

    /**
     * @return BelongsTo
     */
    public function challenge(): BelongsTo
    {
        return $this->belongsTo('App\Models\Challenge');
    }
}
