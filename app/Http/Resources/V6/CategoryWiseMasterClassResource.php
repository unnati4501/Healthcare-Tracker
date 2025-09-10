<?php

namespace App\Http\Resources\V6;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryWiseMasterClassResource extends JsonResource
{
    use ProvidesAuthGuardTrait;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'      => $this->id,
            'title'   => $this->title,
            'creator' => $this->getCreatorData(),
            'image'   => $this->getMediaData('logo', ['w' => 1280, 'h' => 640, 'zc' => 3]),
        ];
    }
}
