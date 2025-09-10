<?php

namespace App\Http\Resources\V1;

use App\Http\Resources\V1\UpcomingChallengeListResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class InvitationsListResource extends JsonResource
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
            'id'             => $this->id,
            'requestor'      => $this->getCreatorData(),
            'requestMessage' => "invited you to the challenge.",
            'challenge'      => new UpcomingChallengeListResource($this),
        ];
    }
}
