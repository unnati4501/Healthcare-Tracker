<?php

namespace App\Http\Resources\V1;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupInfoResource extends JsonResource
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

        if ($this->creator_id == $user->getKey()) {
            $membersIds = $this->members()->wherePivot("user_id", "!=", $user->getKey())->get()->pluck('id')->toArray();
        }

        return [
            'id'           => $this->id,
            'name'         => $this->title,
            'image'        => $this->getMediaData('logo', ['w' => 1280, 'h' => 640]),
            'members'      => (!empty($this->members)) ? $this->members : 0,
            'memberIds'    => $membersIds,
            'introduction' => (!empty($this->description)) ? $this->description : "",
            // 'category' => $this->getCategoryData(),
            'creator'      => $this->getCreatorData(),
        ];
    }
}
