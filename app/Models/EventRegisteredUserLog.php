<?php

namespace App\Models;

use App\Builders\BaseBuilder;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRegisteredUserLog extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'event_registered_users_logs';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['event_id', 'event_booking_log_id', 'user_id', 'is_cancelled', 'meta'];

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
    protected $casts = [
        'meta'              => 'object',
    ];

    /**
     * Custom builder instantiator. newEloquentBuilder is part
     * of Laravel.
     */
    public function newEloquentBuilder($query)
    {
        return new \App\Builders\BaseBuilder($query);
    }

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [];

    /**
     * "BelongsTo" relation to `users` table
     * via `user_id` field.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * "BelongsTo" relation to `events` table
     * via `event_id` field.
     *
     * @return BelongsTo
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
}
