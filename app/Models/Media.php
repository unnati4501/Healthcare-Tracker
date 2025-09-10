<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'media';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'model_type',
        'model_id',
        'collection_name',
        'name',
        'file_name',
        'mime_type',
        'disk',
        'size',
        'manipulations',
        'custom_properties',
        'responsive_images',
        'order_column'
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
    protected $dates = [];
}
