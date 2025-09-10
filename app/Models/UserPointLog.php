<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPointLog extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user_point_logs';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'model_type',
        'model_id',
        'point',
        'meta',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'meta' => 'object',
    ];

    /**
     * "BelongsTo" relation to 'users' table
     * via 'user_id' field
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent pointable model (Audit survey, Masterclass, Meditation, Feed, Webinar, Recipe)
     */
    public function points()
    {
        return $this->morphTo();
    }
}
