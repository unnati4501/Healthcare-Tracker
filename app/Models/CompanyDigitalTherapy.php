<?php

namespace App\Models;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyDigitalTherapy extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'company_digital_therapy';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'dt_is_online',
        'dt_is_onsite',
        'dt_wellbeing_sp_ids',
        'dt_session_update',
        'dt_advanced_booking',
        'dt_future_booking',
        'dt_counselling_duration',
        'dt_coaching_duration',
        'dt_max_sessions_user',
        'dt_max_sessions_company',
        'emergency_contacts',
        'consent',
        'consent_url',
        'set_hours_by',
        'set_availability_by',
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
    protected $casts = [];

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
        return $this->belongsTo(Company::class, 'company_id');
    }
}
