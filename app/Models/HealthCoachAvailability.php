<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HealthCoachAvailability extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'health_coach_availability';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'status',
        'from_date',
        'update_from',
        'to_date',
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
    protected $dates = ['from_date', 'to_date', 'created_at', 'updated_at'];

    /**
     * delete record by record id.
     *
     * @param $id
     * @return array
     */

    public function deleteRecord($id)
    {
        if (!empty($id)) {
            $deleted = \DB::table('health_coach_availability')->where('id', $id)->delete();
            if ($deleted) {
                return array('deleted' => 'true');
            } else {
                return array('deleted' => 'error');
            }
        } else {
            return array('deleted' => 'error');
        }
    }
}
