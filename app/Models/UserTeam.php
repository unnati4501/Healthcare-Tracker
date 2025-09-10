<?php

namespace App\Models;

use App\Models\Company;
use App\Models\Department;
use App\Models\Team;
use App\Models\User;
use App\Observers\UserTeamObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UserTeam extends Pivot
{
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user_team';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'team_id',
        'department_id',
        'company_id',
    ];

    /**
     * Boot model
     */
    protected static function boot()
    {
        parent::boot();
        static::observe(UserTeamObserver::class);
    }

    /**
     * "BelongsTo" relation to `users` table
     * via `creator_id` field.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * "BelongsTo" relation to `teams` table
     * via `team_id` field.
     *
     * @return BelongsTo
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    /**
     * "BelongsTo" relation to `departments` table
     * via `department_id` field.
     *
     * @return BelongsTo
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * "BelongsTo" relation to `companies` table
     * via `company_id` field.
     *
     * @return BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
