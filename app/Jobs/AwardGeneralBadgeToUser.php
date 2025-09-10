<?php

namespace App\Jobs;

use App\Models\Badge;
use App\Models\Notification;
use App\Models\User;
use App\Notifications\SystemAutoNotification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AwardGeneralBadgeToUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var User $user
     */
    protected $user;

    /**
     * @var String $stringData
     */
    protected $stringData;

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
    public function __construct(User $user, $string, $dateInUTC = null)
    {
        $this->queue     = 'notifications';
        $this->user      = $user;
        $this->stringData    = $string;
        $this->dateInUTC = $dateInUTC;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $notification_setting = $this->user->notificationSettings()
            ->select('flag')
            ->where('flag', 1)
            ->where(function ($query) {
                $query->where('module', '=', 'badges')
                    ->orWhere('module', '=', 'all');
            })
            ->first();
        $appTimezone   = config('app.timezone');
        $userTimezone  = $this->user->timezone;
        $string        = $this->stringData;
        $title         = trans("notifications.badge.$string.title");
        $now           = now($appTimezone);
        $logDate       = now($userTimezone)->toDateString();
        $uesrCreatedAt = Carbon::parse($this->user->created_at, $appTimezone)->setTimezone($userTimezone)->toDateString();

        // check is date is passed for check entries for specified date, if not passed then use current date
        if (!is_null($this->dateInUTC)) {
            $logDate = Carbon::parse($this->dateInUTC, $appTimezone)->setTimezone($userTimezone)->toDateString();
        } else {
            $this->dateInUTC = now($appTimezone)->toDateTimeString();
        }

        $sendPush = ($now->toDateString() == $logDate);

        // get and award exercise badges to user if applicable
        if ($this->stringData == 'exercises') {
            // fetch all expirable badges on daily basis of data for user
            $userDailyData = $this->user->exercises()
                ->select('user_exercise.exercise_id', \DB::raw('SUM(user_exercise.duration) as duration'), \DB::raw('SUM(user_exercise.distance) as distance'))
                ->whereRaw("DATE(CONVERT_TZ(user_exercise.start_date, ?, ?)) = ?",[
                    $appTimezone,$userTimezone,$logDate
                ])
                ->whereRaw("DATE(CONVERT_TZ(user_exercise.start_date, ?, ?)) >= ?",[
                    $appTimezone, $userTimezone, $uesrCreatedAt
                ])
                ->groupBy('user_exercise.exercise_id')
                ->get()->toArray();

            // check if daily data is not empty
            if (!empty($userDailyData)) {
                foreach ($userDailyData as $value) {
                    // get all duration based badge for current type of exercise
                    $durationBasedBadges = Badge::join('challenge_targets', 'challenge_targets.id', '=', 'badges.challenge_target_id')
                        ->select("badges.*")
                        ->where("badges.target", "<=", (int) ($value['duration'] / 60))
                        ->where("challenge_targets.short_name", 'exercises')
                        ->where("badges.uom", 'minutes')
                        ->where("badges.type", 'general')
                        ->where("badges.model_id", (int) $value['exercise_id'])
                        ->where("badges.model_name", 'exercise')
                        ->get();

                    // get all distance based badge for current type of exercise
                    $distanceBasedBadges = Badge::join('challenge_targets', 'challenge_targets.id', '=', 'badges.challenge_target_id')
                        ->select("badges.*")
                        ->where("badges.target", "<=", (int) $value['distance'])
                        ->where("challenge_targets.short_name", 'exercises')
                        ->where("badges.uom", 'meter')
                        ->where("badges.type", 'general')
                        ->where("badges.model_id", (int) $value['exercise_id'])
                        ->where("badges.model_name", 'exercise')
                        ->get();

                    $expirableBadges1 = $durationBasedBadges->merge($distanceBasedBadges);
                    if (!empty($expirableBadges)) {
                        $expirableBadges = $expirableBadges->merge($expirableBadges1);
                    } else {
                        $expirableBadges = $expirableBadges1;
                    }
                }
            }

            if (!empty($expirableBadges) && $expirableBadges->count() > 0) {
                $badgeData = $expirableBadges;
            }

            $isMobile = config('notification.general_badges.steps.is_mobile');
            $isPortal = config('notification.general_badges.steps.is_portal');

            if (!empty($badgeData) && $badgeData->count() > 0) {
                foreach ($badgeData as $badge) {
                    $userBadgeData = $this->user->badges()
                        ->select('badge_user.id')
                        ->wherePivot("badge_id", $badge->id)
                        ->wherePivot("user_id", $this->user->id)
                        ->whereRaw("DATE(CONVERT_TZ(badge_user.date_for, ?, ?)) = ?",[
                            $appTimezone, $userTimezone, $logDate
                        ])
                        ->wherePivot('status', 'Active')
                        ->first();
                    if (empty($userBadgeData)) {
                        $badgeInput = [
                            'date_for'   => $this->dateInUTC,
                            'status'     => "Active",
                            'created_at' => $this->dateInUTC,
                        ];
                        $badge->badgeusers()->attach($this->user, $badgeInput);

                        $message = trans("notifications.badge.$string.message");
                        $message = str_replace(["#badge_name#"], [$badge->title], $message);

                        $deepLinkId = \DB::table('badge_user')
                            ->where("badge_id", $badge->id)
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
                            'creator_id'       => $badge->creator_id,
                            'company_id'       => $badge->company_id,
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
        } else {
            if ($this->stringData == 'steps') {
                $isMobile = config('notification.general_badges.steps.is_mobile');
                $isPortal = config('notification.general_badges.steps.is_portal');
                // fetch all expirable badges on daily basis of data for user
                $userDailyData = $this->user->steps()
                    ->whereRaw("DATE(CONVERT_TZ(user_step.log_date, ?, ?)) = ?",[
                        $appTimezone, $userTimezone, $logDate
                    ])
                    ->whereRaw("DATE(CONVERT_TZ(user_step.log_date, ?, ?)) >= ?",[
                        $appTimezone, $userTimezone, $uesrCreatedAt
                    ])
                    ->sum('steps');

                $expirableBadges = Badge::join('challenge_targets', 'challenge_targets.id', '=', 'badges.challenge_target_id')
                    ->select("badges.*")
                    ->where("badges.type", 'general')
                    ->where("badges.target", "<=", $userDailyData)
                    ->where("challenge_targets.short_name", 'steps')
                    ->get();
            } elseif ($this->stringData == 'distance') {
                $isMobile = config('notification.general_badges.distance.is_mobile');
                $isPortal = config('notification.general_badges.distance.is_portal');
                // fetch all permenant badges on total data for user
                $userTotalData = $this->user->steps()
                    ->whereRaw("DATE(CONVERT_TZ(user_step.log_date, ?, ?)) >= ?",[
                        $appTimezone, $userTimezone, $uesrCreatedAt
                    ])
                    ->sum('distance');

                $expirableBadges = Badge::join('challenge_targets', 'challenge_targets.id', '=', 'badges.challenge_target_id')
                    ->select("badges.*")
                    ->where("badges.type", 'general')
                    ->where("badges.target", "<=", $userTotalData)
                    ->where("challenge_targets.short_name", 'distance')
                    ->get();
            } elseif ($this->stringData == 'meditations') {
                $isMobile = config('notification.general_badges.meditation.is_mobile');
                $isPortal = config('notification.general_badges.meditation.is_portal');

                $userDailyData = $this->user->completedMeditationTracks()
                    ->whereRaw("DATE(CONVERT_TZ(user_listened_tracks.created_at, ?, ?)) = ?",[
                        $appTimezone, $userTimezone, $logDate
                    ])
                    ->where("user_listened_tracks.created_at", ">=", $this->user->created_at)
                    ->count();

                $expirableBadges = Badge::join('challenge_targets', 'challenge_targets.id', '=', 'badges.challenge_target_id')
                    ->select("badges.*")
                    ->where("badges.type", 'general')
                    ->where("badges.target", "<=", $userDailyData)
                    ->where("challenge_targets.short_name", 'meditations')
                    ->get();
            }

            if (!empty($expirableBadges) && $expirableBadges->count() > 0) {
                $badgeData = $expirableBadges;
            }

            if (!empty($badgeData) && $badgeData->count() > 0) {
                foreach ($badgeData as $badge) {
                    $insertFlag = true;

                    if ($this->stringData == 'distance') {
                        $userBadgeData = $this->user->badges()
                            ->select('badge_user.id')
                            ->wherePivot("badge_id", $badge->id)
                            ->wherePivot("user_id", $this->user->id)
                            ->wherePivot('status', 'Active')
                            ->first();
                        $insertFlag = !(!empty($userBadgeData));
                    } else {
                        $userBadgeData = $this->user->badges()
                            ->select('badge_user.id')
                            ->wherePivot("badge_id", $badge->id)
                            ->wherePivot("user_id", $this->user->id)
                            ->wherePivot('status', 'Active')
                            ->when($this->stringData, function ($query, $string) use ($logDate, $appTimezone, $userTimezone) {
                                if ($string == 'steps' || $string == 'meditations') {
                                    $query->whereRaw("DATE(CONVERT_TZ(badge_user.date_for, ?, ?)) = ?",[
                                        $appTimezone,$userTimezone,$logDate
                                    ]);
                                } else {
                                    $query->whereRaw(
                                        "DATE(CONVERT_TZ(badge_user.created_at, ?, ?)) = ?"
                                    ,[
                                        $appTimezone,$userTimezone,now($appTimezone)->toDateString()
                                    ]);
                                }
                            })
                            ->first();
                        $insertFlag = !(!empty($userBadgeData));
                    }

                    if ($insertFlag) {
                        $badge->badgeusers()->attach($this->user, [
                            'status'     => 'Active',
                            'date_for'   => $this->dateInUTC,
                            'created_at' => $this->dateInUTC,
                        ]);

                        $message = trans("notifications.badge.$string.message");
                        $message = str_replace(["#badge_name#"], [$badge->title], $message);

                        $deepLinkId = \DB::table('badge_user')
                            ->where("badge_id", $badge->id)
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
                            'creator_id'       => $badge->creator_id,
                            'company_id'       => $badge->company_id,
                            'creator_timezone' => $this->user->timezone,
                            'title'            => $title,
                            'message'          => $message,
                            'push'             => ($sendPush && !empty($notification_setting) && $notification_setting->flag),
                            'scheduled_at'     => now()->toDateTimeString(),
                            'deep_link_uri'    => $deep_link_uri,
                            'is_mobile'        => $isMobile,
                            'is_portal'        => $isPortal,
                            'tag'              => 'badge',
                        ]);

                        $this->user->notifications()->attach($notification, ['sent' => true, 'sent_on' => now()->toDateTimeString()]);

                        // added condition to send notification if badge is being awarded for current day only
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
    }
}
