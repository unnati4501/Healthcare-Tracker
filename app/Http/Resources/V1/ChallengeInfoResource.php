<?php

namespace App\Http\Resources\V1;

use App\Http\Collections\V1\BadgeListCollection;
use App\Http\Traits\ProvidesAuthGuardTrait;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ChallengeInfoResource extends JsonResource
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

        $badgeData = $this->challengeBadges()->wherePivot("challenge_id", $this->id)->get();

        $challengeBadge = $this->challengeBadges()->wherePivot("challenge_id", $this->id)->first();

        if ($this->challenge_type == 'individual') {
            $membersCount = $this->members()->wherePivot("status", "Accepted")->count();
            $members      = $this->members()->wherePivot("status", "Accepted")->wherePivot("challenge_id", $this->id)->get()->pluck("id")->toArray();
        } else {
            $membersCount = $this->memberTeams()->count();
            $members      = $this->memberTeams()->wherePivot("status", "Accepted")->wherePivot("challenge_id", $this->id)->get()->pluck("id")->toArray();
        }

        $isStarted       = false;
        $isCompleted     = false;
        $currentDateTime = now($user->timezone)->toDateTimeString();
        $startDate       = Carbon::parse($this->start_date, config('app.timezone'))->setTimezone($user->timezone)->toDateTimeString();
        $endDate         = Carbon::parse($this->end_date, config('app.timezone'))->setTimezone($user->timezone)->toDateTimeString();

        if ($currentDateTime >= $startDate && $currentDateTime <= $endDate) {
            $isStarted = true;
        }

        if ($currentDateTime > $endDate) {
            $isCompleted = true;
        }

        $challengeRulesArray = array();
        $challengeRulesData  = $this->challengeRules()->get();
        $i                   = 0;
        foreach ($challengeRulesData as $key => $value) {
            $challengeRulesArray[$i]['targetId'] = $value->challenge_target_id;
            if ($value->challenge_target_id == 4 && !empty($value->model_id)) {
                $challengeRulesArray[$i]['exerciseId'] = $value->model_id;
            }
            $challengeRulesArray[$i]['value'] = $value->target;
            $challengeRulesArray[$i]['uom']   = $value->uom;

            $i++;
        }

        return [
            'id'                => $this->id,
            'title'             => $this->title,
            'description'       => (!empty($this->description)) ? $this->description : "",
            'image'             => $this->getMediaData('logo', ['w' => 1280, 'h' => 640]),
            'startDateTime'     => Carbon::parse($this->start_date, config('app.timezone'))->setTimezone($user->timezone)->toAtomString(),
            'endDateTime'       => Carbon::parse($this->end_date, config('app.timezone'))->setTimezone($user->timezone)->toAtomString(),
            'membersTotal'      => (!empty($membersCount)) ? $membersCount : 0,
            'isStarted'         => $isStarted,
            'isCompleted'       => $isCompleted,
            'isOpen'            => (!$this->close) ,
            'creator'           => $this->getCreatorData(),
            'badges'            => new BadgeListCollection($badgeData),
            'challengeCategory' => array("id" => $this->challenge_category_id, "name" => $this->challengeCatName),
            'members'           => $members,
            'rules'             => $challengeRulesArray,
            'type'              => $this->challenge_type,
        ];
    }
}
