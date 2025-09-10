<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecipeUser extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'recipe_user';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'recipe_id',
        'user_id',
        'saved',
        'saved_at',
        'liked',
        'favourited',
        'favourited_at',
        'created_at',
        'updated_at',
        'view_count',
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
        'saved'      => 'boolean',
        'liked'      => 'boolean',
        'favourited' => 'boolean',
        'view_count' => 'boolean',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'saved_at',
        'favourited_at',
        'created_at',
        'updated_at',
    ];
}
