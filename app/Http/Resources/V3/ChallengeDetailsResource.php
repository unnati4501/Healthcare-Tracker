<?php

namespace App\Http\Resources\V3;

use App\Http\Collections\V1\BadgeListCollection;
use App\Http\Traits\ProvidesAuthGuardTrait;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ChallengeDetailsResource extends JsonResource
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
            $loginUserData = $this->members()->wherePivot("challenge_id", $this->id)->wherePivot("user_id", $user->getKey())->first();
        } elseif ($this->challenge_type == 'team' || $this->challenge_type == 'company_goal') {
            $members       = $this->memberTeams()->count();
            $loginUserData = $this->memberTeams()->wherePivot("challenge_id", $this->id)->wherePivot("team_id", $user->teams()->first()->id)->first();
        } elseif ($this->challenge_type == 'inter_company') {
            $members       = $this->memberCompanies()->distinct('company_id')->pluck('company_id')->count();
            $loginUserData = $this->memberCompanies()
                ->wherePivot("challenge_id", $this->id)
                ->wherePivot("company_id", $user->company()->first()->id)
                ->first();
        }

        $badgeData = $this->challengeBadges()->wherePivot("challenge_id", $this->id)->get();

        $challengeBadge  = $this->challengeBadges()->wherePivot("challenge_id", $this->id)->first();
        $isStarted       = false;
        $isCompleted     = false;
        $timerData       = array();
        $currentDateTime = now($user->timezone)->toDateTimeString();
        $startDate       = Carbon::parse($this->start_date, config('app.timezone'))->setTimezone($user->timezone)->toDateTimeString();
        $endDate         = Carbon::parse($this->end_date, config('app.timezone'))->setTimezone($user->timezone)->toDateTimeString();

        if ($currentDateTime >= $startDate && $currentDateTime <= $endDate) {
            $isStarted = true;
        }

        if ($currentDateTime > $endDate) {
            $isCompleted = true;
        }

        if ($currentDateTime < $startDate) {
            $timerData = calculatDayHrMin($currentDateTime, $startDate);
        } else {
            $timerData = calculatDayHrMin($currentDateTime, $endDate);
        }

        return [
            'id'                    => $this->id,
            'title'                 => $this->title,
            'description'           => (!empty($this->description)) ? $this->description : "",
            'image'                 => $this->getMediaData('logo', ['w' => 1280, 'h' => 640]),
            'startDateTime'         => Carbon::parse($this->start_date, config('app.timezone'))->setTimezone($user->timezone)->toAtomString(),
            'endDateTime'           => Carbon::parse($this->end_date, config('app.timezone'))->setTimezone($user->timezone)->toAtomString(),
            'members'               => (!empty($members)) ? $members : 0,
            'isMember'              => (!empty($loginUserData) && $loginUserData->pivot->status == 'Accepted') ? true : false,
            'isStarted'             => $isStarted,
            'isCompleted'           => $isCompleted,
            'isOpen'                => (!$this->close) ? true : false,
            'timerData'             => $timerData,
            'creator'               => $this->getCreatorData(),
            'badges'                => new BadgeListCollection($badgeData),
            'cancelled'             => ($this->cancelled) ? true : false,
            'invitationStatus'      => (!empty($loginUserData)) ? $loginUserData->pivot->status : "",
            'type'                  => $this->challenge_type,
            'challengeCategoryName' => (!empty($this->challengeCatName)) ? $this->challengeCatName : "",
        ];
    }
}
