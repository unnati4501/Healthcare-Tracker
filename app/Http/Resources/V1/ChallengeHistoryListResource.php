<?php

namespace App\Http\Resources\V1;

use App\Http\Collections\V1\BadgeListCollection;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Models\Badge;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ChallengeHistoryListResource extends JsonResource
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

        $challengeBadge = $this->challengeBadges()->wherePivot("challenge_id", $this->id)->first();

        $currentDateTime = now($user->timezone)->toDateTimeString();
        $startDate       = Carbon::parse($this->start_date, config('app.timezone'))->setTimezone($user->timezone)->toDateTimeString();
        $endDate         = Carbon::parse($this->end_date, config('app.timezone'))->setTimezone($user->timezone)->toDateTimeString();

        $badgeData = Badge::select("badges.*")->join('badge_user', 'badge_user.badge_id', '=', 'badges.id')->where("badge_user.user_id", $user->id)->where("badge_user.model_id", $this->id)->where("badge_user.model_name", 'challenge')->get();

        if ($this->challenge_type == 'individual') {
            $pointsRecord = $this->challengeWiseUserPoints()->where('user_id', $user->id)->first();
        } else {
            $freezedTeamData = $this->challengeWiseUserPoints()->where('user_id', $user->id)->first();
            if (empty($freezedTeamData)) {
                $pointsRecord = $this->challengeWiseTeamPoints()->where('team_id', $user->teams()->first()->id)->first();
            } else {
                $pointsRecord = $this->challengeWiseTeamPoints()->where('team_id', $freezedTeamData->team_id)->first();
            }
        }

        return [
            'id'            => $this->id,
            'title'         => $this->title,
            'startDateTime' => Carbon::parse($this->start_date, config('app.timezone'))->setTimezone($user->timezone)->toAtomString(),
            'endDateTime'   => Carbon::parse($this->end_date, config('app.timezone'))->setTimezone($user->timezone)->toAtomString(),
            'rank'          => (isset($pointsRecord) && !empty($pointsRecord->rank)) ? $pointsRecord->rank : 0,
            'image'         => $this->getMediaData('logo', ['w' => 1280, 'h' => 640]),
            'creator'       => $this->getCreatorData(),
            'badges'        => ($badgeData->count() > 0) ? new BadgeListCollection($badgeData) : [],
            'type'          => $this->challenge_type,
        ];
    }
}
