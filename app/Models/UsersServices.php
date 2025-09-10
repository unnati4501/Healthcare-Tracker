<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class UsersServices extends Model implements HasMedia
{
    use InteractsWithMedia;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users_services';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'service_id',
        'created_at',
        'updated_at',
    ];
}
