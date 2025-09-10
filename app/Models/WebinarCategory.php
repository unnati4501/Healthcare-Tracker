<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebinarCategory extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'webinar_category';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'webinar_id',
        'category_id',
        'sub_category_id',
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
    protected $dates = ['created_at', 'updated_at'];

    /**
     * @return HasMany
     */
    public function webinar(): HasMany
    {
        return $this->hasMany('App\Models\Webinar');
    }
}
