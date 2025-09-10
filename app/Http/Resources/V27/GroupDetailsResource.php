<?php

namespace App\Http\Resources\V27;

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

        $categoryData = [
            'id'   => $this->subcategory->id,
            'name' => $this->subcategory->name,
            'slug' => $this->subcategory->short_name,
        ];

        //Get member images of group if group is public
        $memberImages = [];
        if (!empty($this->type) && $this->type == 'public') {
            $memberImages = $this->getMemberImages($this);
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
            'type'        => ((!empty($teamRestriction)) ? 'team' : 'company'),
            'category'    => $categoryData,
            'memberImages'=> (!empty($memberImages)) ? $memberImages : [],
        ];
    }

    /**
     * Get Member images for group
     *
     * @param  $challenge
     * @return array
     */
    private function getMemberImages($group)
    {
        $memberImages = [];
        $members  = $this->members()->wherePivot("status", "Accepted")->get();
        if (!empty($members)) {
            foreach ($members as $key => $value) {
                if (!empty($value)) {
                    $memberImage = $value->getMediaData('logo', ['w' => 60, 'h' => 60, 'zc' => 3, 'ct' => 1, 'mI' => 1]);
                    array_push($memberImages, $memberImage);
                }
            }
        }
        $topFourMembers = array_slice($memberImages, 0, 4);
        return $topFourMembers;
    }
}
