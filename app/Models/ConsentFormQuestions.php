<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsentFormQuestions extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'consent_form_questions';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'consent_id',
        'title',
        'description',
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
    public function consentForm(): BelongsTo
    {
        return $this->belongsTo('App\Models\ConsentForm');
    }
}
