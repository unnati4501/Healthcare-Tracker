<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalChallengeUserTask extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'personal_challenge_user_tasks';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'personal_challenge_id',
        'personal_challenge_user_id',
        'personal_challenge_tasks_id',
        'date',
        'set_time',
        'completed',
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
        'date',
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
     * "belongs to" relation to `personal_challenge_tasks` table
     * via `personal_challenge_tasks_id` field.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function personalChallengeTasks()
    {
        return $this->belongsTo(\App\Models\PersonalChallengeTask::class, 'personal_challenge_tasks_id');
    }

    /**
     * "belongs to" relation to `users` table
     * via `user_id` field.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function personalChallengeUser()
    {
        return $this->belongsTo(\App\Models\PersonalChallengeUser::class, 'personal_challenge_user_id');
    }
}
