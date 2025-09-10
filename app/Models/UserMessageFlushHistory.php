<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMessageFlushHistory extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user_message_flush_history';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'group_id', 'flushed_at'];

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
    protected $dates = ['flushed_at'];
}
