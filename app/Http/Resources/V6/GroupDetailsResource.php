<?php

namespace App\Http\Resources\V6;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupDetailsResource extends JsonResource
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
        $user    = $this->user();
        $team    = $user->teams()->first();
        $company = $user->company()->first();

        $teamRestriction = null;
        if ($this->model_name == 'challenge') {
            $teamRestriction = $this->leftJoin('challenges', 'challenges.id', '=', 'groups.model_id')
                ->where('challenges.challenge_type', 'team')
                ->where('challenges.id', $this->model_id)
                ->first();
        }

        $members = $this->members()
            ->join('user_team', 'user_team.user_id', '=', 'group_members.user_id')
            ->where(function ($query) use ($teamRestriction, $team, $company) {
                if (!empty($teamRestriction)) {
                    $query->where('user_team.team_id', $team->getKey());
                } else {
                    $query->where('user_team.company_id', $company->getKey());
                }
            })
            ->count();

        $membersIds = array();

        $loginUserData = $this->members()->wherePivot("status", "Accepted")->wherePivot("user_id", $user->getKey())->first();

        if ($this->creator_id == $user->getKey()) {
            $membersIds = $this->members()->wherePivot("user_id", "!=", $user->getKey())->get()->pluck('id')->toArray();
        }

        return [
            'id'          => $this->id,
            'name'        => $this->title,
            'description' => (!empty($this->description)) ? $this->description : "",
            'image'       => $this->getMediaData('logo', ['w' => 320, 'h' => 320]),
            'members'     => (!empty($members)) ? $members : 0,
            'membersData' => $membersIds,
            'isMember'    => ((!empty($loginUserData)) ? true : false),
            'muted'       => ((!empty($loginUserData) && $loginUserData->pivot->notification_muted) ? true : false),
            'creator'     => $this->getCreatorData(),
            'type'        => $this->type,
        ];
    }
}
