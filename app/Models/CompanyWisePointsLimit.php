<?php

namespace App\Models;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyWisePointsLimit extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'company_wise_points_limits';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'type',
        'value',
    ];

    /**
     * "BelongsTo" relation to 'companies' table
     * via 'company_id' field
     *
     * @return BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
