<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentPointCalculation extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'content_point_calculation';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'category',
        'category_id',
        'target_id',
        'activities',
        'points',
        'log_date'
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
    protected $dates = ['log_date', 'created_at', 'updated_at'];

    /**
     * @return BelongsTo
     */
    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }
}
