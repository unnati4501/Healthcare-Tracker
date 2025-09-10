<?php

namespace App\Http\Resources\V18;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class BadgeListResource extends JsonResource
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
        /** @var User $user */
        $user = $this->user();

        return [
            'id'          => $this->id,
            'image'       => $this->getMediaData('logo', ['w' => 320, 'h' => 320]),
            'name'        => $this->title,
            'description' => ((!empty($this->description)) ? $this->description : ""),
            'badgeUserId' => $this->badgeUserId,
        ];
    }
}
