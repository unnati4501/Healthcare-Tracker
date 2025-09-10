<?php

namespace App\Http\Resources\V31;

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
        if ($this->type != 0) {
            $loggedUserLog = $this->feedUserLogs()->wherePivot('user_id', $this->user()->getKey())->first();
            $typeArray     = config('zevolifesettings.type_array');
            $hasMedia      = false;
            $mediaData     = [];
            // $isHomeListing = Str::contains($request->route()->getName(), ['home-statistics']);
            $isHomeListing = false;
            $xDeviceOs     = strtolower(request()->header('X-Device-Os', ""));
            $w = 1280;
            $h = 640;
            if ($isHomeListing) {
                $w = 320;
                $h = 640;
            }
            if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
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
            $returnData['category']    = [
                'id'   => $this->sub_category_id,
                'name' => (isset($this->sub_category_name) ? $this->sub_category_name : (isset($this->courseSubCategory) ? $this->courseSubCategory : '')),
            ];
            $returnData['typeImage'] = [
                'url'    => getStaticAlertIconUrl($typeIcon[$this->type]),
                'width'  => 0,
                'height' => 0,
            ];
            $returnData['likes']        = $this->getTotalLikes();
            $returnData['isStick']      = $this->is_stick ? true : false;
            $returnData['media']        = $this->when($hasMedia, $mediaData);
            $returnData['isLiked']      = ((!empty($loggedUserLog) && $loggedUserLog->pivot->liked) ? true : false);
            $returnData['isSaved']      = ((!empty($loggedUserLog) && $loggedUserLog->pivot->saved) ? true : false);
            $returnData['isFavorited']  = ((!empty($loggedUserLog) && $loggedUserLog->pivot->favourited) ? true : false);
            $returnData['viewCount']    = ((!empty($this->view_count)) ? (int) $this->view_count : 0);
            $returnData['creator']      = $this->getCreatorData();
            $returnData['newTag']       = Carbon::now() < Carbon::parse($this->start_date)->addDays(5) ? true : false;
        } elseif ($this->type == 0) {
            $returnData['id']           = $this->booking_log_id;
            $returnData['type']         = 'event';
            $returnData['title']        = $this->name;
            $returnData['description']  = $this->description;
            $returnData['image']        = $this->getMediaData('logo', ['w' => 1280, 'h' => 640, 'zc' => 3]);
            $returnData['typeImage']    = [
                'url'    => getStaticAlertIconUrl('event'),
                'width'  => 0,
                'height' => 0,
            ];
            $returnData['category'] = [
                'id'   => 0,
                'name' => '',
            ];
            $returnData['likes']        = 0;
            $returnData['isStick']      = false;
            $returnData['isLiked']      = false;
            $returnData['isSaved']      = false;
            $returnData['viewCount']    = 0;
        }
        return $returnData;
    }
}
