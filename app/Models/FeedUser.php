<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedUser extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'feed_user';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['feed_id', 'user_id', 'saved' , 'saved_at' , 'liked','view_count'];

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
    protected $casts = ['saved' => 'boolean', 'liked' => 'boolean'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['saved_at'];
}
