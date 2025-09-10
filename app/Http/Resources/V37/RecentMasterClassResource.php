<?php

namespace App\Http\Resources\V37;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class RecentMasterClassResource extends JsonResource
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
        $xDeviceOs      = strtolower($request->header('X-Device-Os', ""));
        $headerImage =  $this->getMediaData('header_image', ['w' => 800, 'h' => 800, 'zc' => 3]);

        $w                          = 1280;
        $h                          = 640;
        $returnData                 = [];
        $returnData['id']           = $this->id;
        $returnData['title']        = $this->title;
        $returnData['creator']      = $this->getCreatorData();
        $returnData['image']        = $this->getMediaData('logo', ['w' => $w, 'h' => $h, 'zc' => 3]);
        $returnData['headerImage']  = $this->when($xDeviceOs != config('zevolifesettings.PORTAL'), $headerImage);

        return $returnData;
    }
}
