<?php

namespace App\Http\Resources\V1;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupDetailsResource extends JsonResource
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

        $membersIds = array();

        $loginUserData = $this->members()->wherePivot("status", "Accepted")->wherePivot("user_id", $user->getKey())->first();

        if ($this->creator_id == $user->getKey()) {
            $membersIds = $this->members()->wherePivot("user_id", "!=", $user->getKey())->get()->pluck('id')->toArray();
        }

        return [
            'id'          => $this->id,
            'name'        => $this->title,
            'description' => (!empty($this->description)) ? $this->description : "",
            'image'       => $this->getMediaData('logo', ['w' => 320, 'h' => 320]),
            'members'     => (!empty($this->members)) ? $this->members : 0,
            'membersData' => $membersIds,
            'isMember'    => (!empty($loginUserData)) ,
            'muted'       => (!empty($loginUserData) && $loginUserData->pivot->notification_muted) ,
            'creator'     => $this->getCreatorData(),
        ];
    }
}
