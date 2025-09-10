<?php

namespace App\Jobs;

use App\Models\Badge;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserGoal;
use App\Notifications\SystemAutoNotification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AwardDailyBadgeToUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var User $user
     */
    protected $user;

    /**
     * @var String $dateInUTC
     * It should be always in UTC
     */
    protected $dateInUTC;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, $dateInUTC = null)
    {
        $this->queue     = 'notifications';
        $this->user      = $user;
        $this->dateInUTC = $dateInUTC;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $appTimezone   = config('app.timezone');
        $userTimezone  = $this->user->timezone;
        $uesrCreatedAt = Carbon::parse($this->user->created_at, $appTimezone)->setTimezone($userTimezone)->toDateString();
        $logDate       = now($userTimezone)->toDateString();

        // check is date is passed for check entries for specified date, if not passed then use current date
        if (!is_null($this->dateInUTC)) {
            $logDate = Carbon::parse($this->dateInUTC, $appTimezone)->setTimezone($userTimezone)->toDateString();
        }

        $from          = $logDate . ' 00:00:00';
        $to            = $logDate . ' 23:59:59';
        $userBadgeData = \DB::table('badge_user')
            ->leftJoin('badges', 'badges.id', '=', 'badge_user.badge_id')
            ->where('badges.type', 'daily')
            ->where('badges.is_default', true)
            ->where("user_id", $this->user->id)
            ->whereBetween('date_for', [$from, $to])
            ->count();

        if ($userBadgeData > 0) {
            return;
        }

        // fetch all expirable badges on daily basis of data for user
        $userDailyData = $this->user->steps()
            ->whereRaw("DATE(CONVERT_TZ(user_step.log_date, ?, ?)) = ?",[
                $appTimezone,$userTimezone,$logDate
            ])
            ->whereRaw("DATE(CONVERT_TZ(user_step.log_date, ?, ?)) >= ?",[
                $appTimezone,$userTimezone,$uesrCreatedAt
            ])
            ->sum('steps');

        // User daily steps
        $userDailySteps = UserGoal::select('steps')->where('user_id', $this->user->id)->first();
        $steps          = config('zevolifesettings.goalSteps');
        if (!empty($userDailySteps) && $userDailySteps->steps != null) {
            $steps = $userDailySteps->steps;
        }

        if ($userDailyData >= $steps) {
            $sendPush             = (now($appTimezone)->toDateString() == $logDate);
            $title                = trans("notifications.badge.daily.title");
            $message              = trans("notifications.badge.daily.message");
            $isMobile             = config('notification.general_badges.daily.is_mobile');
            $isPortal             = config('notification.general_badges.daily.is_portal');
            $notification_setting = $this->user->notificationSettings()
                ->select('flag')
                ->where('flag', 1)
                ->where(function ($query) {
                    $query->where('module', '=', 'badges')
                        ->orWhere('module', '=', 'all');
                })
                ->first();

            $dailyBadge = Badge::where('type', 'daily')->where('is_default', true)->first();

            $dailyBadge->badgeusers()->attach($this->user, [
                'status'     => 'Active',
                'date_for'   => $this->dateInUTC,
                'created_at' => $this->dateInUTC,
                'steps'      => $steps,
            ]);

            if ($logDate >= now($userTimezone)->toDateString()) {
                $message = str_replace(["#first_name#"], [$this->user->first_name], $message);
                $message = str_replace(["#daily_step#"], [$steps], $message);

                $deepLinkId = \DB::table('badge_user')
                    ->where("badge_id", $dailyBadge->id)
                    ->where("user_id", $this->user->id)
                    ->where("status", 'Active')
                    ->orderBy('id', 'DESC')
                    ->pluck('id')
                    ->first();

                $deep_link_uri = __(config('zevolifesettings.deeplink_uri.badge'), [
                    'badge_id' => (!empty($deepLinkId) ? $deepLinkId : 0),
                ]);

                $notification = Notification::create([
                    'type'             => 'Auto',
                    'creator_id'       => $dailyBadge->creator_id,
                    'company_id'       => $dailyBadge->company_id,
                    'creator_timezone' => $this->user->timezone,
                    'title'            => $title,
                    'message'          => $message,
                    'push'             => (!empty($notification_setting) && $notification_setting->flag && $sendPush),
                    'scheduled_at'     => now()->toDateTimeString(),
                    'deep_link_uri'    => $deep_link_uri,
                    'is_mobile'        => $isMobile,
                    'is_portal'        => $isPortal,
                    'tag'              => 'badge',
                ]);

                $this->user->notifications()->attach($notification, ['sent' => true, 'sent_on' => now()->toDateTimeString()]);

                if ($sendPush && !empty($notification_setting) && $notification_setting->flag) {
                    // send notification to user
                    \Notification::send(
                        $this->user,
                        new SystemAutoNotification($notification, 'badge-alert')
                    );
                }
            }
        }
    }
}
