<?php

namespace App\Http\Resources\V1;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ChallengeListResource extends JsonResource
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
            $members       = $this->members()->wherePivot("status", "Accepted")->count();
            $loginUserData = $this->members()
                ->wherePivot("status", "Accepted")
                ->wherePivot("challenge_id", $this->id)
                ->wherePivot("user_id", $user->getKey())
                ->first();
        } else {
            $members       = $this->memberTeams()->count();
            $loginUserData = $this->memberTeams()
                ->wherePivot("status", "Accepted")
                ->wherePivot("challenge_id", $this->id)
                ->wherePivot("team_id", $user->teams()->first()->id)
                ->first();
        }

        $challengeBadge  = $this->challengeBadges()->wherePivot("challenge_id", $this->id)->first();
        $isStarted       = false;
        $timerData       = array();
        $currentDateTime = now($user->timezone)->toDateTimeString();
        $startDate       = Carbon::parse($this->start_date, config('app.timezone'))->setTimezone($user->timezone)->toDateTimeString();
        $endDate         = Carbon::parse($this->end_date, config('app.timezone'))->setTimezone($user->timezone)->toDateTimeString();

        if ($currentDateTime >= $startDate) {
            $isStarted = true;
        }

        if ($currentDateTime < $startDate) {
            $timerData = calculatDayHrMin($currentDateTime, $startDate);
        } else {
            $timerData = calculatDayHrMin($currentDateTime, $endDate);
        }

        return [
            'id'            => $this->id,
            'title'         => $this->title,
            'startDateTime' => Carbon::parse($this->start_date, config('app.timezone'))->setTimezone($user->timezone)->toAtomString(),
            'endDateTime'   => Carbon::parse($this->end_date, config('app.timezone'))->setTimezone($user->timezone)->toAtomString(),
            'members'       => (!empty($members)) ? $members : 0,
            'isMember'      => (!empty($loginUserData)) ,
            'hasBadge'      => (!empty($challengeBadge)) ,
            'image'         => $this->getMediaData('logo', ['w' => 1280, 'h' => 640]),
            'creator'       => $this->getCreatorData(),
            'isStarted'     => $isStarted,
            'timerData'     => $timerData,
            'isOpen'        => (!$this->close) ,
            'type'          => $this->challenge_type,
        ];
    }
}
