<?php

namespace App\Http\Resources\V43;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class UserAutoFillResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $userProfile = $this->profile;
        $userTeam    = $this->teams()->first();
        $userCompany = $this->company()->first();
        $userDept    = $this->department()->first();
        $appTimezone = config('app.timezone');
        $timezone    = (!empty($this->timezone) ? $this->timezone : $appTimezone);
        $now         = now($timezone)->toDateTimeString();

        $userWeightHistory = $this->weights()
            ->select('user_weight.id', 'user_weight.weight')
            ->orderByDesc('user_weight.log_date')
            ->limit(1)
            ->first();

        $team        = $dept        = $company        = [];
        $companyCode = "";
        $now         = \now()->setTime(0, 0, 0);
        if (!empty($userTeam)) {
            $team['id']   = $userTeam->getKey();
            $team['name'] = $userTeam->name;

            $company['id']   = $userCompany->getKey();
            $company['name'] = $userCompany->name;
            $companyCode     = $userCompany->code;

            $dept['id']   = $userDept->getKey();
            $dept['name'] = $userDept->name;
        }

        $age           = ((!empty($userProfile->birth_date)) ? $now->diffInYears($userProfile->birth_date) : 0);
        $team          = $this->teams()->select('teams.id', 'teams.name', 'teams.department_id', 'teams.default')->first();
        $department    = $team->department()->select('departments.id', 'departments.name')->first();
        $location      = $team->teamlocation()->select('company_locations.id', 'company_locations.name')->first();
        $allowEditTeam = true;

        if (!$team->default) {
            $chInvolvedTeams = [];

            // get ongoing + upcoming challenge ids
            $challenge = $userCompany->challenge()
                ->select('challenges.id', 'challenges.challenge_type')
                ->where('challenges.cancelled', false)
                ->where('challenges.finished', false)
                ->whereNotIn('challenges.challenge_type', ['inter_company', 'individual'])
                ->where(function ($query) use ($now, $appTimezone, $timezone) {
                    $query
                        ->whereRaw("CONVERT_TZ(challenges.start_date, ?, ?)  <= ? AND CONVERT_TZ(challenges.end_date, ?, ?) >= ?",[
                            $appTimezone,$timezone,$now,$appTimezone,$timezone,$now
                        ])
                        ->orWhereRaw("CONVERT_TZ(challenges.start_date, ?, ?)  >= ? AND CONVERT_TZ(challenges.end_date, ?, ?) >= ?",[
                            $appTimezone,$timezone,$now,$appTimezone,$timezone,$now
                        ]);
                })
                ->groupBy('challenges.id')
                ->get();

            // get ongoing + upcoming inter_company challenge ids
            $icChallenge = $userCompany->icChallenge()
                ->select('challenges.id', 'challenges.challenge_type')
                ->where('challenges.cancelled', false)
                ->where('challenges.finished', false)
                ->where(function ($query) use ($now, $appTimezone, $timezone) {
                    $query
                        ->whereRaw("CONVERT_TZ(challenges.start_date, ?, ?)  <= ? AND CONVERT_TZ(challenges.end_date, ?, ?) >= ?",[
                            $appTimezone,$timezone,$now,$appTimezone,$timezone,$now
                        ])
                        ->orWhereRaw("CONVERT_TZ(challenges.start_date, ?, ?)  >= ? AND CONVERT_TZ(challenges.end_date, ?, ?) >= ?",[
                            $appTimezone,$timezone,$now,$appTimezone,$timezone,$now
                        ]);
                })
                ->groupBy('challenges.id')
                ->get();

            $challenge = $challenge->merge($icChallenge);

            // get involved team ids
            if (!empty($challenge)) {
                foreach ($challenge as $key => $challenge) {
                    $chTeams = $challenge->memberTeams()
                        ->select('teams.id')
                        ->where('teams.default', false)
                        ->where('teams.company_id', $userCompany->id)
                        ->get()->pluck('', 'id')->toArray();
                    $chInvolvedTeams = ($chInvolvedTeams + $chTeams);
                }
                $chInvolvedTeams = array_keys($chInvolvedTeams);
            }

            // check if any ongoing + upcoming challenge then disable loc/dept/team
            $allowEditTeam = !in_array($team->id, $chInvolvedTeams);
        }

        $profileImage    = $this->getMediaData('logo', ['w' => 512, 'h' => 512, 'zc' => 3]);
        $profileImageSet = false;
        if (isset($profileImage['isProfileImageSet'])) {
            $profileImageSet = $profileImage['isProfileImageSet'];
            unset($profileImage['isProfileImageSet']);
        }

        $return = [
            'id'                => $this->id,
            'firstName'         => $this->first_name,
            'lastName'          => $this->last_name,
            'email'             => $this->email,
            'isProfileImageSet' => $profileImageSet,
            'profileImage'      => $profileImage,
            'coverImage'        => $this->getMediaData('coverImage', ['w' => 1280, 'h' => 640, 'zc' => 3]),
            'dob'               => $userProfile->birth_date->toDateString(),
            'age'               => $age,
            'height'            => $userProfile->height,
            'weight'            => (!empty($userWeightHistory->weight) ? $userWeightHistory->weight : 50),
            'location'          => $userProfile->location,
            'gender'            => $userProfile->gender,
            'expirationDate'    => Carbon::parse($userCompany->subscription_end_date, config('app.timezone'))->setTimezone($this->timezone)->toAtomString(),
            'team'              => $team,
            'company'           => $company,
            'department'        => $dept,
            'location'          => [
                'id'   => $location->id,
                'name' => $location->name,
            ],
            'department'        => [
                'id'   => $department->id,
                'name' => $department->name,
            ],
            'team'              => [
                'id'   => $team->id,
                'name' => $team->name,
            ],
            'allowEditTeam'     => $allowEditTeam,
            'companyCode'       => $companyCode,
        ];

        return $return;
    }
}
