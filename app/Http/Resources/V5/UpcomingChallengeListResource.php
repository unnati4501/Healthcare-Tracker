<?php

namespace App\Http\Resources\V5;

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
        } elseif ($this->challenge_type == 'team' || $this->challenge_type == 'company_goal') {
            $members = $this->memberTeams()->wherePivot("status", "Accepted")->wherePivot("challenge_id", $this->id)->count();
        } elseif ($this->challenge_type == 'inter_company') {
            $members = $this->memberCompanies()->wherePivot("status", "Accepted")->distinct('company_id')->pluck('company_id')->count();
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

        unset($timerData['hour']);
        unset($timerData['minute']);
        $timerData['day'] = $timerData['day'] + 1;

        return [
            'id'                    => $this->id,
            'title'                 => $this->title,
            'image'                 => $this->getMediaData('logo', ['w' => 1280, 'h' => 640]),
            'startDateTime'         => Carbon::parse($this->start_date, config('app.timezone'))->setTimezone($user->timezone)->toAtomString(),
            'endDateTime'           => Carbon::parse($this->end_date, config('app.timezone'))->setTimezone($user->timezone)->toAtomString(),
            'members'               => (!empty($members)) ? $members : 0,
            'creator'               => $this->getCreatorData(),
            'timerData'             => $timerData,
            // 'badges'                => new BadgeListCollection($badgeData),
            'hasBadge'              => true,
            'type'                  => $this->challenge_type,
            'challengeCategoryName' => (!empty($this->challengeCatName)) ? $this->challengeCatName : "",
        ];
    }
}
