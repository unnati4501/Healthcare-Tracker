<?php

namespace App\Http\Resources\V10;

use App\Models\AppSlide;
use App\Models\Goal;
use App\Models\ZcSurveyLog;
use App\Models\ZcSurveyResponse;
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
        $type              = ($request->header('X-Device-Os') == config('zevolifesettings.PORTAL')) ? "portal" : "app";
        $userTeam          = $this->teams()->first();
        $userCompany       = $this->company()->first();
        $userDept          = $this->department()->first();
        $surveyDisplay     = false;
        $goalObj           = new Goal();
        $goalRecords       = $goalObj->getAssociatedGoalTags()->count();
        $goalSelectedCount = $this->userGoalTags()->pluck("goals.id")->count();
        $goalDisplay       = ($goalRecords <= 0 || $goalSelectedCount >= 1) ? false : true;
        $appSlide          = (AppSlide::where('type', $type)->count() > 0) ;

        $timezone    = (!empty($this->timezone) ? $this->timezone : config('app.timezone'));
        $todayDate   = Carbon::now()->setTimezone($timezone)->toDateTimeString();
        $zcsurveylog = ZcSurveyLog::where('company_id', $userCompany->id)
            ->where('roll_out_date', '<=', $todayDate)
            ->where('expire_date', '>=', $todayDate)
            ->first();

        if ($zcsurveylog) {
            $zcSurveyUserLog = ZcSurveyResponse::where('survey_log_id', $zcsurveylog->id)
                ->where('user_id', $this->id)
                ->where('company_id', $userCompany->id)
                ->count();

            $surveyDisplay = ($zcSurveyUserLog <= 0) ;
        }

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
            'profileImage'             => $this->getMediaData('logo', ['w' => 512, 'h' => 512]),
            'coverImage'               => $this->getMediaData('coverImage', ['w' => 1280, 'h' => 640, 'zc' => 3]),
            'email'                    => $this->email,
            'isIntercomEnabled'        => (!empty($userCompany) && $userCompany->is_intercom ) ,
            'healthScoreAvailable'     => (!empty($this->hs_show_banner) && $this->hs_show_banner ) ,
            'isPremium'                => ($this->is_premium ) ,
            'unReadNotificationCount'  => $this->userUnreadNotifactionCount(),
            'unReadMessageCount'       => $this->userUnreadMsgCount(),
            'stepLastSyncDateTime'     => (!empty($this->step_last_sync_date_time)) ? Carbon::parse($this->step_last_sync_date_time, config('app.timezone'))->setTimezone($this->timezone)->toAtomString() : "",
            'exerciseLastSyncDateTime' => (!empty($this->exercise_last_sync_date_time)) ? Carbon::parse($this->exercise_last_sync_date_time, config('app.timezone'))->setTimezone($this->timezone)->toAtomString() : "",
            'registrationDate'         => Carbon::parse($this->created_at, config('app.timezone'))->setTimezone($this->timezone)->toAtomString(),
            'team'                     => $team,
            'company'                  => $company,
            'department'               => $dept,
            'userRestriction'          => (!empty($userCompany)) ? $userCompany->group_restriction : 0,
            'goalsSelectedCount'       => $goalSelectedCount,
            'goalDisplay'              => $goalDisplay,
            'onboardDisplay'           => $appSlide,
            'surveyDisplay'            => $surveyDisplay,
        ];
    }
}
