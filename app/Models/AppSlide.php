<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Yajra\DataTables\Facades\DataTables;

class AppSlide extends Model implements HasMedia
{

    use InteractsWithMedia;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'app_slides';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['content', 'portal_content', 'order_priority', 'type'];

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

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogoAttribute()
    {
        return $this->getLogo(['w' => 100, 'h' => 100, 'ct' => 1]);
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getPortalLogoAttribute()
    {
        return $this->getPortalLogo(['w' => 100, 'h' => 100, 'ct' => 1]);
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogoNameAttribute()
    {
        $collectionType = ($this->type == 'portal') ? 'slideImagePortal' : 'slideImage';
        return $this->getFirstMedia($collectionType)->name;
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getPortalLogoNameAttribute()
    {
        $collectionType = 'portalSlideImage';
        return $this->getFirstMedia($collectionType)->name ?? null;
    }

    /**
     * @param string $params
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogo(array $params): string
    {
        $collectionType = ($this->type == 'portal') ? 'slideImagePortal' : 'slideImage';
        $media          = $this->getFirstMedia($collectionType);
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $this->getFirstMediaUrl($collectionType);
        }
        return getThumbURL($params, 'app_slide', $collectionType);
    }

    /**
     * @param string $params
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getPortalLogo(array $params): string
    {
        $collectionType = 'portalSlideImage';
        $media          = $this->getFirstMedia($collectionType);
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $this->getFirstMediaUrl($collectionType);
        }
        return getThumbURL($params, 'app_slide', $collectionType);
    }

    /**
     * Set datatable for app slides list.
     *
     * @param payload
     * @return dataTable
     */

    public function getTableData($payload)
    {
        $list = $this->getSlidesList($payload);

        return DataTables::of($list)
            ->addIndexColumn()
            ->addColumn('updated_at', function ($slide) {
                return $slide->updated_at;
            })
            ->addColumn('content', function ($slide) {
                return strip_tags(htmlspecialchars_decode($slide->content));
            })
            ->addColumn('slideImage', function ($slide) {
                if (!empty($slide->logo)) {
                    return '<div class="table-img table-img-l"><img src="' . $slide->logo . '" alt=""></div>';
                } else {
                    return '<div class="table-img table-img-l"><img src="' . asset('assets/dist/img/boxed-bg.png') . '" alt=""></div>';
                }
            })
            ->addColumn('actions', function ($slide) {
                return view('admin.slides.listaction', compact('slide'))->render();
            })
            ->rawColumns(['actions', 'slideImage'])
            ->make(true);
    }

    /**
     * get slide list for data table list.
     *
     * @param payload
     * @return roleList
     */

    public function getSlidesList($payload)
    {
        $type  = $payload['type'];
        $query = AppSlide::where('type', $type)
            ->orderBy('order_priority', 'ASC');

        return $query->get();
    }

    /**
     * store AppSlide data.
     *
     * @param payload
     * @return boolean
     */

    public function storeEntity($payload)
    {
        $slideObj = new AppSlide();

        $slideObj->content = trim(str_replace(["\r\n", "&nbsp;", "&nbsp; "], "", htmlspecialchars_decode($payload['content'])));

        if ($payload['type'] == 'eap') {
            $slideObj->portal_content = trim(str_replace(["\r\n", "&nbsp;", "&nbsp; "], "", htmlspecialchars_decode($payload['portal_content'])));
        }
        
        $slideObj->order_priority = (self::where('type', $payload['type'])->max('order_priority') + 1);

        $slideObj->type = $payload['type'];

        $data = $slideObj->save();

        if ($data) {
            if (isset($payload['slideImage']) && !empty($payload['slideImage'])) {
                $name = $slideObj->id . '_' . \time();

                $collectionName = 'slideImage';
                if ($payload['type'] == 'portal') {
                    $collectionName = 'slideImagePortal';
                }
                $slideObj->clearMediaCollection($collectionName)->addMediaFromRequest('slideImage')
                    ->usingName($payload['slideImage']->getClientOriginalName())
                    ->usingFileName($name . '.' . $payload['slideImage']->extension())
                    ->toMediaCollection($collectionName, config('medialibrary.disk_name'));
            }

            if (isset($payload['portalSlideImage']) && !empty($payload['portalSlideImage'])) {
                $name = $slideObj->id . '_' . \time();

                $collectionName = 'portalSlideImage';
                $slideObj->clearMediaCollection($collectionName)->addMediaFromRequest('portalSlideImage')
                    ->usingName($payload['portalSlideImage']->getClientOriginalName())
                    ->usingFileName($name . '.' . $payload['portalSlideImage']->extension())
                    ->toMediaCollection($collectionName, config('medialibrary.disk_name'));
            }
            return true;
        }

        return false;
    }

    /**
     * update AppSlide data.
     *
     * @param payload , $id
     * @return boolean
     */

    public function updateEntity($payload, $id)
    {
        $slideObj = AppSlide::where("id", $id)->first();

        if (!empty($slideObj)) {
            $slideObj->content = trim(str_replace(["\r\n", "&nbsp;", "&nbsp; "], "", htmlspecialchars_decode($payload['content'])));

            if ($payload['type'] == 'eap') {
                $slideObj->portal_content = trim(str_replace(["\r\n", "&nbsp;", "&nbsp; "], "", htmlspecialchars_decode($payload['portal_content'])));
            }

            $data = $slideObj->save();

            if ($data) {
                if (isset($payload['slideImage']) && !empty($payload['slideImage'])) {
                    $name           = $slideObj->id . '_' . \time();
                    $collectionName = 'slideImage';
                    if ($payload['type'] == 'portal') {
                        $collectionName = 'slideImagePortal';
                    }
                    $slideObj->clearMediaCollection($collectionName)->addMediaFromRequest('slideImage')
                        ->usingName($payload['slideImage']->getClientOriginalName())
                        ->usingFileName($name . '.' . $payload['slideImage']->extension())
                        ->toMediaCollection($collectionName, config('medialibrary.disk_name'));
                }

                if (isset($payload['portalSlideImage']) && !empty($payload['portalSlideImage'])) {
                    $name           = $slideObj->id . '_' . \time();
                    $collectionName = 'portalSlideImage';
                    $slideObj->clearMediaCollection($collectionName)->addMediaFromRequest('portalSlideImage')
                        ->usingName($payload['portalSlideImage']->getClientOriginalName())
                        ->usingFileName($name . '.' . $payload['portalSlideImage']->extension())
                        ->toMediaCollection($collectionName, config('medialibrary.disk_name'));
                }
                return true;
            }
        }

        return false;
    }

    /**
     * fatch appSlide data by rSlide id.
     *
     * @param $id
     * @return AppSlide data
     */

    public function getSlideDataById($id)
    {
        return AppSlide::where("id", $id)->first();
    }

    /**
     * delete AppSlide by AppSlide id.
     *
     * @param $id
     * @return array
     */

    public function deleteAppSlide()
    {
        $deleteOrderPriority = $this->order_priority;
        $type                = $this->type;
        if ($this->delete()) {
            self::where('order_priority', '>', $deleteOrderPriority)
                ->where('type', $type)
                ->decrement('order_priority', 1);
            $count       = $this->onBoardingCount('app');
            $portalCount = $this->onBoardingCount('portal');
            return array('deleted' => 'true', "onBoardingappCount" => $count, 'onBoardingportalCount' => $portalCount);
        }
        return array('deleted' => 'error');
    }

    public function onBoardingCount($type = 'app')
    {
        return AppSlide::where('type', $type)->count();
    }

    public function reorderingLesson($positions, $type)
    {
        $updated = false;
        foreach ($positions as $key => $position) {
            $updated = $this
                ->where([
                    'id'             => (int) $key,
                    'order_priority' => (int) $position['oldPosition'],
                    'type'           => $type,
                ])
                ->update([
                    'order_priority' => (int) $position['newPosition'],
                ]);
        }
        return $updated;
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
        $size   = config("zevolifesettings.imageConversions.app_slide.$collection");
        $return = [
            'width'  => ($param['w'] ?? $size['width']),
            'height' => ($param['h'] ?? $size['height']),
        ];

        $param['w'] = $return['width'];
        $param['h'] = $return['height'];

        $media = $this->getFirstMedia($collection);

        if (!is_null($media) && $media->count() > 1) {
            $param['src'] = $this->getFirstMediaUrl($collection, ($param['conversion'] ?? ''));
        }
        $return['url'] = getThumbURL($param, 'app_slide', $collection);
        return $return;
    }
}
