<?php
declare (strict_types = 1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserNpsLogs
 *
 * @package App\Models
 */
class UserNpsLogs extends Model
{
    /**
     * @var string
     */
    protected $table = 'user_nps_survey_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'survey_sent_on',
        'feedback_type',
        'feedback',
        'survey_received_on',
        'is_portal',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'user_id'       => 'integer',
        'feedback'      => 'string',
        'is_portal'     => 'boolean',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'survey_sent_on',
        'survey_received_on',
    ];
}
