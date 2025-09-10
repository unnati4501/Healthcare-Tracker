<?php

namespace App\Http\Resources\V17;

use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Models\Challenge;
use App\Models\Course;
use App\Models\PersonalChallenge;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class BadgeDetailResource extends JsonResource
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

        $title           = $this->title;
        $challengeName   = '';
        $masterclassName = '';

        if (!empty($this->badgeModelId) && !empty($this->badgeModelName) && $this->type == 'challenge') {
            if ($this->badgeModelName == 'challenge') {
                $model = Challenge::find($this->badgeModelId);
            } elseif ($this->badgeModelName == 'personal_challenge') {
                $model = PersonalChallenge::find($this->badgeModelId);
            }

            if (!empty($model)) {
                $title .= " (" . $model->title . ")";
                $challengeName = $model->title;
            }
        }

        if (!empty($this->modelId) && !empty($this->modelName) && $this->type == 'masterclass') {
            if ($this->modelName == 'masterclass') {
                $model = Course::find($this->modelId);
            }

            if (!empty($model)) {
                $masterclassName = $model->title;
            }
        }

        return [
            'id'              => $this->id,
            'userId'          => $this->user_id,
            'title'           => $title,
            'type'            => $this->type,
            'masterclassName' => $this->when($this->type == 'masterclass' && !empty($masterclassName), $masterclassName),
            'challengeName'   => $this->when($this->type == 'challenge' && !empty($challengeName), $challengeName),
            'challengeType'   => $this->when($this->type == 'challenge', ucfirst(str_replace('_', ' ', $this->challenge_type_slug))),
            'description'     => $this->description,
            'badgeUserId'     => $this->badgeUserId,
            'status'          => $this->status,
            'level'           => $this->level,
            'achieverName'    => $this->achieverName,
            'awardedOn'       => Carbon::parse($this->badgeAwardedOn, config('app.timezone'))->setTimezone($user->timezone)->toAtomString(),
            'image'           => $this->getMediaData('logo', ['w' => 320, 'h' => 320]),
        ];
    }
}
