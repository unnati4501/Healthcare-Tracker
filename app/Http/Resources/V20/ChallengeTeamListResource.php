<?php

namespace App\Http\Resources\V20;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class ChallengeTeamListResource extends JsonResource
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
        if ($this->getTable() === 'freezed_challenge_participents') {
            $img           = [];
            $img['url']    = getDefaultFallbackImageURL("user", "user-none1");
            $img['width']  = 0;
            $img['height'] = 0;

            $teamRecord = \App\Models\Team::find($this->team_id);

            if (isset($teamRecord)) {
                return [
                    'id'      => $teamRecord->id,
                    'name'    => $teamRecord->name,
                    'image'   => $teamRecord->getMediaData('logo', ['w' => 320, 'h' => 320]),
                    'deleted' => false,
                    'team'    => [
                        'id'   => $teamRecord->id,
                        'name' => $teamRecord->name,
                    ],
                ];
            } else {
                return [
                    'id'      => $this->team_id,
                    'name'    => 'Deleted Team',
                    'image'   => (object) $img,
                    'deleted' => true,
                    'team'    => [
                        'id'   => $this->team_id,
                        'name' => 'Deleted Team',
                    ],
                ];
            }
        } else {
            return [
                'id'      => $this->id,
                'name'    => $this->name,
                'image'   => $this->getMediaData('logo', ['w' => 320, 'h' => 320]),
                'deleted' => false,
                'team'    => [
                    'id'   => $this->id,
                    'name' => $this->name,
                ],
            ];
        }
    }
}
