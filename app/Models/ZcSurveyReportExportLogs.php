<?php

namespace App\Models;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZcSurveyReportExportLogs extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'zc_survey_report_export_logs';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'user_id',
        'status',
        'process_started_at',
        'process_completed_at',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'status' => 'integer',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['process_started_at', 'process_completed_at'];

    /**
     * BelongsTo relation with 'companies' table
     * using 'company_id' column
     *
     * @return BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * BelongsTo relation with 'users' table
     * using 'user_id' column
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
