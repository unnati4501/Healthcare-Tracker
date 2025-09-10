<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppVersion extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'app_versions';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'andriod_version',
        'andriod_force_update',
        'ios_version',
        'ios_force_update',
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
    protected $casts = [
        'andriod_force_update' => 'boolean',
        'ios_force_update' => 'boolean',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [];
}
