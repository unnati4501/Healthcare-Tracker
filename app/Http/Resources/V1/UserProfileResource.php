<?php

namespace App\Http\Resources\V1;

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
        $userProfile = UserProfile::where('user_id', $this->id)->first();
        $userTeam    = $this->teams()->first();
        $userCompany = $this->company()->first();
        $userDept    = $this->department()->first();

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

        return [
            'id'                       => $this->id,
            'firstName'                => $this->first_name,
            'lastName'                 => $this->last_name,
            'about'                    => $userProfile->about,
            'email'                    => $this->email,
            'profileImage'             => $this->getMediaData('logo', ['w' => 512, 'h' => 512]),
            'coverImage'               => $this->getMediaData('coverImage', ['w' => 1280, 'h' => 640, 'zc' => 3]),
            'isPremium'                => (($this->is_premium) ),
            'unReadNotificationCount'  => $this->un_read_notification_count,
            'unReadMessageCount'       => $this->userUnreadMsgCount(),
            'dob'                      => $userProfile->birth_date,
            // 'age'                      => $userProfile->age,
            'age'                      => $age,
            'location'                 => $userProfile->location,
            'gender'                   => $userProfile->gender,
            'expirationDate'           => '2025-01-01',
            'team'                     => $team,
            'company'                  => $company,
            'department'               => $dept,
            'badges'                   => $this->badges()->wherePivot('status', 'Active')->count(),
            'stepLastSyncDateTime'     => (!empty($this->step_last_sync_date_time)) ? Carbon::parse($this->step_last_sync_date_time, config('app.timezone'))->setTimezone($this->timezone)->toAtomString() : "",
            'exerciseLastSyncDateTime' => (!empty($this->exercise_last_sync_date_time)) ? Carbon::parse($this->exercise_last_sync_date_time, config('app.timezone'))->setTimezone($this->timezone)->toAtomString() : "",
            'registrationDate'         => Carbon::parse($this->created_at, config('app.timezone'))->setTimezone($this->timezone)->toAtomString(),
        ];
    }
}
