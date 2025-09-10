<?php

namespace App\Models;

use App\Models\Department;
use Illuminate\Database\Eloquent\Model;

class DepartmentLocation extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'department_location';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['company_location_id', 'company_id', 'department_id', 'auto_team_creation_meta'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'auto_team_creation_meta' => 'object',
    ];

    /**
     * 'BelongsTo' relation using 'departments'
     * table via 'department_id'
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
}
