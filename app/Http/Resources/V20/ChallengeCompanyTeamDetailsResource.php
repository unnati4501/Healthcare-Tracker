<?php

namespace App\Http\Resources\V20;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class ChallengeCompanyTeamDetailsResource extends JsonResource
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
        $teamRecord = \App\Models\Team::find($this->team_id);

        if (!empty($teamRecord)) {
            return [
                'id'      => $teamRecord->id,
                'name'    => $teamRecord->name,
                'image'   => $teamRecord->getMediaData('logo', ['w' => 320, 'h' => 320]),
                'deleted' => false,
            ];
        } else {
            $img           = [];
            $img['url']    = getDefaultFallbackImageURL("user", "user-none1");
            $img['width']  = 0;
            $img['height'] = 0;

            return [
                'id'      => $this->team_id,
                'name'    => 'Deleted Team',
                'image'   => (object) $img,
                'deleted' => true,
            ];
        }
    }
}
