<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminAlertUsers extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'admin_alert_users';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'alert_id',
        'user_name',
        'user_email',
    ];

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
    protected $dates = [
        'created_at',
        'updated_at',
    ];

     /**
     * @return BelongsTo
     */
    public function adminAlert(): BelongsTo
    {
        return $this->belongsTo('App\Models\AdminAlert');
    }
}
