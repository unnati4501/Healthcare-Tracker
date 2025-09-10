<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalChallengeTask extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'personal_challenge_tasks';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'personal_challenge_id',
        'task_name',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * "belongs to" relation to `personal_challenges` table
     * via `personal_challenge_id` field.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function personalChallenge()
    {
        return $this->belongsTo(\App\Models\PersonalChallenge::class, 'personal_challenge_id');
    }

    /**
     * "has many" relation to `personal_challenge_user_tasks` table
     * via `personal_challenge_tasks_id` field.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function personalChallengeUserTasksViaPersonalChallengeTasksId()
    {
        return $this->hasMany(\App\Models\PersonalChallengeUserTask::class, 'personal_challenge_tasks_id');
    }
}
