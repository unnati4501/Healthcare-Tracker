<?php

namespace App\Http\Resources\V6;

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

        $return = [
            'id'                       => $this->user->id,
            'firstName'                => $this->user->first_name,
            'lastName'                 => $this->user->last_name,
            'about'                    => $userProfile->about,
            'email'                    => $this->user->email,
            'profileImage'             => $this->user->getMediaData('logo', ['w' => 512, 'h' => 512]),
            'coverImage'               => $this->user->getMediaData('coverImage', ['w' => 1280, 'h' => 640, 'zc' => 3]),
            'isPremium'                => (($this->user->is_premium) ? true : false),
            'unReadNotificationCount'  => $this->user->un_read_notification_count,
            'unReadMessageCount'       => $this->user->userUnreadMsgCount(),
            'dob'                      => $userProfile->birth_date,
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

        ];

        if (!empty($this->lastSubmittedSurvey)) {
            $return['lastSubmittedSurvey'] = $this->lastSubmittedSurvey;
        }

        return $return;
    }
}
