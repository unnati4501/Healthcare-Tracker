<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MoodTagUser extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mood_tag_user';

    /**
     * @var array
     */
    protected $fillable = [
        'user_id',
        'company_id',
        'mood_id',
        'tag_id',
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
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * "belongs to" relation to `users` table
     * via `user_id` field.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /**
     * "belongs to" relation to `companies` table
     * via `company_id` field.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(\App\Models\Companie::class, 'company_id');
    }

    /**
     * "belongs to" relation to `moods` table
     * via `mood_id` field.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function mood()
    {
        return $this->belongsTo(\App\Models\Mood::class, 'mood_id');
    }

    /**
     * "belongs to" relation to `mood_tags` table
     * via `tag_id` field.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tag()
    {
        return $this->belongsTo(\App\Models\MoodTag::class, 'tag_id');
    }
}
