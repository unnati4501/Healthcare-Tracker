<?php

namespace App\Http\Resources\V1;

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
                'width'  => 320,
                'height' => 320,
                'url'    => $this->getLogo(['w' => 320, 'h' => 320]),
            ],
        ];
    }
}
