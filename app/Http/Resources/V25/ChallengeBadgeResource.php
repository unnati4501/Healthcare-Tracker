<?php

namespace App\Http\Resources\V25;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class ChallengeBadgeResource extends JsonResource
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
        /**
         * @var User
         * $user
         **/
        $image = $this->getMediaData('logo', ['w' => 320, 'h' => 320]);

        return [
            'id'               => $this->id,
            'image'            => $image,
            'name'             => $this->title,
            'description'      => ((!empty($this->description)) ? $this->description : ""),
            'badgeUserId'      => $this->when($this->badgeUserId != null, $this->badgeUserId),
            'achievementCount' => $this->assignCount,
        ];
    }
}
