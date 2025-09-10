<?php

namespace App\Models;

use App\Models\Event;
use App\Models\EventBookingLogs;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class EventInviteSequenceUserLog extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'event_invite_sequence_user_logs';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'event_id',
        'event_booking_log_id',
        'user_id',
        'sequence',
    ];

    /**
     * "BelongsTo" relation to `events` table
     * via `event_id` field.
     *
     * @return BelongsTo
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * "BelongsTo" relation to `events` table
     * via `event_id` field.
     *
     * @return BelongsTo
     */
    public function bookingLog(): BelongsTo
    {
        return $this->belongsTo(EventBookingLogs::class, 'event_booking_log_id');
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
