<?php

namespace App\Http\Resources\V1;

use App\Http\Collections\V1\BadgeListCollection;
use App\Http\Traits\ProvidesAuthGuardTrait;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class UpcomingChallengeListResource extends JsonResource
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

        if ($this->challenge_type == 'individual') {
            $members = $this->members()->wherePivot("status", "Accepted")->wherePivot("challenge_id", $this->id)->count();
        } else {
            $members = $this->memberTeams()->wherePivot("status", "Accepted")->wherePivot("challenge_id", $this->id)->count();
        }

        $badgeData = $this->challengeBadges()->wherePivot("challenge_id", $this->id)->get();

        $timerData       = array();
        $currentDateTime = now($user->timezone)->toDateTimeString();
        $startDate       = Carbon::parse($this->start_date, config('app.timezone'))->setTimezone($user->timezone)->toDateTimeString();
        $endDate         = Carbon::parse($this->end_date, config('app.timezone'))->setTimezone($user->timezone)->toDateTimeString();

        if ($currentDateTime < $startDate) {
            $timerData = calculatDayHrMin($currentDateTime, $startDate);
        } else {
            $timerData = calculatDayHrMin($currentDateTime, $endDate);
        }

        return [
            'id'            => $this->id,
            'title'         => $this->title,
            'image'                 => $this->getMediaData('logo', ['w' => 1280, 'h' => 640]),
            'startDateTime' => Carbon::parse($this->start_date, config('app.timezone'))->setTimezone($user->timezone)->toAtomString(),
            'endDateTime'   => Carbon::parse($this->end_date, config('app.timezone'))->setTimezone($user->timezone)->toAtomString(),
            'members'       => (!empty($members)) ? $members : 0,
            'creator'       => $this->getCreatorData(),
            'timerData'     => $timerData,
            'badges'        => new BadgeListCollection($badgeData),
            'type'          => $this->challenge_type,
        ];
    }
}
