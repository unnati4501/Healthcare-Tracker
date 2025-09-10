<?php

namespace App\Http\Resources\V37;

use Illuminate\Http\Resources\Json\JsonResource;
use Str;

class AppSlideResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $data['id']         = $this->id;
        $data['content']    = $this->description;
        if (!empty($this->banner_image)) {
            $data['image']  = $this->getMediaData('banner_image', ['w' => 640, 'h' => 640, 'zc' => 3]);
        } else {
            $data['image']  = ['width' => 640 ,'height' => 640 , 'url' => $this->image];
        }
        return $data;
    }
}
