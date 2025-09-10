<?php

namespace App\Http\Resources\V17;

use App\Http\Resources\V12\GethelpResource;
use App\Models\AppSetting;
use App\Models\AppSlide;
use App\Models\AppTheme;
use App\Models\Goal;
use App\Models\UserNpsLogs;
use App\Models\ZcSurveyLog;
use App\Models\ZcSurveyResponse;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Schema;

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
        $xDeviceOs                = strtolower($request->header('X-Device-Os', ""));
        $type                     = ($xDeviceOs == config('zevolifesettings.PORTAL')) ? "portal" : "app";
        $userTeam                 = $this->teams()->first();
        $userCompany              = $this->company()->first();
        $userDept                 = $this->department()->first();
        $surveyDisplay            = false;
        $wellbeingSurveySubmitted = false;
        $surveyIdDisplay          = false;
        $goalObj                  = new Goal();
        $goalRecords              = $goalObj->getAssociatedGoalTags()->count();
        $goalSelectedCount        = $this->userGoalTags()->pluck("goals.id")->count();
        $goalDisplay              = ($goalRecords <= 0 || $goalSelectedCount >= 1) ? false : true;
        $goalMappedCount          = (($goalRecords > 0) );
        $appSlide                 = (AppSlide::where('type', $type)->count() > 0) ;
        $appTheme                 = [];
        $csatAvailable            = false;
        $userProfile              = $this->profile;

        // App Theme Related changes
        if ($xDeviceOs != config('zevolifesettings.PORTAL')) {
            $appThemeFile          = config('zevolifesettings.app_theme_path');
            $companywiseAppSetting = $userCompany->companywiseAppSetting()->get()->pluck('value', 'key')->toArray();
            $defaultAppSetting     = AppSetting::all()->pluck('value', 'key')->toArray();
            $defaultAppThemeName   = (!empty($defaultAppSetting) && isset($defaultAppSetting['app_theme'])) ? $defaultAppSetting['app_theme'] : 'dark';
            $appThemeName          = (!empty($companywiseAppSetting) && isset($companywiseAppSetting['app_theme'])) ? $companywiseAppSetting['app_theme'] : $defaultAppThemeName;

            if (Schema::hasTable('app_themes')) {
                $appTheme         = AppTheme::where('slug', $appThemeName)->first();
                $path             = $appTheme->getFirstMediaPath('theme');
                $folder           = config('filesystems.disks.spaces.root');
                $path             = (!empty($path) ? "{$folder}/{$path}" : (isset($appThemeFile[$defaultAppThemeName]) ? $appThemeFile[$defaultAppThemeName] : $appThemeFile['dark']));
                $companyThemeJson = readFileToSpaces($path);
            } else {
                $appThemeName     = (isset($appThemeFile[$appThemeName]) ? $appThemeFile[$appThemeName] : $appThemeFile['dark']);
                $companyThemeJson = readFileToSpaces($appThemeName);
            }

            $companyThemeJson = (!empty($companyThemeJson)) ? json_decode($companyThemeJson) : [];
            $appTheme         = $companyThemeJson;
        }

        $timezone    = (!empty($this->timezone) ? $this->timezone : config('app.timezone'));
        $todayDate   = Carbon::now()->setTimezone($timezone)->toDateTimeString();
        $zcsurveylog = ZcSurveyLog::select('id')
            ->where('company_id', $userCompany->id)
            ->where('roll_out_date', '<=', $todayDate)
            ->where('expire_date', '>=', $todayDate)
            ->first();

        if ($zcsurveylog) {
            $zcSurveyUserLog = ZcSurveyResponse::where('user_id', $this->id)
                ->where('company_id', $userCompany->id)
                ->count();

            $surveyDisplay = ($zcSurveyUserLog <= 0 && $this->start_date <= $todayDate) ;
        }

        if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
            $surveySubmittedCount = ZcSurveyResponse::where('user_id', $this->id)
                ->where('company_id', $userCompany->id)
                ->count();
            $wellbeingSurveySubmitted = ($surveySubmittedCount > 0) ;

            $userNpsLogs = UserNpsLogs::select('id')
                ->where('user_id', $this->id)
                ->where('survey_sent_on', '<=', $todayDate)
                ->where('survey_received_on', null)
                ->first();

            $csatAvailable = (!empty($userNpsLogs)) ;
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
            'profileImage'             => $this->getMediaData('logo', ['w' => 512, 'h' => 512, 'zc' => 3]),
            'coverImage'               => $this->getMediaData('coverImage', ['w' => 1280, 'h' => 640, 'zc' => 3]),
            'email'                    => $this->email,
            'points'                   => $this->when(($xDeviceOs == config('zevolifesettings.PORTAL')), ($userProfile->points ?? 0)),
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
            'goalsMapped'              => $goalMappedCount,
            'onboardDisplay'           => $appSlide,
            'surveyDisplay'            => $surveyDisplay,
            'surveyLogId'              => (($surveyDisplay ) ? $zcsurveylog->id : 0),
            'eapSetting'               => new GethelpResource($this),
            'wellbeingSurveySubmitted' => $this->when($xDeviceOs == config('zevolifesettings.PORTAL'), $wellbeingSurveySubmitted),
            'appTheme'                 => $this->when($xDeviceOs != config('zevolifesettings.PORTAL'), $appTheme),
            'csatAvailable'            => $this->when($xDeviceOs == config('zevolifesettings.PORTAL'), $csatAvailable),
            'socialId'                 => $this->when(!empty($this->social_id), $this->social_id),
            'socialType'               => $this->when(!empty($this->social_type), (int) $this->social_type),
        ];
    }
}
