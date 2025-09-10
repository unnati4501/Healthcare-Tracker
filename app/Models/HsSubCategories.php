<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $category_id
 * @property string $name
 * @property string $display_name
 * @property boolean $status
 * @property string $created_at
 * @property string $updated_at
 * @property HsCategory $hsCategory
 * @property HsQuestion[] $hsQuestions
 */
class HsSubCategories extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hs_sub_categories';

    /**
     * @var array
     */
    protected $fillable = [
        'category_id',
        'name',
        'display_name',
        'status',
        'created_at',
        'updated_at',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function hsCategory()
    {
        return $this->belongsTo('App\Models\HsCategories', 'category_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hsQuestions()
    {
        return $this->hasMany('App\Models\HsQuestions', 'sub_category_id');
    }
}
