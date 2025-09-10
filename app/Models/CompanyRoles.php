<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyRoles extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'company_roles';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['company_id', 'role_id'];

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
    protected $dates = ['created_at', 'updated_at'];
}
