<?php

namespace App\Http\Resources\V9;

use Illuminate\Http\Resources\Json\JsonResource;

class ChallengeImageLibraryTargetTypeResource extends JsonResource
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
            'id'   => $this->id,
            'name' => $this->target,
            'slug' => $this->slug,
        ];
    }
}
