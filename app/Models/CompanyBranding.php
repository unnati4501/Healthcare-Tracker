<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyBranding extends Model
{
    /**
     * The database table used by the models
     *
     * @var string
     */
    protected $table = 'company_branding';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'onboarding_title',
        'onboarding_description',
        'sub_domain',
        'portal_domain',
        'portal_theme',
        'portal_title',
        'portal_description',
        'portal_sub_description',
        'terms_url',
        'privacy_policy_url',
        'portal_footer_json',
        'portal_footer_text',
        'portal_footer_header_text',
        'exclude_gender_and_dob',
        'manage_the_design_change',
        'dt_title',
        'dt_description',
        'appointment_title',
        'appointment_description',
        'status',
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
    protected $casts = ['status' => 'boolean'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [];

    /**
     * @return BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo('App\Models\Company');
    }
}
