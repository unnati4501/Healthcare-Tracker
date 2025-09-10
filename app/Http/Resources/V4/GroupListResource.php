<?php

namespace App\Http\Resources\V4;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupListResource extends JsonResource
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

        $loginUserData = $this->members()->wherePivot("status", "Accepted")->wherePivot("user_id", $user->getKey())->first();

        $memberCount = $this->members()->wherePivot("status", "Accepted")->count();

        return [
            'id'          => $this->id,
            'name'        => $this->title,
            'description' => (!empty($this->description)) ? $this->description : "",
            'image'       => $this->getMediaData('logo', ['w' => 1280, 'h' => 640]),
            'members'     => (!empty($memberCount)) ? $memberCount : 0,
            'isMember'    => (!empty($loginUserData)) ? true : false,
            'creator'     => $this->getCreatorData(),
        ];
    }
}
