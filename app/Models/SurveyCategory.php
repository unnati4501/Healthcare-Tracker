<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Yajra\DataTables\Facades\DataTables;

class SurveyCategory extends Model implements HasMedia
{

    use InteractsWithMedia;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'zc_categories';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'display_name',
        'status',
        'default',
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
        'status'  => 'boolean',
        'default' => 'boolean',
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
        return $this->hasMany('App\Models\SurveySubCategory', 'category_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function questions()
    {
        return $this->hasMany('App\Models\ZcSurveyQuestion', 'category_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function responses(): HasMany
    {
        return $this->hasMany('App\Models\ZcSurveyResponse', 'category_id');
    }

    /**
     * One-to-Many relations with survey category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function surveyCategoryGoalTag()
    {
        return $this->belongsToMany('App\Models\Goal', 'zc_categories_tag', 'categories_id', 'goal_id');
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogoAttribute()
    {
        return $this->getLogo(['h' => 100, 'w' => 100]);
    }

    /**
     * @param string $params
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogo(array $params): string
    {
        $media = $this->getFirstMedia('logo');
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $this->getFirstMediaUrl('logo');
        }
        return getThumbURL($params, 'recipe', 'logo');
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
            ->addColumn('logo', function ($category) {
                return "<div class='table-img table-img-l'><img src='{$category->logo}'/></div>";
            })
            ->addColumn('subcategory', function ($category) {
                return $category->subcategories()->count();
            })
            ->addColumn('actions', function ($category) {
                return view('admin.surveycategories.listaction', compact('category'))->render();
            })
            ->rawColumns(['actions', 'logo'])
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

        if (in_array('categoryName', array_keys($payload)) && !empty($payload['categoryName'])) {
            $query->where('display_name', 'like', '%' . $payload['categoryName'] . '%');
        }

        return $query->get();
    }

    /**
     * store category data.
     *
     * @param payload
     * @return boolean
     */

    public function storeEntity($payload)
    {
        $data = [
            'name'         => str_replace(' ', '_', strtolower($payload['display_name'])),
            'display_name' => $payload['display_name'],
        ];

        $category = self::create($data);

        if (!empty($payload['logo'])) {
            $name = $category->id . '_' . \time();
            $category
                ->clearMediaCollection('logo')
                ->addMediaFromRequest('logo')
                ->usingName($payload['logo']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['logo']->extension())
                ->toMediaCollection('logo', config('medialibrary.disk_name'));
        }

        if (!empty($payload['goal_tag'])) {
            $category->surveyCategoryGoalTag()->sync($payload['goal_tag']);
        }

        if ($category) {
            return true;
        }

        return false;
    }

    /**
     * update category data.
     *
     * @param payload , $id
     * @return boolean
     */

    public function updateEntity($payload)
    {
        if (!empty($this)) {
            $this->name         = str_replace(' ', '_', strtolower($payload['display_name']));
            $this->display_name = $payload['display_name'];

            $data = $this->save();

            if (!empty($payload['logo'])) {
                $name = $this->id . '_' . \time();
                $this
                    ->clearMediaCollection('logo')
                    ->addMediaFromRequest('logo')
                    ->usingName($payload['logo']->getClientOriginalName())
                    ->usingFileName($name . '.' . $payload['logo']->extension())
                    ->toMediaCollection('logo', config('medialibrary.disk_name'));
            }

            if (isset($payload['remove_logo']) && $payload['remove_logo'] == 1) {
                $this->clearMediaCollection('logo');
            }

            if (!empty($payload['goal_tag'])) {
                $this->surveyCategoryGoalTag()->sync($payload['goal_tag']);
            } else {
                $this->surveyCategoryGoalTag()->detach();
            }

            if ($data) {
                return true;
            }
        }

        return false;
    }

    /**
     * delete category by category id.
     *
     * @param $id
     * @return array
     */
    public function deleteCategory()
    {
        if ($this->subcategories()->count() > 0) {
            return array('deleted' => 'use');
        }

        if ($this->delete()) {
            return array('deleted' => 'true');
        }

        return array('deleted' => 'error');
    }
}
