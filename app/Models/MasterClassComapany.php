<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterClassComapany extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'masterclass_company';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['masterclass_id', 'company_id', 'created_at', 'updated_at'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'masterclass_id' => 'integer',
        'company_id'     => 'integer',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    /**
     * Custom builder instantiator. newEloquentBuilder is part
     * of Laravel.
     */
    public function newEloquentBuilder($query)
    {
        return new \App\Builders\BaseBuilder($query);
    }
}
