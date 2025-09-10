<?php

namespace App\Models;

use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ChallengeHistory extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'challenge_history';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'challenge_id',
        'creator_id',
        'challenge_category_id',
        'challenge_type',
        'timezone',
        'title',
        'description',
        'start_date',
        'end_date',
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
    protected $dates = ['start_date', 'end_date'];

    /**
     * @return BelongsTo
     */
    public function challengecategory(): BelongsTo
    {
        return $this->belongsTo('App\Models\ChallengeCategory', 'challenge_category_id');
    }

    /**
     * @return BelongsTo
     */
    public function challenge(): BelongsTo
    {
        return $this->belongsTo('App\Models\Challenge', 'challenge_id');
    }
}
