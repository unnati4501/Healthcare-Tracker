<?php

namespace App\Models;

use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Yajra\DataTables\Facades\DataTables;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Models\ServiceSubCategory;

class ServiceSubCategory extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_sub_categories';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'service_id',
        'name',
        'default',
        'status',
        'created_at',
        'updated_at',
    ];
    
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * "belongs to" relation to `categories` table
     * via `category_id` field.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service(): belongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    /**
     * One-to-Many relations with user service list.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function wellbeingSpecialist(): belongsTo
    {
        return $this->belongsTo(ServiceSubCategory::class, 'service_id', 'id');
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogoAttribute()
    {
        return $this->getLogo(['h' => 36, 'w' => 36]);
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getSubCategoryLogoAttribute()
    {
        return $this->getSubCategoryLogo();
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
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getSubCategoryLogoNameAttribute()
    {
        $media = $this->getFirstMedia('sub_category_logo');
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
        return getThumbURL($params, 'sub_categories', 'logo');
    }

    /**
     * @param string $size
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getSubCategoryLogo(array $params): string
    {
        $media = $this->getFirstMedia('sub_category_logo');
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $this->getFirstMediaUrl('sub_category_logo');
        }
        return getThumbURL($params, 'sub_categories', 'sub_category_logo');
    }

    /**
     * Set datatable for sub-category list.
     *
     * @param payload
     * @return dataTable
     */
    public function getTableData($payload)
    {
        $list = $this->getServiceSubCategoryList($payload);

        return DataTables::of($list)
            ->addColumn('updated_at', function ($subCategory) {
                return $subCategory->updated_at;
            })
            ->addColumn('actions', function ($subCategory) {
                return view('admin.categories.subcategories.listaction', compact('subCategory'))->render();
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * get sub category list for data table list.
     *
     * @param payload
     * @return categoryList
     */
    public function getServiceSubCategoryList($payload)
    {
        $query = self::where('service_id', $payload['service']);

        return $query->get();
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

        $return['url'] = getThumbURL($param, 'sub_categories', $collection);
        return $return;
    }
}
