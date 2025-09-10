<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationUser extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'notification_user';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['notification_id', 'user_id', 'read', 'sent' , 'read_at', 'sent_on'];

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
    protected $casts = ['read' => 'boolean' , 'sent' => 'boolean'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['read_at','sent_on'];
}
