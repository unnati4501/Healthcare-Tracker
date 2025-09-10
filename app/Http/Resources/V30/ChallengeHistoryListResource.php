<?php

namespace App\Http\Resources\V30;

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
        } elseif ($this->challenge_type == 'team' || $this->challenge_type == 'company_goal') {
            $freezedTeamData = $this->challengeWiseUserPoints()->where('user_id', $user->id)->first();
            if (empty($freezedTeamData)) {
                $pointsRecord = $this->challengeWiseTeamPoints()->where('team_id', $user->teams()->first()->id)->first();
            } else {
                $pointsRecord = $this->challengeWiseTeamPoints()->where('team_id', $freezedTeamData->team_id)->first();
            }
        } elseif ($this->challenge_type == 'inter_company') {
            $pointsRecord = $this->challengeWiseCompanyPoints()->where('company_id', $user->company()->first()->id)->first();
        }

        if ($this->challenge_type == 'individual') {
            $members = $this->membersHistory()->count();
        } elseif ($this->challenge_type == 'team' || $this->challenge_type == 'company_goal') {
            $members = $this->memberTeamsHistory()->count();
        } elseif ($this->challenge_type == 'inter_company') {
            $members = \DB::table('freezed_challenge_participents')->where('challenge_id', $this->id)->distinct('company_id')->pluck('company_id')->count();
        }

        $mapChalengeFlag = false;
        if ($this->map_id != null) {
            $mapChalengeFlag = true;
        }

        return [
            'id'                    => $this->id,
            'title'                 => $this->title,
            'startDateTime'         => Carbon::parse($this->start_date, config('app.timezone'))->setTimezone($user->timezone)->toAtomString(),
            'endDateTime'           => Carbon::parse($this->end_date, config('app.timezone'))->setTimezone($user->timezone)->toAtomString(),
            'rank'                  => (isset($pointsRecord) && !empty($pointsRecord->rank)) ? $pointsRecord->rank : 0,
            'image'                 => $this->getMediaData('logo', ['w' => 1280, 'h' => 640, 'zc' => 3]),
            'creator'               => $this->getCreatorData(),
            'hasBadge'              => true,
            'badges'                => ($badgeData->count() > 0) ? new BadgeListCollection($badgeData) : [],
            'type'                  => $this->challenge_type,
            'members'               => $members,
            'isMapChallenge'        => $mapChalengeFlag,
            'challengeCategoryName' => (!empty($this->challengeCatName)) ? $this->challengeCatName : "",
        ];
    }
}
