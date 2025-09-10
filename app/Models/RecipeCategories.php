<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecipeCategories extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'recipe_categories';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'display_name',
        'status',
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
    protected $casts = ['status' => 'boolean'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];
}
