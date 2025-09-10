<?php

namespace App\Http\Resources\V20;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class ChallengeUserListResource extends JsonResource
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
        $user = $this->user();

        if ($this->getTable() === 'freezed_challenge_participents') {
            $img           = [];
            $img['url']    = getDefaultFallbackImageURL("user", "user-none1");
            $img['width']  = 0;
            $img['height'] = 0;

            $userRecord = \App\Models\User::find($this->user_id);

            if (isset($userRecord)) {
                return [
                    'id'      => $userRecord->id,
                    'name'    => $userRecord->first_name . ' ' . $userRecord->last_name,
                    'image'   => $userRecord->getMediaData('logo', ['w' => 320, 'h' => 320]),
                    'deleted' => false,
                    'team'    => [
                        'id'   => $userRecord->id,
                        'name' => $userRecord->name,
                    ],
                ];
            } else {
                return [
                    'id'      => $this->user_id,
                    'name'    => 'Deleted User',
                    'image'   => (object) $img,
                    'deleted' => true,
                    'team'    => [
                        'id'   => $this->user_id,
                        'name' => 'Deleted Team',
                    ],
                ];
            }
        } else {
            $team = $this->teams()->first();

            return [
                'id'      => $this->id,
                'name'    => $this->first_name . ' ' . $this->last_name,
                'image'   => $this->getMediaData('logo', ['w' => 320, 'h' => 320]),
                'deleted' => false,
                'team'    => [
                    'id'   => $team->id,
                    'name' => $team->name,
                ],
            ];
        }
    }
}
