<?php

namespace App\Models;

use App\Models\ServiceSubCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Yajra\DataTables\Facades\DataTables;

class Service extends Model implements HasMedia
{

    use SoftDeletes, InteractsWithMedia;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'services';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'default',
        'description',
        'is_public',
        'session_duration',
        'is_counselling',
        'created_at',
        'updated_at'
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
        return $this->hasMany('App\Models\ServiceSubCategory', 'service_id');
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogoAttribute()
    {
        return $this->getLogo(['h' => 512, 'w' => 512]);
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogoNameAttribute()
    {
        $media = $this->getFirstMedia('logo');
        return !empty($media->name) ? $media->name : "onboard_large.png";
    }

    /**
     * @param string $size
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
        return getThumbURL($params, 'services', 'logo');
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getIconAttribute()
    {
        return $this->getIcon(['h' => 36, 'w' => 36]);
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getIconNameAttribute()
    {
        $media = $this->getFirstMedia('icon');
        return !empty($media->name) ? $media->name : "onboard_large.png";
    }

    /**
     * @param string $size
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getIcon(array $params): string
    {
        $media = $this->getFirstMedia('icon');
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $this->getFirstMediaUrl('icon');
        }
        return getThumbURL($params, 'services', 'icon');
    }

    /**
     * @param string $collection
     * @param array $param
     *
     * @return array
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getMediaData(string $collection, array $param): array
    {
        $return = [
            'width'  => $param['w'],
            'height' => $param['h'],
        ];
        $media = $this->getFirstMedia($collection);

        if (!is_null($media) && $media->count() > 1) {
            $param['src'] = $this->getFirstMediaUrl($collection);
        }

        $return['url'] = getThumbURL($param, 'services', $collection);
        return $return;
    }

    /**
     * Set datatable for service list.
     *
     * @param payload
     * @return dataTable
     */
    public function getTableData($payload)
    {
        $list = $this->getServiceList($payload);
        return DataTables::of($list)
            ->addColumn('logo', function ($service) {
                $return['logo'] = $service->logo;
                $return['icon'] = (!empty($service->icon) ? $service->icon : null);
                return $return;
            })
            ->addColumn('service_type', function ($service) {
                return $service->is_public ? 'Public' : 'Private';
            })
            ->addColumn('updated_at', function ($service) {
                return $service->updated_at;
            })
            ->addColumn('subcategory', function ($service) {
                $subCategories      = $service->subcategories()->select('service_sub_categories.name')->get()->toArray();
                $totalSubcategories = sizeof($subCategories);
                if ($totalSubcategories > 0) {
                    return "<a href='javascript:void(0);' title='View Sub-Categories' class='preview_subcategories' data-rowdata='" . base64_encode(json_encode($subCategories)) . "' data-cid='" . $service->id . "'> " . $totalSubcategories . "</a>";
                }
            })
            ->addColumn('wellbeing_specialist', function ($service) {
                $services    = $service->subcategories()->select('service_sub_categories.id')->get()->pluck('id')->toArray();
                $wellbeingSp = \DB::table('users_services')->select(\DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS name"))->leftJoin('users', 'users.id', '=', 'users_services.user_id')
                    ->whereIn('users_services.service_id', $services)->distinct()->get()->toArray();
                $totalWellbeingSp = sizeof($wellbeingSp);
                if ($totalWellbeingSp > 0) {
                    return "<a href='javascript:void(0);' title='View Wellbeing Specialist' class='preview_wellbeing_specialist' data-rowdata='" . base64_encode(json_encode($wellbeingSp)) . "' data-cid='" . $service->id . "'> " . $totalWellbeingSp . "</a>";
                } else {
                    return "0";
                }
            })
            ->addColumn('actions', function ($service) {
                return view('admin.services.listaction', compact('service'))->render();
            })
            ->rawColumns(['actions', 'subcategory', 'wellbeing_specialist'])
            ->make(true);
    }

    /**
     * get service list for data table list.
     *
     * @param payload
     * @return serviceList
     */

    public function getServiceList($payload)
    {
        $query = self::with('subcategories')
                    ->orderBy('default', 'DESC')
                    ->orderBy('services.updated_at', 'DESC');
        return $query->get();
        }

    /**
     * delete service by id.
     *
     * @param $id
     * @return array
     */
    public function deleteService()
    {
        $services    = $this->subcategories()->select('service_sub_categories.id')->get()->pluck('id')->toArray();
        $wellbeingSp = \DB::table('users_services')->select(\DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS name"))->leftJoin('users', 'users.id', '=', 'users_services.user_id')
            ->whereIn('users_services.service_id', $services)->distinct()->get()->toArray();
        $totalWellbeingSp = sizeof($wellbeingSp);
        if($totalWellbeingSp > 0){
            return array('deleted' => 'use');
        }
       if ($this->delete()) {
            ServiceSubCategory::where('service_id', $this->id)->delete();
            return array('deleted' => 'true');
        }
        return array('deleted' => 'error');
    }

    /**
     * @return hasMany
     */
    public function serviceSubCategory(): hasMany
    {
        return $this->hasMany('App\Models\ServiceSubCategory', 'service_id', 'id');
    }

    /**
     * store sub category data.
     *
     * @param payload
     * @return boolean
     */
    public function storeEntity($payload)
    {
        $service = self::create([
            'name'             => $payload['name'],
            'description'      => $payload['description'],
            'is_public'        => $payload['is_public'],
            'session_duration' => $payload['session_duration'],
            'is_counselling'   => (!empty($payload['is_counselling']) && $payload['is_counselling'] == 'yes') ? 1 : 0,
        ]);

        if (!empty($payload['logo'])) {
            $name = $service->id . '_' . \time();
            $service
                ->clearMediaCollection('logo')
                ->addMediaFromRequest('logo')
                ->usingName($payload['logo']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['logo']->extension())
                ->toMediaCollection('logo', config('medialibrary.disk_name'));
        }

        if (!empty($payload['icon'])) {
            $name = $service->id . '_' . \time();
            $service
                ->clearMediaCollection('icon')
                ->addMediaFromRequest('icon')
                ->usingName($payload['icon']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['icon']->extension())
                ->toMediaCollection('icon', config('medialibrary.disk_name'));
        }

        //Add subcategories for services
        $serviceSubcategoriesData = [];
        foreach ($payload['subcategory_name'] as $index => $value) {
            $serviceSubcategoriesData[$index]['name'] = $value;
        }
        foreach ($payload['subcategory_logo'] as $index => $logo) {
            $serviceSubcategoriesData[$index]['sub_category_logo'] = $logo;
        }
        foreach ($payload['is_default'] as $index => $default) {
            $serviceSubcategoriesData[$index]['is_default'] = $default;
        }
        foreach ($payload['subcategory_logo_name'] as $index => $logoName) {
            $serviceSubcategoriesData[$index]['sub_category_logo_name'] = $logoName;
        }
        foreach ($serviceSubcategoriesData as $serviceSubcategory) {
            //Create service subcategory
            $subCategoryInput = [
                'service_id' => $service->id,
                'default'    => $serviceSubcategory['is_default'],
                'status'     => 1,
                'name'       => $serviceSubcategory['name'],
            ];
            $subCategory = ServiceSubCategory::create($subCategoryInput);

            //Upload logo of service subcategory
            $name = $subCategory->id . '_' . \time();
            $subCategory->clearMediaCollection('sub_category_logo')
                ->addMediaFromBase64($serviceSubcategory['sub_category_logo'])
                ->usingName($name)
                ->usingFileName($serviceSubcategory['sub_category_logo_name'])
                ->toMediaCollection('sub_category_logo', config('medialibrary.disk_name'));
        }

        if ($service) {
            return $service;
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
        $data['serviceData'] = $this;
        $data['serviceType'] = array(1 => 'Public', 0 => 'Private');
        $data['id']          = $this->id;
        return $data;
    }

    /**
     * update service data.
     *
     * @param payload , $id
     * @return boolean
     */
    public function updateEntity($payload)
    {
        $nowInUTC = now(config('app.timezone'))->todatetimeString();
        $updated  = $this->update([
            'name'             => $payload['name'],
            'description'      => $payload['description'],
            'is_public'        => $payload['is_public'],
            'session_duration' => $payload['session_duration'],
            'is_counselling'   => (!empty($payload['is_counselling']) && $payload['is_counselling'] == 'yes') ? 1 : 0,
        ]);

        if (!empty($payload['logo'])) {
            $name = $this->id . '_' . \time();
            $this->clearMediaCollection('logo')
                ->addMediaFromRequest('logo')
                ->usingName($payload['logo']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['logo']->extension())
                ->toMediaCollection('logo', config('medialibrary.disk_name'));
        }

        if (!empty($payload['icon'])) {
            $name = $this->id . '_' . \time();
            $this->clearMediaCollection('icon')
                ->addMediaFromRequest('icon')
                ->usingName($payload['icon']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['icon']->extension())
                ->toMediaCollection('icon', config('medialibrary.disk_name'));
        }

        $subCategoriesData = [];
        if (!empty($payload['subcategory_name'])) {
            foreach ($payload['subcategory_name'] as $index => $value) {
                $subCategoriesData[$index]['subcategory_name']       = $value;
                $subCategoriesData[$index]['sub_category_logo']      = $payload['subcategory_logo'][$index];
                $subCategoriesData[$index]['sub_category_id']        = $payload['subcategory_id'][$index];
                $subCategoriesData[$index]['sub_category_logo_name'] = $payload['subcategory_logo_name'][$index];
            }

            $subCategoriesIds = [];
            foreach ($subCategoriesData as $serviceSubcategory) {
                $subcategoryId = $serviceSubcategory['sub_category_id'];
                if (!is_null($serviceSubcategory['subcategory_name'])) {
                    $findId           = strpos($subcategoryId, "id");
                    $subCategoryInput = [
                        'service_id' => $this->id,
                        'status'     => 1,
                        'name'       => $serviceSubcategory['subcategory_name'],
                    ];
                    if ($findId === false) {
                        $subCategoryResult = ServiceSubCategory::find($subcategoryId);
                        $name              = $subCategoryResult->id . '_' . \time();
                        $subCategoryResult->update($subCategoryInput);
                        $subCategory = $subCategoryResult;
                    } else {
                        $name        = $subcategoryId . '_' . \time();
                        $subCategory = ServiceSubCategory::create($subCategoryInput);
                        array_push($subCategoriesIds, $subCategory->id);
                    }

                    if (!filter_var($serviceSubcategory['sub_category_logo'], FILTER_VALIDATE_URL)) {
                        $subCategory->clearMediaCollection('sub_category_logo')
                            ->addMediaFromBase64($serviceSubcategory['sub_category_logo'])
                            ->usingName($name)
                            ->usingFileName($serviceSubcategory['sub_category_logo_name'])
                            ->toMediaCollection('sub_category_logo', config('medialibrary.disk_name'));
                    }
                }
            }

            $serviceIds = array_merge(array_column($subCategoriesData, 'sub_category_id'), $subCategoriesIds);
            ServiceSubCategory::whereNotIn('id', $serviceIds)->where('service_id', $this->id)->delete();
            $this->update(['updated_at' => $nowInUTC]);
        }
        if ($updated) {
            return true;
        }
        return false;
    }
}
