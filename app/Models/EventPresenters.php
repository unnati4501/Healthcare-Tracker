<?php

namespace App\Models;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventPresenters extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'event_presenters';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['event_id', 'company_id', 'user_id'];

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
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * "BelongsTo" relation to `events` table
     * via `event_id` field.
     *
     * @return BelongsTo
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(User::class, 'event_id');
    }

    /**
     * "BelongsTo" relation to `users` table
     * via `user_id` field.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'user_id');
    }
}
