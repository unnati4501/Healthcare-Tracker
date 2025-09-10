<?php

namespace App\Models;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalTherapySlots extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'digital_therapy_slots';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'location_id',
        'day',
        'start_time',
        'end_time',
        'ws_id',
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
    protected $casts = [
        'day' => 'string',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    /**
     * "BelongsTo" relation to `users` table
     * via `user_id` field.
     *
     * @return BelongsTo
     */
    public function company(): belongsTo
    {
        return $this->belongsTo('App\Models\Company');
    }
}
