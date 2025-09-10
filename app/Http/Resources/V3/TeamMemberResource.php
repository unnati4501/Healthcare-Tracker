<?php

namespace App\Http\Resources\V3;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamMemberResource extends JsonResource
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
            'id'    => $this->id,
            'name'  => "$this->first_name $this->last_name",
            'image' => $this->getMediaData('logo', ['w' => 320, 'h' => 320]),
        ];
    }
}
