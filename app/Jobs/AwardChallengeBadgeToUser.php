<?php

namespace App\Jobs;

use App\Models\Badge;
use App\Models\Challenge;
use App\Models\Notification;
use App\Models\User;
use App\Notifications\SystemAutoNotification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AwardChallengeBadgeToUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var User
     */
    protected $users;
    protected $challenge;
    protected $challengeRulesData;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Challenge $challenge, $users, $challengeRulesData)
    {
        $this->queue              = 'notifications';
        $this->challenge          = $challenge;
        $this->users              = $users;
        $this->challengeRulesData = $challengeRulesData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $appTimezone = config('app.timezone');
        $chRulesData = $this->challenge
            ->challengeRules()
            ->join('challenge_targets', 'challenge_targets.id', '=', 'challenge_rules.challenge_target_id')
            ->select('challenge_rules.*', 'challenge_targets.short_name', 'challenge_targets.name')
            ->get();

        $isMobile = config('notification.general_badges.steps.is_mobile');
        $isPortal = config('notification.general_badges.steps.is_portal');
        if ($this->users->count() > 0) {
            foreach ($this->users as $value) {
                $userTimezone         = $value->timezone ?? config('app.timezone');
                $notification_setting = $value
                    ->notificationSettings()
                    ->select('flag')
                    ->where('flag', 1)
                    ->where(function ($query) {
                        $query->where('module', '=', 'badges')->orWhere('module', '=', 'all');
                    })
                    ->first();
                $title = trans('notifications.badge.steps.title');

                $startDate = Carbon::parse($this->challenge->start_date, $appTimezone)->setTimezone($userTimezone);
                $endDate   = Carbon::parse($this->challenge->end_date, $appTimezone)->setTimezone($userTimezone);
                foreach ($chRulesData as $rule) {
                    $userData = 0;
                    if ($rule->short_name == 'distance') {
                        $isMobile = config('notification.general_badges.distance.is_mobile');
                        $isPortal = config('notification.general_badges.distance.is_portal');

                        $userData = $value->getDistancePointsForBadgeCalculation($startDate, $endDate, $appTimezone, $userTimezone);

                        $badgeData = Badge::select("badges.*")
                            ->join("challenge_badges", "challenge_badges.badge_id", "=", "badges.id")
                            ->where("challenge_badges.challenge_id", $this->challenge->id)
                            ->where("badges.challenge_target_id", $rule->challenge_target_id)
                            ->where("badges.target", "<=", $userData)
                            ->where("badges.type", 'challenge')
                            ->get();
                    } elseif ($rule->short_name == 'steps') {
                        $isMobile = config('notification.general_badges.steps.is_mobile');
                        $isPortal = config('notification.general_badges.steps.is_portal');

                        $userData = $value->getStepsPointsForBadgeCalculation($startDate, $endDate, $appTimezone, $userTimezone);

                        $badgeData = Badge::select("badges.*")
                            ->join("challenge_badges", "challenge_badges.badge_id", "=", "badges.id")
                            ->where("challenge_badges.challenge_id", $this->challenge->id)
                            ->where("badges.challenge_target_id", $rule->challenge_target_id)
                            ->where("badges.target", "<=", $userData)
                            ->where("badges.type", 'challenge')
                            ->get();
                    } elseif ($rule->short_name == 'exercises' && $rule->model_name == 'Exercise') {
                        $isMobile = config('notification.general_badges.earned.is_mobile');
                        $isPortal = config('notification.general_badges.earned.is_portal');

                        $userData = $value->getExercisesPointsForBadgeCalculation($startDate, $endDate, $appTimezone, $userTimezone, $rule->uom, $rule->model_id);

                        $badgeData = Badge::select("badges.*")
                            ->join("challenge_badges", "challenge_badges.badge_id", "=", "badges.id")
                            ->where("challenge_badges.challenge_id", $this->challenge->id)
                            ->where("badges.challenge_target_id", $rule->challenge_target_id)
                            ->where("badges.uom", $rule->uom)
                            ->where("badges.model_id", $rule->model_id)
                            ->where("badges.target", "<=", $userData)
                            ->where("badges.type", 'challenge')
                            ->get();
                    } elseif ($rule->short_name == 'meditations') {
                        $isMobile = config('notification.general_badges.meditation.is_mobile');
                        $isPortal = config('notification.general_badges.meditation.is_portal');

                        $userData = $value->getMeditationPointsForBadgeCalculation($startDate, $endDate, $appTimezone, $userTimezone, $rule->uom);

                        $badgeData = Badge::select("badges.*")
                            ->join("challenge_badges", "challenge_badges.badge_id", "=", "badges.id")
                            ->where("challenge_badges.challenge_id", $this->challenge->id)
                            ->where("badges.challenge_target_id", $rule->challenge_target_id)
                            ->where("badges.target", "<=", $userData)
                            ->where("badges.type", 'challenge')
                            ->get();
                    }

                    if (!empty($badgeData) && $badgeData->count() > 0) {
                        foreach ($badgeData as $badge) {
                            $userBadgeData = $value->badges()
                                ->wherePivot("badge_id", $badge->id)
                                ->wherePivot("user_id", $value->id)
                                ->wherePivot('model_id', $this->challenge->id)
                                ->wherePivot("model_name", 'challenge')
                                ->wherePivot("status", 'Active')
                                ->first();

                            if (empty($userBadgeData)) {
                                $badgeInput = [
                                    'status'     => "Active",
                                    'model_id'   => $this->challenge->id,
                                    'model_name' => 'challenge',
                                ];
                                $badge->badgeusers()->attach($value->id, $badgeInput);

                                $message = trans("notifications.badge.$rule->short_name.message", "");
                                $message = str_replace(["#badge_name#"], [$badge->title], $message);

                                $deepLinkId = \DB::table('badge_user')
                                    ->where("badge_id", $badge->id)
                                    ->where("user_id", $value->id)
                                    ->where("status", 'Active')
                                    ->orderBy('id', 'DESC')
                                    ->pluck('id')
                                    ->first();

                                $notification = Notification::create([
                                    'type'             => 'Auto',
                                    'creator_id'       => $this->challenge->creator_id,
                                    'company_id'       => $this->challenge->company_id,
                                    'creator_timezone' => $this->challenge->timezone,
                                    'title'            => $title,
                                    'message'          => $message,
                                    'push'             => ($notification_setting->flag ?? false),
                                    'scheduled_at'     => now()->toDateTimeString(),
                                    'deep_link_uri'    => 'zevolife://zevo/badge/' . $deepLinkId,
                                    'is_mobile'        => $isMobile,
                                    'is_portal'        => $isPortal,
                                    'tag'              => 'badge',
                                ]);

                                $value->notifications()->attach($notification, ['sent' => true, 'sent_on' => now()->toDateTimeString()]);

                                if (!empty($notification_setting) && $notification_setting->flag) {
                                    // send notification to all users
                                    \Notification::send(
                                        $value,
                                        new SystemAutoNotification($notification, '')
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
