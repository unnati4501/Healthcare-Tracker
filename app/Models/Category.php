<?php

namespace App\Models;

use App\Models\CategoryTags;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Yajra\DataTables\Facades\DataTables;

class Category extends Model implements HasMedia
{

    use InteractsWithMedia;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'categories';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'short_name',
        'description',
        'in_activity_level',
        'is_excluded',
        'default',
        'has_tags',
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
        'in_activity_level' => 'boolean',
        'is_excluded'       => 'boolean',
        'default'           => 'boolean',
        'has_tags'          => 'boolean',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subcategories()
    {
        return $this->hasMany('App\Models\SubCategory', 'category_id');
    }

    /**
     * "hasMany" relation to `category_tags` table
     * via `category_id` field.
     *
     * @return hasMany
     */
    public function tags(): hasMany
    {
        return $this->hasMany(CategoryTags::class, 'category_id');
    }

    /**
     * Set datatable for category list.
     *
     * @param payload
     * @return dataTable
     */
    public function getTableData($payload)
    {
        $list = $this->getCategoryList($payload);
        return DataTables::of($list)
            ->addColumn('updated_at', function ($category) {
                return $category->updated_at;
            })
            ->addColumn('subcategory', function ($category) {
                return $category->subcategories()->count();
            })
            ->addColumn('actions', function ($category) {
                return view('admin.categories.listaction', compact('category'))->render();
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * get category list for data table list.
     *
     * @param payload
     * @return categoryList
     */

    public function getCategoryList($payload)
    {
        $query = self::with('subcategories');
        return $query->get();
    }
}
