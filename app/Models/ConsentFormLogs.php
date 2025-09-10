<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsentFormLogs extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'consent_form_logs';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cronofy_schedule_id',
        'user_id',
        'ws_id',
        'name',
        'email',
        'submitted_at',
        'fullname',
        'address',
        'contact_no',
        'relation',
        'is_accessed'
    ];

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
     * Custom builder instantiator. newEloquentBuilder is part
     * of Laravel.
     */
    public function newEloquentBuilder($query)
    {
        return new \App\Builders\BaseBuilder($query);
    }
}
