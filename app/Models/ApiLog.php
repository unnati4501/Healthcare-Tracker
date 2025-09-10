<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'api_logs';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'type',
        'route',
        'headers',
        'request_data',
        'parameters',
        'response_data',
        'status'
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
}
