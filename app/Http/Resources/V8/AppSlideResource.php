<?php

namespace App\Http\Resources\V8;

use Illuminate\Http\Resources\Json\JsonResource;

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
        return [
            'id'      => $this->id,
            'content' => $this->content,
            'image'   => [
                'width'  => 640,
                'height' => 640,
                'url'    => $this->getLogo(['w' => 640, 'h' => 640, 'ct' => 1]),
            ],
        ];
    }
}
