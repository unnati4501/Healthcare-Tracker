<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ChallengeRule extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'challenge_rules';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'challenge_id',
        'challenge_category_id',
        'challenge_target_id',
        'target',
        'uom',
        'model_id',
        'content_challenge_ids',
        'model_name'
    ];

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
    protected $dates = [];

    /**
     * @return BelongsTo
     */
    public function challenge(): BelongsTo
    {
        return $this->belongsTo('App\Models\Challenge');
    }

    /**
     * @return BelongsTo
     */
    public function challengeTarget(): BelongsTo
    {
        return $this->belongsTo('App\Models\ChallengeTarget');
    }
}
