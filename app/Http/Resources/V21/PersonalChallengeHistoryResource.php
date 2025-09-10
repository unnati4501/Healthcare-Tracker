<?php

namespace App\Http\Resources\V21;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonalChallengeHistoryResource extends JsonResource
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

        $challengeAchieved = $this->is_winner ? true : false;

        $returnArray = [
            'challengeId'        => $this->id,
            'title'              => $this->title,
            'image'              => $this->getMediaData('logo', ['w' => 1280, 'h' => 640]),
            "challengeStartDate" => Carbon::parse($this->start_date, config('app.timezone'))->setTimezone($user->timezone)->toAtomString(),
            "challengeEndDate"   => Carbon::parse($this->end_date, config('app.timezone'))->setTimezone($user->timezone)->toAtomString(),
            'duration'           => (int) $this->duration,
            'type'               => ucfirst($this->challenge_type),
            'subType'            => ucfirst($this->type),
            'challengeAchieved'  => $challengeAchieved,
            'creator'            => $this->getCreatorData(),
            'mappingId'          => $this->personal_challenge_mapping_id,
        ];

        return $returnArray;
    }
}
