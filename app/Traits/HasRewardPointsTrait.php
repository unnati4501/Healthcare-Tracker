<?php

namespace App\Traits;

use App\Models\Company;
use App\Models\User;
use App\Models\UserPointLog;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasRewardPointsTrait
{
    /**
     * Set the polymorphic relation for portal point.
     *
     * @return MorphMany
     */
    public function portalPoints(): MorphMany
    {
        return $this->morphMany(UserPointLog::class, 'model');
    }

    /**
     * Award points to user on completion/view of content(Recipe|Course|Feed|Webinar|MeditationTrack|ZcSurvey)
     *
     * @param \App\Models\User $user
     * @param \App\Models\Company $company
     * @param String $modelType
     * @param Array $meta
     * @return mixed UserPointLog|null
     */
    public function rewardPortalPointsToUser(User $user, Company $company, $modelType, $meta = null)
    {
        $appTimezone       = config('app.timezone');
        $timezone          = (!empty($user->timezone) ? $user->timezone : $appTimezone);
        $now               = now($timezone)->toDateString();
        $defaultDailyLimit = config('zevolifesettings.reward_point_daily_limit');
        $dailyLimit        = $company->companyWisePointsDailyLimit()->select('value')->where('type', $modelType)->first();
        $dailyLimit        = (!empty($dailyLimit) ? $dailyLimit['value'] : $defaultDailyLimit[$modelType]);

        // get today's awared points to put daily limit
        $awaredCounts = $user->rewardPointLogs()
            ->where('user_point_logs.model_type', get_class($this))
            ->whereRaw("DATE(CONVERT_TZ(user_point_logs.created_at, ?, ?)) = ?",[
                $appTimezone,$timezone,$now
            ])
            ->count('user_point_logs.id');

        // assign points if user not reached to daily limit for particular content type
        if (is_null($awaredCounts) || $dailyLimit == 0 || $dailyLimit > $awaredCounts) {
            $defaultPoints = config('zevolifesettings.default_portal_limits');
            $point         = $company->companyWisePointsSetting()->select('value')->where('type', $modelType)->first();
            $point         = ($point->value ?? $defaultPoints[$modelType]);

            // add points to user
            $user->profile()->increment('points', $point);

            // add log for points
            return $this->portalPoints()->create([
                'user_id' => $user->id,
                'point'   => $point,
                'meta'    => $meta,
            ]);
        }
        return null;
    }
}
