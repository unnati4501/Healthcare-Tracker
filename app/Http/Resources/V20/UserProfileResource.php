<?php

namespace App\Http\Resources\V20;

use App\Models\Goal;
use App\Models\UserProfile;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $userProfile = UserProfile::where('user_id', $this->user->id)->first();
        $userTeam    = $this->user->teams()->first();
        $userCompany = $this->user->company()->first();
        $userDept    = $this->user->department()->first();
        $appTimezone = config('app.timezone');
        $timezone    = (!empty($this->user->timezone) ? $this->user->timezone : $appTimezone);
        $now         = now($timezone)->toDateTimeString();

        $team = $dept = $company = [];
        $now  = \now()->setTime(0, 0, 0);
        if (!empty($userTeam)) {
            $team['id']   = $userTeam->getKey();
            $team['name'] = $userTeam->name;

            $company['id']   = $userCompany->getKey();
            $company['name'] = $userCompany->name;

            $dept['id']   = $userDept->getKey();
            $dept['name'] = $userDept->name;
        }

        $age = ((!empty($userProfile->birth_date)) ? $now->diffInYears($userProfile->birth_date) : 0);

        $showRecommendation = true;
        $goalObj            = new Goal();
        $goalRecords        = $goalObj->getAssociatedGoalTags();
        if ($goalRecords->count() > 0) {
            $showRecommendation = true;
        } else {
            $showRecommendation = false;
        }

        $team       = $this->user->teams()->select('teams.id', 'teams.name', 'teams.department_id', 'teams.default')->first();
        $department = $team->department()->select('departments.id', 'departments.name')->first();
        $location   = $team->teamlocation()->select('company_locations.id', 'company_locations.name')->first();

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
                        ->whereRaw("CONVERT_TZ(challenges.start_date, '{$appTimezone}', '{$timezone}')  <= '{$now}' AND CONVERT_TZ(challenges.end_date, '{$appTimezone}', '{$timezone}') >= '{$now}'")
                        ->orWhereRaw("CONVERT_TZ(challenges.start_date, '{$appTimezone}', '{$timezone}')  >= '{$now}' AND CONVERT_TZ(challenges.end_date, '{$appTimezone}', '{$timezone}') >= '{$now}'");
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
                        ->whereRaw("CONVERT_TZ(challenges.start_date, '{$appTimezone}', '{$timezone}')  <= '{$now}' AND CONVERT_TZ(challenges.end_date, '{$appTimezone}', '{$timezone}') >= '{$now}'")
                        ->orWhereRaw("CONVERT_TZ(challenges.start_date, '{$appTimezone}', '{$timezone}')  >= '{$now}' AND CONVERT_TZ(challenges.end_date, '{$appTimezone}', '{$timezone}') >= '{$now}'");
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

        $profileImage    = $this->user->getMediaData('logo', ['w' => 512, 'h' => 512, 'zc' => 3]);
        $profileImageSet = false;
        if (isset($profileImage['isProfileImageSet'])) {
            $profileImageSet = $profileImage['isProfileImageSet'];
            unset($profileImage['isProfileImageSet']);
        }

        $return = [
            'id'                       => $this->user->id,
            'firstName'                => $this->user->first_name,
            'lastName'                 => $this->user->last_name,
            'about'                    => $userProfile->about,
            'email'                    => $this->user->email,
            'isProfileImageSet'        => $profileImageSet,
            'profileImage'             => $profileImage,
            'coverImage'               => $this->user->getMediaData('coverImage', ['w' => 1280, 'h' => 640, 'zc' => 3]),
            'isPremium'                => (($this->user->is_premium ) ? true : false),
            'unReadNotificationCount'  => (!empty($this->user->un_read_notification_count) ? $this->user->un_read_notification_count : 0),
            'unReadMessageCount'       => $this->user->userUnreadMsgCount(),
            'dob'                      => Carbon::parse($userProfile->birth_date)->toDateString(),
            'age'                      => $age,
            'location'                 => $userProfile->location,
            'gender'                   => $userProfile->gender,
            'expirationDate'           => Carbon::parse($userCompany->subscription_end_date, config('app.timezone'))->setTimezone($this->user->timezone)->toAtomString(),
            'team'                     => $team,
            'company'                  => $company,
            'department'               => $dept,
            'badges'                   => $this->user->badges()->wherePivot('status', 'Active')->count(),
            'stepLastSyncDateTime'     => (!empty($this->user->step_last_sync_date_time)) ? Carbon::parse($this->user->step_last_sync_date_time, config('app.timezone'))->setTimezone($this->user->timezone)->toAtomString() : "",
            'exerciseLastSyncDateTime' => (!empty($this->user->exercise_last_sync_date_time)) ? Carbon::parse($this->user->exercise_last_sync_date_time, config('app.timezone'))->setTimezone($this->user->timezone)->toAtomString() : "",
            'registrationDate'         => Carbon::parse($this->user->created_at, config('app.timezone'))->setTimezone($this->user->timezone)->toAtomString(),
            'location'                 => [
                'id'   => $location->id,
                'name' => $location->name,
            ],
            'department'               => [
                'id'   => $department->id,
                'name' => $department->name,
            ],
            'team'                     => [
                'id'   => $team->id,
                'name' => $team->name,
            ],
            'allowEditTeam'            => $allowEditTeam,
        ];

        if (!empty($this->lastSubmittedSurvey)) {
            $return['lastSubmittedSurvey'] = $this->lastSubmittedSurvey;
        }
        $return['showRecommendation'] = $showRecommendation;

        return $return;
    }
}
