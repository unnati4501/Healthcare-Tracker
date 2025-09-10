<?php

namespace App\Models;

use App\Models\SurveyCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Yajra\DataTables\Facades\DataTables;

class SurveySubCategory extends Model implements HasMedia
{
    use InteractsWithMedia;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'zc_sub_categories';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_id',
        'name',
        'display_name',
        'status',
        'default',
        'is_primum',
    ];

    /**
     * "belongs to" relation to `categories` table
     * via `category_id` field.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(\App\Models\SurveyCategory::class, 'category_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function questions()
    {
        return $this->hasMany('App\Models\ZcQuestion', 'sub_category_id', 'id');
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
     * Set datatable for sub-category list.
     *
     * @param payload
     * @return dataTable
     */
    public function getTableData($payload)
    {
        $list = $this->getSubCategoryList($payload);

        return DataTables::of($list)
            ->addColumn('logo', function ($subCategory) {
                return "<div class='table-img table-img-l'><img src='{$subCategory->logo}' /></div>";
            })
            ->addColumn('questions', function ($subCategory) {
                return $subCategory->questions()->count();
            })
            ->addColumn('premium', function ($subCategory) {
                return ($subCategory->is_primum) ? "Yes" : "No";
            })
            ->addColumn('actions', function ($subCategory) {
                return view('admin.surveycategories.surveysubcategories.listaction', compact('subCategory'))->render();
            })
            ->rawColumns(['actions', 'logo'])
            ->make(true);
    }

    /**
     * get sub category list for data table list.
     *
     * @param payload
     * @return categoryList
     */
    public function getSubCategoryList($payload)
    {
        $query = self::where('category_id', $payload['category']);

        if (in_array('subcategoryName', array_keys($payload)) && !empty($payload['subcategoryName'])) {
            $query->where('display_name', 'like', '%' . $payload['subcategoryName'] . '%');
        }

        if (in_array('isPrimum', array_keys($payload)) && !empty($payload['isPrimum'])) {
            if ($payload['isPrimum'] == "yes") {
                $query->where('is_primum', true);
            } elseif ($payload['isPrimum'] == "no") {
                $query->where('is_primum', false);
            }
        }

        return $query->get();
    }

    /**
     * store sub category data.
     *
     * @param payload
     * @return boolean
     */
    public function storeEntity($payload)
    {
        $data = [
            'category_id'  => $payload['category'],
            'display_name' => $payload['display_name'],
            'name'         => str_replace(' ', '_', strtolower($payload['display_name'])),
            'is_primum'    => (!empty($payload['is_primum']) && $payload['is_primum']),
        ];

        $subCategory = self::create($data);

        if (!empty($payload['logo'])) {
            $name = $subCategory->id . '_' . \time();
            $subCategory
                ->clearMediaCollection('logo')
                ->addMediaFromRequest('logo')
                ->usingName($payload['logo']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['logo']->extension())
                ->toMediaCollection('logo', config('medialibrary.disk_name'));
        }

        if ($subCategory) {
            return $subCategory;
        }

        return false;
    }

    /**
     * For pre-populating data in edit sub category page.
     *
     * @param $id
     * @return array
     */
    public function getUpdateData()
    {
        $data = array();

        $data['id']              = $this->id;
        $data['categories']      = Category::get()->pluck('name', 'id')->toArray();
        $data['subCategoryData'] = $this;
        $data['categoryId']      = $this->category_id;

        return $data;
    }

    /**
     * update sub-category data.
     *
     * @param payload , $id
     * @return boolean
     */
    public function updateEntity($payload)
    {
        if (!empty($this)) {
            $this->display_name = $payload['display_name'];
            $this->name         = str_replace(' ', '_', strtolower($payload['display_name']));
            $this->is_primum    = (!empty($payload['is_primum']) && $payload['is_primum']);

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

            if ($data) {
                return $this;
            }
        }
        return false;
    }

    /**
     * delete sub-category by id.
     *
     * @param $id
     * @return array
     */
    public function deleteSub()
    {
        if ($this->questions()->count() > 0) {
            return array('deleted' => 'use');
        }

        if ($this->delete()) {
            return array('deleted' => 'true');
        }

        return array('deleted' => 'error');
    }
}
