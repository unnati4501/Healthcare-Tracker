<?php

namespace App\Http\Resources\V1;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class OngoingCourseResource extends JsonResource
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
            'id'               => $this->id,
            'title'            => $this->title,
            'image'            => $this->getMediaData('logo', ['w' => 1280, 'h' => 640]),
            'isPremium'        => ($this->is_premium) ,
            'isJoined'         => ($this->pivot->joined) ,
            'joinedOn'         => ($this->pivot->joined_on) ? Carbon::parse($this->pivot->joined_on, config('app.timezone'))->setTimezone($user->timezone)->toAtomString() : null,
            'purchasedOn'      => ($this->pivot->joined_on) ? Carbon::parse($this->pivot->joined_on, config('app.timezone'))->setTimezone($user->timezone)->toAtomString() : null,
            'totalLessons'     => $this->courseLessions()->where('is_default', false)->count(),
            'completedLessons' => $user->completedLession($this->id),
            'coach'            => $this->getCreatorData(),
        ];
    }
}
