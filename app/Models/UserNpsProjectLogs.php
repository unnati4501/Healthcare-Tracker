<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNpsProjectLogs extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user_nps_project_logs';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'nps_project_id',
        'feedback_type',
        'feedback',
        'survey_received_on',
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
    protected $dates = ['survey_received_on','created_at', 'updated_at'];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * @return BelongsTo
     */
    public function npsProject(): BelongsTo
    {
        return $this->belongsTo('App\Models\NpsProject');
    }
}
