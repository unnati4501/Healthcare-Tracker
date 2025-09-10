<?php

namespace App\Http\Resources\V2;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class ChallengeTeamMemberDetailsResource extends JsonResource
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
        $userRecord = \App\Models\User::find($this->user_id);

        if (!empty($userRecord)) {
            return [
                'id'      => $userRecord->id,
                'name'    => $userRecord->first_name . ' ' . $userRecord->last_name,
                'image'   => $userRecord->getMediaData('logo', ['w' => 320, 'h' => 320]),
                'deleted' => false,
            ];
        } else {
            $img           = [];
            $img['url']    = "";
            $img['width']  = 0;
            $img['height'] = 0;

            return [
                'id'      => $this->user_id,
                'name'    => 'Deleted',
                'image'   => (object) $img,
                'deleted' => true,
            ];
        }
    }
}
