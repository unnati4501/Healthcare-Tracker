<?php

namespace App\Http\Resources\V1;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $userTeam    = $this->teams()->first();
        $userCompany = $this->company()->first();
        $userDept    = $this->department()->first();

        $team = $dept = $company = [];
        if (!empty($userTeam)) {
            $team['id']   = $userTeam->getKey();
            $team['name'] = $userTeam->name;

            $company['id']   = $userCompany->getKey();
            $company['name'] = $userCompany->name;

            $dept['id']   = $userDept->getKey();
            $dept['name'] = $userDept->name;
        }

        return [
            'id'                       => $this->id,
            'name'                     => $this->full_name,
            'email'                    => $this->email,
            'isIntercomEnabled'        => (!empty($userCompany) && $userCompany->is_intercom ) ,
            'healthScoreAvailable'     => (!empty($this->hs_show_banner) && $this->hs_show_banner ) ,
            'isPremium'                => ($this->is_premium) ,
            'unReadNotificationCount'  => $this->userUnreadNotifactionCount(),
            'unReadMessageCount'       => $this->userUnreadMsgCount(),
            'stepLastSyncDateTime'     => (!empty($this->step_last_sync_date_time)) ? Carbon::parse($this->step_last_sync_date_time, config('app.timezone'))->setTimezone($this->timezone)->toAtomString() : "",
            'exerciseLastSyncDateTime' => (!empty($this->exercise_last_sync_date_time)) ? Carbon::parse($this->exercise_last_sync_date_time, config('app.timezone'))->setTimezone($this->timezone)->toAtomString() : "",
            'registrationDate'         => Carbon::parse($this->created_at, config('app.timezone'))->setTimezone($this->timezone)->toAtomString(),
            'team'                     => $team,
            'company'                  => $company,
            'department'               => $dept,
            'userRestriction'          => (!empty($userCompany)) ? $userCompany->group_restriction : 0,
        ];
    }
}
