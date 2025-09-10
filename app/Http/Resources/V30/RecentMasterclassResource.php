<?php

namespace App\Http\Resources\V30;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class RecentMasterclassResource extends JsonResource
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
        $w                       = 1280;
        $h                       = 640;
        $returnData              = [];
        $returnData['id']        = $this->id;
        $returnData['title']     = $this->title;
        $returnData['creator']   = $this->getCreatorData();
        $returnData['image']     = $this->getMediaData('logo', ['w' => $w, 'h' => $h, 'zc' => 3]);
        return $returnData;
    }
}
