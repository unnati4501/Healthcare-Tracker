<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use DataTables;

class CompanyDigitalTherapyBanner extends Model implements HasMedia
{
    use InteractsWithMedia;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'company_digital_therapy_banners';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'description',
        'order_priority'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [];

    /**
     * @return BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo('App\Models\Company');
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getBannerImageAttribute()
    {
        return $this->getBannerImage(['w' => 640, 'h' => 640]);
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getBannerImageNameAttribute()
    {
        return $this->getFirstMedia('banner_image')->name;
    }

    /**
     * @param string $params
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getBannerImage(array $params): string
    {
        $media = $this->getFirstMedia('banner_image');
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $this->getFirstMediaUrl('banner_image');
        }
        return getThumbURL($params, 'company_digital_therapy_banners', 'banner_image');
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
        $return['url'] = getThumbURL($param, 'company_digital_therapy_banners', $collection);
        return $return;
    }

     /**
     * Set datatable for app listing.
     *
     * @param payload
     * @return dataTable
     */
    public function getTableData($payload)
    {
        $list               = $this->getList($payload);
        $companyType        = $payload['companyType'];
        return DataTables::of($list)
            ->addIndexColumn()
            ->addColumn('description', function ($record) {
                return (strlen(strip_tags(htmlspecialchars_decode($record->description))) > 150) ? substr(strip_tags(htmlspecialchars_decode($record->description)), 0, 60) . "..." : strip_tags(htmlspecialchars_decode($record->description));
            })
            ->addColumn('banner', function ($record) {
                return "<div class='table-img table-img-l'><img src='{$record->banner_image}'/></div>";
            })
            ->addColumn('actions', function ($record) use ($companyType)  {
                return view('admin.companies.dt-banners.listaction', compact('record', 'companyType'))->render();
            })
            ->rawColumns(['actions', 'banner'])
            ->make(true);
    }

    /**
     * get appSettings list for data table list.
     *
     * @param payload
     * @return appSettingsList
     */

    public function getList($payload)
    {
        $query = $this->where('company_id', $payload['company'])->orderBy('order_priority', 'ASC');
        return $query->get();
    }

    /**
     * store record data.
     *
     * @param payload, $company
     * @return boolean
     */
    public function storeEntity($payload, $company)
    {
        $bannerObj = new CompanyDigitalTherapyBanner();

        $bannerObj->description     = ($payload['description'] ? trim(str_replace(["\r\n", "&nbsp;", "&nbsp; "], "", htmlspecialchars_decode($payload['description']))) : "");
        $bannerObj->order_priority  = (self::where('company_id', $company->id)->max('order_priority') + 1);
        $bannerObj->company_id      = $company->id;
        $record = $bannerObj->save();
    
        if (isset($payload['banner_image']) && !empty($payload['banner_image'])) {
            $name = $bannerObj->id . '_' . \time();
            $bannerObj
                ->clearMediaCollection('banner_image')
                ->addMediaFromRequest('banner_image')
                ->usingName($payload['banner_image']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['banner_image']->getClientOriginalExtension())
                ->preservingOriginal()
                ->toMediaCollection('banner_image', config('medialibrary.disk_name'));
        }

        if ($record) {
            return true;
        }

        return false;
    }

    /**
     * update record data.
     *
     * @param payload
     * @return boolean
     */

     public function updateEntity($payload)
     {
         $updateData = [
            'description'  => ($payload['description'] ? trim(str_replace(["\r\n", "&nbsp;", "&nbsp; "], "", htmlspecialchars_decode($payload['description']))) : ""),
         ];
         $updated = $this->update($updateData);
 
         if (isset($payload['banner_image']) && !empty($payload['banner_image'])) {
             $name = $this->id . '_' . \time();
             $this
                 ->clearMediaCollection('banner_image')
                 ->addMediaFromRequest('banner_image')
                 ->usingName($payload['banner_image']->getClientOriginalName())
                 ->usingFileName($name . '.' . $payload['banner_image']->getClientOriginalExtension())
                 ->toMediaCollection('banner_image', config('medialibrary.disk_name'));
         }
 
         if ($updated) {
             return true;
         }
 
         return false;
     }

     /**
     * delete record by record id.
     *
     * @param $id
     * @return array
     */
    public function deleteRecord()
    {
        $totalBanners = self::where('company_id', $this->company_id)->count();
        if($totalBanners == 1){
            return array('deleted' => 'use');
        }
        $deleteOrderPriority = $this->order_priority;
        $this->clearMediaCollection('banner_image');
        if ($this->delete()) {
            self::where('order_priority', '>', $deleteOrderPriority)
                ->where('company_id', $this->company_id)
                ->decrement('order_priority', 1);
            $count       = $this->onBoardingCount($this->company_id);
            return array('deleted' => 'true', "onBoardingappCount" => $count);
        }
    }

    public function onBoardingCount($companyId)
    {
        return CompanyDigitalTherapyBanner::where('company_id', $companyId)->count();
    }

     /**
     * Update the banner's orders
     *
     * @param array $positions
     * @param object $company
     * @return array
     */
    public function reorderingBanner($positions, $company)
    {
        $updated = false;
        foreach ($positions as $key => $position) {
            $updated = $this
                ->where([
                    'id'             => (int) $key,
                    'order_priority' => (int) $position['oldPosition'],
                    'company_id'     => $company->id,
                ])
                ->update([
                    'order_priority' => (int) $position['newPosition'],
                ]);
        }
        return $updated;
    }
}
