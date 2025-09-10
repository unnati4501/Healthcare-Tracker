<?php

namespace App\Jobs;

use App\Models\Badge;
use App\Models\Challenge;
use App\Models\ChallengeOngoingBadgeUsers;
use App\Models\ChallengeParticipant;
use App\Models\Notification;
use App\Models\User;
use App\Notifications\SystemAutoNotification;
use Carbon\Carbon;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AwardOngoingChallengeBadgeToUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var User $user
     */
    protected $user;

    /**
     * @var String $string
     */
    protected $string;

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
        $this->string    = $string;
        $this->dateInUTC = $dateInUTC;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $loggedInuser  = $this->user;
        $appTimezone   = config('app.timezone');
        $userTimezone  = $this->user->timezone;
        $company       = $this->user->company()->first();
        $uesrCreatedAt = Carbon::parse($this->user->created_at, $appTimezone)->setTimezone($userTimezone)->toDateString();
        $logDate       = now($userTimezone)->toDateString();

        // check is date is passed for check entries for specified date, if not passed then use current date
        if (!is_null($this->dateInUTC)) {
            $logDate = Carbon::parse($this->dateInUTC, $appTimezone)->setTimezone($userTimezone)->toDateString();
        } else {
            $this->dateInUTC = now($appTimezone)->toDateTimeString();
        }

        // Challenge Participant Ids
        $challengeIds = ChallengeParticipant::where(function ($query) use ($company) {
            $query->where("user_id", $this->user->id)
                ->orWhere("team_id", $this->user->teams()->first()->id)
                ->orWhere("company_id", $company->getKey());
        })
            ->where("status", "Accepted")
            ->groupBy("challenge_id")
            ->get()
            ->pluck('challenge_id')
            ->toArray();

        // Ongoing challenge for this users
        $getOngoingChallenge = Challenge::leftJoin("challenge_participants", function ($join) {
            $join->on("challenges.id", "=", "challenge_participants.challenge_id")
                ->where("challenge_participants.status", "Accepted");
        })
            ->select(
                'challenges.id',
                'challenges.title',
                'challenges.challenge_type',
                'challenges.start_date',
                'challenges.end_date',
                'challenges.is_badge'
            )
            ->where('challenges.start_date', '<', now($appTimezone)->toDateTimeString())
            ->where('challenges.end_date', '>', now($appTimezone)->toDateTimeString())
            ->where(function ($query) use ($loggedInuser, $company, $challengeIds, $appTimezone) {
                $query->where(function ($subQuery) use ($challengeIds, $appTimezone) {
                    $subQuery->where('challenges.challenge_type', 'individual')
                        ->where(function ($subQuery1) use ($challengeIds, $appTimezone) {
                            $subQuery1->where(function ($subQuery2) use ($challengeIds) {
                                $subQuery2->whereIn("challenges.id", $challengeIds);
                            })->orWhere(function ($subQuery2) use ($appTimezone) {
                                $subQuery2->where("challenges.close", false)
                                    ->whereRaw("CONVERT_TZ(challenges.start_date, ?, ?) > ?",[
                                        'UTC', $appTimezone, now($appTimezone)->toDateTimeString()
                                    ]);
                            });
                        });
                })->orWhere(function ($subQuery) use ($loggedInuser) {
                    $subQuery->whereIn('challenges.challenge_type', ['team', 'company_goal'])
                        ->where('challenge_participants.team_id', $loggedInuser->teams()->first()->id);
                })->orWhere(function ($subQuery) use ($loggedInuser, $company) {
                    $subQuery->where('challenges.challenge_type', 'inter_company')
                        ->where('challenge_participants.team_id', $loggedInuser->teams()->first()->id)
                        ->where('challenge_participants.company_id', $company->getKey());
                });
            })
            ->get();

        // fetch all expirable badges on daily basis of data for user
        $userDailyData = $this->user->steps()
            ->whereRaw("DATE(CONVERT_TZ(user_step.log_date, ?, ?)) = ?",[
                $appTimezone, $userTimezone, $logDate
            ])
            ->whereRaw("DATE(CONVERT_TZ(user_step.log_date, ?, ?)) >= ?",[
                $appTimezone, $userTimezone, $uesrCreatedAt
            ]);

        if ($this->string == 'distance') {
            $userDailyData = $userDailyData->sum('distance');
        } else {
            $userDailyData = $userDailyData->sum('steps');
        }

        $utcDateTime = now($appTimezone)->toDateTimeString();
        $sendPush    = (now($appTimezone)->toDateString() == $logDate);

        // Steps and Distance badge setting
        $title    = trans("notifications.badge.ongoing-steps.title");
        $isMobile = config('notification.general_badges.ongoing_steps.is_mobile');
        $isPortal = config('notification.general_badges.ongoing_steps.is_portal');

        if ($this->string == 'distance') {
            $title    = trans("notifications.badge.ongoing-distance.title");
            $isMobile = config('notification.general_badges.ongoing_distance.is_mobile');
            $isPortal = config('notification.general_badges.ongoing_distance.is_portal');
        }

        // Notification settings for badge module
        $notification_setting = $this->user->notificationSettings()
            ->select('flag')
            ->where('flag', 1)
            ->where(function ($query) {
                $query->where('module', '=', 'badges')
                    ->orWhere('module', '=', 'all');
            })
            ->first();

        if (!empty($getOngoingChallenge)) {
            foreach ($getOngoingChallenge as $value) {
                if ($value->is_badge) {
                    $startDate           = $value->start_date;
                    $diff                = $startDate->diffInDays($utcDateTime);
                    $targetType          = ($this->string == 'steps') ? 1 : 2;
                    $challengeGoingBadge = Badge::select('badges.*', 'challenge_ongoing_badges.id AS ongoing_badge_id', 'challenge_ongoing_badges.challenge_id AS challenge_id')
                        ->leftJoin('challenge_ongoing_badges', 'challenge_ongoing_badges.badge_id', '=', 'badges.id')
                        ->where('challenge_ongoing_badges.in_days', '>=', $diff)
                        ->where('challenge_ongoing_badges.challenge_target_id', $targetType)
                        ->where('challenge_ongoing_badges.target', '<=', $userDailyData)
                        ->where('challenge_ongoing_badges.challenge_id', $value->id)
                        ->get();
                    if (!empty($challengeGoingBadge)) {
                        // Challenge ongoing badge
                        foreach ($challengeGoingBadge as $badgeValue) {
                            // Steps and Distance badge setting
                            $message = trans("notifications.badge.ongoing-steps.message");
                            if ($this->string == 'distance') {
                                $message = trans("notifications.badge.ongoing-distance.message");
                            }

                            $checkAssignBadge = ChallengeOngoingBadgeUsers::select('id')
                                ->where('challenge_id', $badgeValue->challenge_id)
                                ->where('ongoing_badge_id', $badgeValue->ongoing_badge_id)
                                ->where('user_id', $this->user->id)
                                ->first();

                            if (empty($checkAssignBadge)) {
                                // Assign badge to user
                                $badgeValue->badgeusers()->attach($this->user, [
                                    'status'     => 'Active',
                                    'model_name' => 'challenge',
                                    'model_id'   => $badgeValue->challenge_id,
                                    'date_for'   => $this->dateInUTC,
                                    'created_at' => $this->dateInUTC,
                                ]);

                                ChallengeOngoingBadgeUsers::insert([
                                    'challenge_id'     => $badgeValue->challenge_id,
                                    'badge_id'         => $badgeValue->id,
                                    'ongoing_badge_id' => $badgeValue->ongoing_badge_id,
                                    'user_id'          => $this->user->id,
                                ]);

                                if ($logDate >= now($userTimezone)->toDateString()) {
                                    // Set badge Title in notification message
                                    $message = str_replace(["#challenge_badge_name#"], [$badgeValue->title], $message);
                                    $message = str_replace(["#challenge_name#"], [$value->title], $message);
                                    // Find deep link id
                                    $deepLinkId = \DB::table('badge_user')
                                        ->where("badge_id", $badgeValue->id)
                                        ->where("user_id", $this->user->id)
                                        ->where("status", 'Active')
                                        ->orderBy('id', 'DESC')
                                        ->pluck('id')
                                        ->first();
                                    $deep_link_uri = __(config('zevolifesettings.deeplink_uri.badge'), [
                                        'badge_id' => (!empty($deepLinkId) ? $deepLinkId : 0),
                                    ]);

                                    // Set notification
                                    $notification = Notification::create([
                                        'type'             => 'Auto',
                                        'creator_id'       => $badgeValue->creator_id,
                                        'company_id'       => $badgeValue->company_id,
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
                }
            }
        }
    }
}
