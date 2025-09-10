<?php

namespace App\Http\Resources\V19;

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

        $points = isset($this->points) ? $this->points : null;

        if (!empty($userRecord)) {
            return [
                'id'      => $userRecord->id,
                'name'    => $userRecord->first_name . ' ' . $userRecord->last_name,
                'image'   => $userRecord->getMediaData('logo', ['w' => 320, 'h' => 320]),
                'deleted' => false,
                'points'  => $this->when(isset($points), $points),
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
                'deleted' => false,
                'points'  => $this->when(isset($points), $points),
            ];
        }
    }
}
