<?php

namespace App\Http\Resources\V22;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

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

        return $returnData;
    }
}
