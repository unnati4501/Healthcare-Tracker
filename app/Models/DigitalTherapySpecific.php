<?php

namespace App\Models;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalTherapySpecific extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'digital_therapy_specific';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'location_id',
        'company_id',
        'ws_id',
        'date',
        'start_time',
        'end_time',
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
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['date', 'created_at', 'updated_at'];

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

    /**
     * "BelongsTo" relation to `users` table
     * via `user_id` field.
     *
     * @return BelongsTo
     */
    public function companyLocation(): belongsTo
    {
        return $this->belongsTo('App\Models\CompanyLocation');
    }
}
