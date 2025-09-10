<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecipeType extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'recipe_types';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type_name',
        'status',
    ];
}
