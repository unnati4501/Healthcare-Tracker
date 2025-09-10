<?php

namespace App\Http\Resources\V11;

use App\Http\Traits\ProvidesAuthGuardTrait;
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
        $loggedUserLog = $this->feedUserLogs()->wherePivot('user_id', $this->user()->getKey())->first();
        $typeArray     = config('zevolifesettings.type_array');
        // $isHomeListing = Str::contains($request->route()->getName(), ['home-statistics']);
        $isHomeListing = true;
        $xDeviceOs     = strtolower(request()->header('X-Device-Os', ""));
        $w = 1280;
        $h = 640;
        if ($isHomeListing) {
            $w = 320;
            $h = 640;
        }
        if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
            $w = 200;
            $h = 200;
        }
        $typeIcon = config('zevolifesettings.type_icon');

        $returnData             = [];
        $returnData['id']       = $this->id;
        $returnData['type']     = $typeArray[$this->type];
        $returnData['title']    = $this->title;
        $returnData['image']    = $this->getMediaData('featured_image', ['w' => $w, 'h' => $h, 'zc' => 3]);
        $returnData['category'] = [
            'id'   => $this->sub_category_id,
            'name' => (isset($this->sub_category_name) ? $this->sub_category_name : (isset($this->courseSubCategory) ? $this->courseSubCategory : '')),
        ];
        $returnData['typeImage'] = [
            'url'    => getStaticAlertIconUrl($typeIcon[$this->type]),
            'width'  => 0,
            'height' => 0,
        ];
        $returnData['likes']     = $this->getTotalLikes();
        $returnData['isStick']   = $this->is_stick ;
        $returnData['isLiked']   = ((!empty($loggedUserLog) && $loggedUserLog->pivot->liked) );
        $returnData['isSaved']   = ((!empty($loggedUserLog) && $loggedUserLog->pivot->saved) );
        $returnData['viewCount'] = ((!empty($this->view_count)) ? $this->view_count : 0);
        $returnData['creator']   = $this->getCreatorData();

        return $returnData;
    }
}
