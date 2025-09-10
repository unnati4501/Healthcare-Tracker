<?php

namespace App\Http\Resources\V38;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Feed;

class FeedRecentStoriesResource extends JsonResource
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
        $xDeviceOs               = strtolower(request()->header('X-Device-Os', ""));
        $typeArray               = config('zevolifesettings.type_array');
        $w                       = 1280;
        $h                       = 640;
        $typeIcon                = config('zevolifesettings.type_icon');
        $returnData              = [];
        $returnData['id']        = $this->id;
        $returnData['type']      = $typeArray[$this->type];
        $returnData['title']     = $this->title;
        $returnData['image']     = $this->getMediaData('featured_image', ['w' => $w, 'h' => $h, 'zc' => 3]);
        $returnData['typeImage'] = [
            'url'    => getStaticAlertIconUrl($typeIcon[$this->type]),
            'width'  => 0,
            'height' => 0,
        ];
        $returnData['headerImage']  = $this->when(($xDeviceOs != config('zevolifesettings.PORTAL')), $this->getMediaData('header_image', ['w' => 800, 'h' => 800]));
        $returnData['tag']          = $this->when(($xDeviceOs != "portal" && !empty($this->caption)), $this->caption);
        return $returnData;
    }
}
