<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EAPOrderPriority extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'eap_order_priority';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['company_id', 'eap_id', 'order_priority'];

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
     * @return BelongsTo
     */
    public function eap(): BelongsTo
    {
        return $this->belongsTo('App\Models\EAP');
    }
}
