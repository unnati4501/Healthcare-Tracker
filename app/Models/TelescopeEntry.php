<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelescopeEntry extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'telescope_entries';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sequence',
        'uuid',
        'batch_id',
        'family_hash',
        'should_display_on_index',
        'type',
        'content'
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
    protected $casts = ['should_display_on_index' => 'boolean'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [];
}
