<?php

namespace App\Http\Resources\V38;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedListingResource extends JsonResource
{
    use ProvidesAuthGuardTrait;

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource)
    {
        // Ensure you call the parent constructor
        parent::__construct($resource);
        $this->resource = $resource;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $xDeviceOs     = strtolower(request()->header('X-Device-Os', ""));
        $isPortal      = false;
        if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
            $isPortal       = true;
        }
        if ($this->type != 0) {
            $loggedUserLog = $this->feedUserLogs()->wherePivot('user_id', $this->user()->getKey())->first();
            $typeArray     = config('zevolifesettings.type_array');
            $hasMedia      = false;
            $mediaData     = [];
            $isHomeListing = false;
            $xDeviceOs     = strtolower(request()->header('X-Device-Os', ""));
            $w = 1280;
            $h = 640;
            if ($isHomeListing) {
                $w = 320;
                $h = 640;
            } else if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
                $w = 800;
                $h = 800;
            }
            $typeIcon = config('zevolifesettings.type_icon');
            if ($this->type != 4) {
                $mediaData = $this->getFeedMediaData();
                $hasMedia  = true;
            }
            $returnData                = [];
            $returnData['id']          = $this->id;
            $returnData['type']        = $typeArray[$this->type];
            $returnData['description'] = $this->when(($this->type == 4), $this->description);
            $returnData['title']       = $this->title;
            $returnData['image']       = $this->getMediaData('featured_image', ['w' => $w, 'h' => $h, 'zc' => 3]);
            $returnData['category']    = $this->when(($isPortal), [
                'id'   => $this->sub_category_id,
                'name' => (isset($this->sub_category_name) ? $this->sub_category_name : (isset($this->courseSubCategory) ? $this->courseSubCategory : '')),
            ]);
            $returnData['typeImage'] = [
                'url'    => getStaticAlertIconUrl($typeIcon[$this->type]),
                'width'  => 0,
                'height' => 0,
            ];
            $returnData['likes']        = $this->when(($isPortal), $this->getTotalLikes());
            $returnData['isStick']      = $this->when(($isPortal), $this->is_stick);
            $returnData['media']        = $this->when($hasMedia && $isPortal, $mediaData);
            $returnData['isLiked']      = $this->when(($isPortal), (!empty($loggedUserLog) && $loggedUserLog->pivot->liked));
            $returnData['isSaved']      = $this->when(($isPortal), (!empty($loggedUserLog) && $loggedUserLog->pivot->saved));
            $returnData['isFavorited']  = $this->when(($isPortal), (!empty($loggedUserLog) && $loggedUserLog->pivot->favourited));
            $returnData['viewCount']    = $this->when(($isPortal), ((!empty($this->view_count)) ? (int) $this->view_count : 0));
            $returnData['creator']      = $this->when(($isPortal), $this->getCreatorData());
            $returnData['newTag']       = Carbon::now() < Carbon::parse($this->start_date)->addDays(5) ;
        } elseif ($this->type == 0) {
            $returnData['id']           = $this->booking_log_id;
            $returnData['type']         = 'event';
            $returnData['title']        = $this->name;
            $returnData['description']  = $this->when(($isPortal), $this->description);
            $returnData['image']        = $this->getMediaData('logo', ['w' => 1280, 'h' => 640, 'zc' => 3]);
            $returnData['typeImage']    = [
                'url'    => getStaticAlertIconUrl('event'),
                'width'  => 0,
                'height' => 0,
            ];
            $returnData['category'] = $this->when(($isPortal), [
                'id'   => 0,
                'name' => '',
            ]);
            $returnData['likes']        = $this->when(($isPortal), 0);
            $returnData['isStick']      = $this->when(($isPortal), false);
            $returnData['isLiked']      = $this->when(($isPortal), false);
            $returnData['isSaved']      = $this->when(($isPortal), false);
            $returnData['viewCount']    = $this->when(($isPortal), 0);
        }
        $returnData['headerImage']  = $this->when((!$isPortal), $this->getMediaData('header_image', ['w' => 800, 'h' => 800]));
        $returnData['tag'] = $this->when(($xDeviceOs != "portal" && !empty($this->caption)), $this->caption);
        return $returnData;
    }
}
