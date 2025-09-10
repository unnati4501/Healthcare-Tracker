<?php

namespace App\Http\Resources\V8;

use Illuminate\Http\Resources\Json\JsonResource;

class HomeLeaderboardResource extends JsonResource
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
            'id'    => $this->id,
            'name'  => $this->name,
            'steps' => (int) $this->steps,
            'image' => $this->getMediaData('logo', ['w' => 320, 'h' => 320, 'zc' => 0]),
        ];
    }
}
