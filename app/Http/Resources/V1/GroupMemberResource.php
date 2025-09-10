<?php

namespace App\Http\Resources\V1;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupMemberResource extends JsonResource
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

        return $this->getUserDataForApi() + [
            'activeBadgesCount'  => $this->activeBadgesCount,
        ];
    }
}
