<?php

namespace App\Http\Resources\V17;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class UserBadgeDetailsResource extends JsonResource
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

        $title = $this->title;
        if (!empty($this->badgeModelId) && !empty($this->badgeModelName)) {
            if ($this->badgeModelName == 'challenge') {
                $model = \App\Models\Challenge::find($this->badgeModelId);
            } elseif ($this->badgeModelName == 'personal_challenge') {
                $model = \App\Models\PersonalChallenge::find($this->badgeModelId);
            }

            if (!empty($model)) {
                $title .= " (" . $model->title . ")";
            }
        }

        return [
            'id'          => $this->id,
            'title'       => $title,
            'type'        => $this->type,
            'image'       => $this->getMediaData('logo', ['w' => 320, 'h' => 320]),
            'awardedOn'   => Carbon::parse($this->badgeAwardedOn, config('app.timezone'))->setTimezone($user->timezone)->toAtomString(),
            'isExpired'   => (empty($this->badgeExpiredAt) && $this->status == "Active") ? false : true,
            'badgeUserId' => $this->badgeUserId,
        ];
    }
}
