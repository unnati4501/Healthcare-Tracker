<?php

namespace App\Models;

use App\Models\CronofySchedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class SessionInviteSequenceUserLog extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'session_invite_sequence_user_logs';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'session_id',
        'user_id',
        'sequence',
    ];

    /**
     * "BelongsTo" relation to `events` table
     * via `event_id` field.
     *
     * @return BelongsTo
     */
    public function cronofySchedule(): BelongsTo
    {
        return $this->belongsTo(CronofySchedule::class, 'session_id');
    }

    /**
     * "BelongsTo" relation to `events` table
     * via `event_id` field.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
