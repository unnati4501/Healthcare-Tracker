<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventBookingLogsTemp extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'event_booking_logs_temp';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'event_id',
        'company_id',
        'presenter_user_id',
        'description',
        'notes',
        'email_notes',
        'is_complementary',
        'add_to_story',
        'cc_email',
        'company_type',
        'video_link',
        'capacity_log',
        'registration_date',
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
    protected $dates = [
    ];
}
