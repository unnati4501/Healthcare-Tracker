<?php
declare(strict_types = 1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Challenge;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

/**
 * Class Step
 *
 * @package App\Models
 */
class ChallengeWiseUserLogData extends Model
{
    /**
     * @var string
     */
    protected $table = 'challenge_wise_user_log';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'challenge_id',
        'user_id',
        'team_id',
        'company_id',
        'is_disqualified',
        'disqualified_at',
        'is_winner',
        'won_at',
        'finished_at',
        'start_remindered_at',
        'end_remindered_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'challenge_id' => 'integer',
        'user_id' => 'integer',
        'team_id' => 'integer',
        'company_id' => 'integer',
        'is_disqualified' => 'boolean',
        'is_winner' => 'boolean'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'disqualified_at',
        'won_at',
        'finished_at',
        'start_remindered_at',
        'end_remindered_at'
    ];

    /**
     * @return BelongsTo
     */
    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * @return BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
