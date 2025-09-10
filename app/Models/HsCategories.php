<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $name
 * @property string $display_name
 * @property boolean $status
 * @property string $created_at
 * @property string $updated_at
 * @property HsQuestion[] $hsQuestions
 * @property HsSubCategory[] $hsSubCategories
 */
class HsCategories extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hs_categories';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'display_name',
        'status',
        'created_at',
        'updated_at',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hsQuestions()
    {
        return $this->hasMany('App\Models\HsQuestions', 'category_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hsSubCategories()
    {
        return $this->hasMany('App\Models\HsSubCategories', 'category_id');
    }
}
