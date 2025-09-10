<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackerLogs extends Model
{

    const UPDATED_AT = null;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'tracker_logs';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */

    protected $fillable = ['user_id', 'os', 'tracker_name', 'request_url', 'request_data', 'fetched_data'];

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
    protected $dates = ['created_at'];
}
