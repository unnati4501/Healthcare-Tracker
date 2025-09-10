<?php

namespace App\Observers;

use App\Models\Challenge;
use App\Models\Team;
use App\Models\UserTeam;

class UserTeamObserver
{
    /**
     * Handle the user team "created" event.
     *
     * @param  App\Models\UserTeam  $userTeam
     * @return void
     */
    public function created(UserTeam $userTeam)
    {
        // check auto team creation is enable for the company
        $company    = $userTeam->company()->select('id', 'auto_team_creation', 'team_limit')->first();
        $department = $userTeam->department()->select('id', 'default')->first();
        if ($company->auto_team_creation && !$department->default) {
            $team         = $userTeam->team()->select('teams.id', 'teams.name')->first();
            $teamLocation = $team->teamlocation()->select('company_locations.id')->first();
            $deptLocation = $department->locations()
                ->select('department_location.id', 'department_location.auto_team_creation_meta')
                ->where('company_location_id', $teamLocation->pivot->company_location_id)
                ->first();
            // get all teams belongs to the same company location and count total members, suppose all the teams are full then create new team as per the name convection
            $teams = $department->locationWiseTeams()
                ->select(
                    \DB::raw("COUNT(team_location.team_id) AS teams_count"),
                    \DB::raw("SUM((SELECT COUNT(user_team.id) FROM user_team WHERE user_team.team_id = teams.id)) AS users_count")
                )
                ->where('team_location.company_location_id', $teamLocation->pivot->company_location_id)
                ->groupBy('team_location.company_location_id')
                ->first();
            // check all the teams are full
            if ($teams->users_count >= ($teams->teams_count * $company->team_limit)) {
                $namingConvention = (
                    (is_null($deptLocation->auto_team_creation_meta))
                    ? $team->name
                    : $deptLocation->auto_team_creation_meta->naming_convention
                );
                // generate sequencing team name
                $name = generateUniqueTeamNames($namingConvention, 1, $userTeam->department_id, $teamLocation->pivot->company_location_id);

                // create new team
                $newTeam = Team::create([
                    'name'          => $name[0],
                    'company_id'    => $userTeam->company_id,
                    'department_id' => $userTeam->department_id,
                ]);

                // assign same company location to newly generated team
                $newTeam->teamlocation()->sync([[
                    'company_location_id' => $teamLocation->pivot->company_location_id,
                    'company_id'          => $userTeam->company_id,
                    'department_id'       => $userTeam->department_id,
                    'team_id'             => $newTeam->id,
                ]]);

                // check if any ongoing company_goal type challenges are running then assign newly added to the each
                $ongoingCompanyGoalChallenges = Challenge::where('company_id', $userTeam->company_id)
                    ->where('challenge_type', 'company_goal')
                    ->where('finished', 0)
                    ->where('cancelled', 0)
                    ->get();
                if (!empty($ongoingCompanyGoalChallenges)) {
                    $ongoingCompanyGoalChallenges->each(function ($query) use ($newTeam) {
                        $query->memberTeams()->attach($newTeam->id);
                    });
                }
            }
        }
    }
}
