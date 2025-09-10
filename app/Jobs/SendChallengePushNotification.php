<?php

namespace App\Jobs;

use App\Models\Challenge;
use App\Models\Company;
use App\Models\Notification;
use App\Models\NotificationSetting;
use App\Models\User;
use App\Notifications\SystemAutoNotification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendChallengePushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Challenge
     */
    protected $challenge;
    protected $string;
    protected $userName;
    protected $invitedUser;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Challenge $challenge, $string, $userName = "", $invitedUser = array())
    {
        $this->queue       = 'notifications';
        $this->challenge   = $challenge;
        $this->string      = $string;
        $this->userName    = $userName;
        $this->invitedUser = $invitedUser;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $createrData = User::find($this->challenge->creator_id);
        $title       = "";
        $message     = "";
        $isMobile    = "";
        $isPortal    = "";
        $membersData = array();
        $deepLink    = $this->challenge->deep_link_uri;

        $now = now()->toDateTimeString();

        if ($this->string == "challenge-created") {
            $challenge_type = $this->challenge->challenge_type;
            $title          = trans('notifications.challenge.challenge-created.title');
            $message        = trans("notifications.challenge.challenge-created.message.$challenge_type");
            $message        = str_replace(["#challenge_name#"], [$this->challenge->title], $message);

            if ($this->challenge->challenge_type == 'individual') {
                $isMobile    = config('notification.individual_challenge.created.is_mobile');
                $isPortal    = config('notification.individual_challenge.created.is_portal');
                $membersData = $this->challenge->members()->wherePivot('status', "Accepted")->get();
            } elseif ($this->challenge->challenge_type == 'team' || $this->challenge->challenge_type == 'company_goal') {
                $isMobile    = config('notification.team_company_goal_challenge.created.is_mobile');
                $isPortal    = config('notification.team_company_goal_challenge.created.is_portal');
                $teamData    = $this->challenge->memberTeams()->get();
                $membersData = $teamData->transform(function ($value) {
                    return $value->users()->get();
                })->flatten();
            } elseif ($this->challenge->challenge_type == 'inter_company') {
                $isMobile    = config('notification.intercompany_challenge.created.is_mobile');
                $isPortal    = config('notification.intercompany_challenge.created.is_portal');
                $teamData    = $this->challenge->memberTeams()->get();
                $membersData = $teamData->transform(function ($value) {
                    return $value->users()->get();
                })->flatten();
            }

            $deepLink = 'zevolife://zevo/challenge/' . $this->challenge->id . '/upcoming';
        } elseif ($this->string == "challenge-updated") {
            $title   = "Challenge updated";
            $message = $this->challenge->title . " was updated.";

            if ($this->challenge->challenge_type == 'individual') {
                $isMobile    = config('notification.individual_challenge.created.is_mobile');
                $isPortal    = config('notification.individual_challenge.created.is_portal');
                $membersData = $this->challenge->members()->wherePivotIn('user_id', $this->invitedUser)->get();
            } elseif ($this->challenge->challenge_type == 'team' || $this->challenge->challenge_type == 'company_goal') {
                $isMobile    = config('notification.team_company_goal_challenge.created.is_mobile');
                $isPortal    = config('notification.team_company_goal_challenge.created.is_portal');
                $teamData    = $this->challenge->memberTeams()->wherePivotIn('team_id', $this->invitedUser)->get();
                $membersData = $teamData->transform(function ($value) {
                    return $value->users()->get();
                })->flatten();
            } elseif ($this->challenge->challenge_type == 'inter_company') {
                $isMobile    = config('notification.intercompany_challenge.created.is_mobile');
                $isPortal    = config('notification.intercompany_challenge.created.is_portal');
                $teamData    = $this->challenge->memberTeams()->wherePivotIn('team_id', $this->invitedUser)->get();
                $membersData = $teamData->transform(function ($value) {
                    return $value->users()->get();
                })->flatten();
            }

            if ($now < $this->challenge->start_date) {
                $deepLink = 'zevolife://zevo/challenge/' . $this->challenge->id . '/upcoming';
            }
        } elseif ($this->string == "challenge-invitation") {
            $title   = trans('notifications.challenge.challenge-invitation.title');
            $message = trans('notifications.challenge.challenge-invitation.message');

            $isMobile = config('notification.individual_challenge.invitation.is_mobile');
            $isPortal = config('notification.individual_challenge.invitation.is_portal');

            $message = str_replace(["#challenge_title#", "#challenge_owner#"], [$this->challenge->title, $createrData->full_name], $message);

            $membersData = $this->challenge->members()->wherePivotIn('user_id', $this->invitedUser)->get();

            if ($now < $this->challenge->start_date) {
                $deepLink = 'zevolife://zevo/challenge/' . $this->challenge->id . '/upcoming';
            }
        } elseif ($this->string == "challenge-invitation-accepted") {
            $title   = trans('notifications.challenge.challenge-invitation-accepted.title');
            $message = trans('notifications.challenge.challenge-invitation-accepted.message');

            $isMobile = config('notification.individual_challenge.accepted.is_mobile');
            $isPortal = config('notification.individual_challenge.accepted.is_portal');

            $message = str_replace(["#user_name#", "#challenge_name#"], [$this->userName, $this->challenge->title], $message);

            $membersData = User::where("id", $this->challenge->creator_id)->get();

            if ($now < $this->challenge->start_date) {
                $deepLink = 'zevolife://zevo/challenge/' . $this->challenge->id . '/upcoming';
            }
        } elseif ($this->string == "challenge-invitation-rejected") {
            $title   = "Challenge invitation rejected";
            $message = $this->userName . " did not accept your invite to " . $this->challenge->title;

            $isMobile = config('notification.individual_challenge.accepted.is_mobile');
            $isPortal = config('notification.individual_challenge.accepted.is_portal');

            $membersData = User::where("id", $this->challenge->creator_id)->get();

            if ($now < $this->challenge->start_date) {
                $deepLink = 'zevolife://zevo/challenge/' . $this->challenge->id . '/upcoming';
            }
        } elseif ($this->string == "challenge-cancelled") {
            $title   = trans('notifications.challenge.challenge-cancelled.title');
            $message = trans('notifications.challenge.challenge-cancelled.message');

            $isMobile = config('notification.intercompany_challenge.cancelled.is_mobile');
            $isPortal = config('notification.intercompany_challenge.cancelled.is_portal');

            $message = str_replace(["#challenge_name#"], [$this->challenge->title], $message);

            $membersData = $this->challenge->members()->where('user_id', '!=', $this->challenge->creator_id)->wherePivot('status', "Accepted")->get();

            if ($now < $this->challenge->start_date) {
                $deepLink = 'zevolife://zevo/challenge/' . $this->challenge->id . '/upcoming';
            }
        } elseif ($this->string == "challenge-left") {
            $title       = "Challenge left";
            $message     = $this->userName . " left the " . $this->challenge->title;
            $membersData = User::where("id", $this->challenge->creator_id)->get();

            $isMobile = config('notification.intercompany_challenge.cancelled.is_mobile');
            $isPortal = config('notification.intercompany_challenge.cancelled.is_portal');

            if ($now < $this->challenge->start_date) {
                $deepLink = 'zevolife://zevo/challenge/' . $this->challenge->id . '/upcoming';
            }
        } elseif ($this->string == "challenge-created-updated") {
            $challenge_type = $this->challenge->challenge_type;

            $title   = trans('notifications.challenge.challenge-created.title');
            $message = trans("notifications.challenge.challenge-created.message.$challenge_type");

            $message = str_replace(["#challenge_name#"], [$this->challenge->title], $message);

            if ($this->challenge->challenge_type == 'individual') {
                $isMobile = config('notification.intercompany_challenge.created.is_mobile');
                $isPortal = config('notification.intercompany_challenge.created.is_portal');

                $membersData = $this->challenge->members()->wherePivotIn('user_id', $this->invitedUser)->get();
            } else {
                $isMobile = config('notification.team_company_goal_challenge.created.is_mobile');
                $isPortal = config('notification.team_company_goal_challenge.created.is_portal');

                $teamData    = $this->challenge->memberTeams()->wherePivotIn('team_id', $this->invitedUser)->get();
                $membersData = $teamData->transform(function ($value) {
                    return $value->users()->get();
                })->flatten();
            }

            if ($now < $this->challenge->start_date) {
                $deepLink = 'zevolife://zevo/challenge/' . $this->challenge->id . '/upcoming';
            }
        } elseif ($this->string == "challenge-updated-removed") {
            $title   = trans('notifications.challenge.challenge-updated-removed.title');
            $message = trans('notifications.challenge.challenge-updated-removed.message');

            $isMobile = config('notification.team_company_goal_challenge.created.is_mobile');
            $isPortal = config('notification.team_company_goal_challenge.created.is_portal');

            $message = str_replace(["#challenge_name#"], [$this->challenge->title], $message);

            $teamData    = \App\Models\Team::where('id', $this->invitedUser)->get();
            $membersData = $teamData->transform(function ($value) {
                return $value->users()->get();
            })->flatten();

            if ($now < $this->challenge->start_date) {
                $deepLink = 'zevolife://zevo/challenge/' . $this->challenge->id . '/upcoming';
            }
        } elseif ($this->string == "challenge-deleted") {
            $title   = trans('notifications.challenge.challenge-deleted.title');
            $message = trans('notifications.challenge.challenge-deleted.message');

            $isMobile = config('notification.challenge.deleted.is_mobile');
            $isPortal = config('notification.challenge.deleted.is_portal');

            $message = str_replace(["#challenge_name#"], [$this->challenge->title], $message);

            if ($this->challenge->challenge_type == 'individual') {
                $membersData = $this->challenge->members()->where('status', 'Accepted')->get();
            } else {
                $teamData    = $this->challenge->memberTeams()->get();
                $membersData = $teamData->transform(function ($value) {
                    return $value->users()->get();
                })->flatten();
            }
            $deepLink = '';
        } elseif ($this->string == "challenge-auto-cancelled") {
            $isMobile = config('notification.challenge.auto_cancelled.is_mobile');
            $isPortal = config('notification.challenge.auto_cancelled.is_portal');

            $title   = trans('notifications.challenge.challenge-auto-cancelled.title');
            $message = trans('notifications.challenge.challenge-auto-cancelled.message');
            $message = str_replace(["#challenge_name#"], [$this->challenge->title], $message);

            $membersData = $this->invitedUser;
        } elseif ($this->string == "challenge-started-reminder") {
            $isMobile = config('notification.challenge.start.is_mobile');
            $isPortal = config('notification.challenge.start.is_portal');

            $title   = trans('notifications.challenge.challenge-start-reminder.title');
            $message = trans('notifications.challenge.challenge-start-reminder.message');
            $message = str_replace(["#challenge_name#"], [$this->challenge->title], $message);

            $membersData = collect($this->invitedUser);

            $deepLink = 'zevolife://zevo/challenge/' . $this->challenge->id . '/upcoming';
        } elseif ($this->string == "challenge-end-reminder") {
            $isMobile = config('notification.challenge.end.is_mobile');
            $isPortal = config('notification.challenge.end.is_portal');

            $end_date = Carbon::parse($this->challenge->end_date, \config('app.timezone'))
                ->setTimezone($createrData->timezone)
                ->format(\config('zevolifesettings.date_format.default_datetime'));

            $title   = trans('notifications.challenge.challenge-end-reminder.title');
            $message = trans('notifications.challenge.challenge-end-reminder.message');
            $message = str_replace(["#challenge_name#", "#end_time#"], [$this->challenge->title, $end_date], $message);

            $membersData = collect($this->invitedUser);
        } elseif ($this->string == "challenge-finished") {
            $isMobile = config('notification.challenge.finished.is_mobile');
            $isPortal = config('notification.challenge.finished.is_portal');

            $title   = trans('notifications.challenge.challenge-finished.title');
            $message = trans('notifications.challenge.challenge-finished.message');
            $message = str_replace(["#challenge_name#"], [$this->challenge->title], $message);

            $deepLink = 'zevolife://zevo/challenge/leaderboad/' . $this->challenge->id;
            $membersData = collect($this->invitedUser);
        } elseif ($this->string == "challenge-loss") {
            $challenge_type = $this->challenge->challenge_type;

            $isMobile = config('notification.challenge.finished.is_mobile');
            $isPortal = config('notification.challenge.finished.is_portal');

            $title   = trans('notifications.challenge.challenge-loss.title');
            $message = trans("notifications.challenge.challenge-loss.message.$challenge_type");
            $message = str_replace(["#challenge_name#"], [$this->challenge->title], $message);
            if ($challenge_type == "company_goal") {
                $companyDetails = Company::find($this->challenge->company_id);
                $message        = str_replace(["#company_name#"], [$companyDetails->name], $message);
            }

            $membersData = collect($this->invitedUser);
        } elseif ($this->string == "challenge-won") {
            $challenge_type = $this->challenge->challenge_type;
            $title          = trans('notifications.challenge.challenge-won.title');
            $message        = '';

            if ($challenge_type == "individual") {
                $title    = trans('notifications.challenge.challenge-won.title');
                $isMobile = config('notification.individual_challenge.won.is_mobile');
                $isPortal = config('notification.individual_challenge.won.is_portal');

                $recurring_type = (($this->challenge->recurring || !empty($this->challenge->parent_id)) ? 'recurring' : 'non-recurring');
                $message        = trans("notifications.challenge.challenge-won.message.$challenge_type.$recurring_type");
                if ($recurring_type == 'recurring') {
                    $message = str_replace(["#level_no#"], [$this->userName], $message);
                }
            } elseif ($challenge_type == "team") {
                $title    = trans('notifications.challenge.challenge-won.team_title');
                $isMobile = config('notification.team_challenge.won.is_mobile');
                $isPortal = config('notification.team_challenge.won.is_portal');

                $message = trans("notifications.challenge.challenge-won.message.$challenge_type");
                $message = str_replace(["#level_no#"], [$this->userName], $message);
            } elseif ($challenge_type == "company_goal") {
                $title    = trans('notifications.challenge.challenge-won.company_goal_title');
                $isMobile = config('notification.team_company_goal_challenge.won.is_mobile');
                $isPortal = config('notification.team_company_goal_challenge.won.is_portal');

                $message        = trans("notifications.challenge.challenge-won.message.$challenge_type");
                $companyDetails = Company::find($this->challenge->company_id);
                $message        = str_replace(["#company_name#"], [$companyDetails->name], $message);
            } elseif ($challenge_type == "inter_company") {
                $title    = trans('notifications.challenge.challenge-won.intercompany_title');
                $isMobile = config('notification.intercompany_challenge.won.is_mobile');
                $isPortal = config('notification.intercompany_challenge.won.is_portal');

                if ($this->userName == "team_level") {
                    $message = trans('notifications.challenge.challenge-won.message.inter_company.team_level');
                    $message = str_replace(
                        ["#user_name#", "#company_name#", "#challenge_name#"],
                        [
                            $this->invitedUser[0]->full_name,
                            $this->invitedUser[0]->company()->first()->name,
                            $this->challenge->title,
                        ],
                        $message
                    );
                } elseif ($this->userName == "company_level") {
                    $message = trans('notifications.challenge.challenge-won.message.inter_company.company_level');
                }
            }

            $message = str_replace(["#challenge_name#"], [$this->challenge->title], $message);

            $membersData = collect($this->invitedUser);
        }

        if ($membersData->count() > 0) {
            if ($this->string == "challenge-created" || $this->string == 'challenge-created-updated' || $this->string == 'challenge-invitation' || $this->string == 'challenge-won' || $this->string == 'challenge-loss') {
                foreach ($membersData as $value) {
                    if ($this->string == "challenge-created") {
                        $challenge_type = $this->challenge->challenge_type;
                        $title          = trans('notifications.challenge.challenge-created.title');
                        $message        = trans("notifications.challenge.challenge-created.message.$challenge_type");
                        $message        = str_replace(["#challenge_name#"], [$this->challenge->title], $message);

                        if ($this->challenge->challenge_type == 'individual') {
                            $isMobile    = config('notification.individual_challenge.created.is_mobile');
                            $isPortal    = config('notification.individual_challenge.created.is_portal');
                            $membersData = $this->challenge->members()->wherePivot('status', "Accepted")->get();
                        } elseif ($this->challenge->challenge_type == 'team' || $this->challenge->challenge_type == 'company_goal') {
                            $isMobile    = config('notification.team_company_goal_challenge.created.is_mobile');
                            $isPortal    = config('notification.team_company_goal_challenge.created.is_portal');
                            $teamData    = $this->challenge->memberTeams()->get();
                            $membersData = $teamData->transform(function ($value) {
                                return $value->users()->get();
                            })->flatten();
                        } elseif ($this->challenge->challenge_type == 'inter_company') {
                            $isMobile    = config('notification.intercompany_challenge.created.is_mobile');
                            $isPortal    = config('notification.intercompany_challenge.created.is_portal');
                            $teamData    = $this->challenge->memberTeams()->get();
                            $membersData = $teamData->transform(function ($value) {
                                return $value->users()->get();
                            })->flatten();
                        }

                        $deepLink = 'zevolife://zevo/challenge/' . $this->challenge->id . '/upcoming';
                    } elseif ($this->string == "challenge-invitation") {
                        $title   = trans('notifications.challenge.challenge-invitation.title');
                        $message = trans('notifications.challenge.challenge-invitation.message');

                        $isMobile = config('notification.individual_challenge.invitation.is_mobile');
                        $isPortal = config('notification.individual_challenge.invitation.is_portal');

                        $message = str_replace(["#challenge_title#", "#challenge_owner#"], [$this->challenge->title, $createrData->full_name], $message);

                        $membersData = $this->challenge->members()->wherePivotIn('user_id', $this->invitedUser)->get();

                        if ($now < $this->challenge->start_date) {
                            $deepLink = 'zevolife://zevo/challenge/' . $this->challenge->id . '/upcoming';
                        }
                    } elseif ($this->string == "challenge-created-updated") {
                        $challenge_type = $this->challenge->challenge_type;

                        $title   = trans('notifications.challenge.challenge-created.title');
                        $message = trans("notifications.challenge.challenge-created.message.$challenge_type");

                        $message = str_replace(["#challenge_name#"], [$this->challenge->title], $message);

                        if ($this->challenge->challenge_type == 'individual') {
                            $isMobile = config('notification.intercompany_challenge.created.is_mobile');
                            $isPortal = config('notification.intercompany_challenge.created.is_portal');

                            $membersData = $this->challenge->members()->wherePivotIn('user_id', $this->invitedUser)->get();
                        } else {
                            $isMobile = config('notification.team_company_goal_challenge.created.is_mobile');
                            $isPortal = config('notification.team_company_goal_challenge.created.is_portal');

                            $teamData    = $this->challenge->memberTeams()->wherePivotIn('team_id', $this->invitedUser)->get();
                            $membersData = $teamData->transform(function ($value) {
                                return $value->users()->get();
                            })->flatten();
                        }

                        if ($now < $this->challenge->start_date) {
                            $deepLink = 'zevolife://zevo/challenge/' . $this->challenge->id . '/upcoming';
                        }
                    } elseif ($this->string == "challenge-loss") {
                        $challenge_type = $this->challenge->challenge_type;

                        $isMobile = config('notification.challenge.finished.is_mobile');
                        $isPortal = config('notification.challenge.finished.is_portal');

                        $title   = trans('notifications.challenge.challenge-loss.title');
                        $message = trans("notifications.challenge.challenge-loss.message.$challenge_type");
                        $message = str_replace(["#challenge_name#"], [$this->challenge->title], $message);
                        if ($challenge_type == "company_goal") {
                            $companyDetails = Company::find($this->challenge->company_id);
                            $message        = str_replace(["#company_name#"], [$companyDetails->name], $message);
                        }

                        $membersData = collect($this->invitedUser);
                    } elseif ($this->string == "challenge-won") {
                        $challenge_type = $this->challenge->challenge_type;
                        $title          = trans('notifications.challenge.challenge-won.title');
                        $message        = '';

                        if ($challenge_type == "individual") {
                            $title    = trans('notifications.challenge.challenge-won.title');
                            $isMobile = config('notification.individual_challenge.won.is_mobile');
                            $isPortal = config('notification.individual_challenge.won.is_portal');

                            $recurring_type = (($this->challenge->recurring || !empty($this->challenge->parent_id)) ? 'recurring' : 'non-recurring');
                            $message        = trans("notifications.challenge.challenge-won.message.$challenge_type.$recurring_type");
                            if ($recurring_type == 'recurring') {
                                $message = str_replace(["#level_no#"], [$this->userName], $message);
                            }
                        } elseif ($challenge_type == "team") {
                            $title    = trans('notifications.challenge.challenge-won.team_title');
                            $isMobile = config('notification.team_challenge.won.is_mobile');
                            $isPortal = config('notification.team_challenge.won.is_portal');

                            $message = trans("notifications.challenge.challenge-won.message.$challenge_type");
                            $message = str_replace(["#level_no#"], [$this->userName], $message);
                        } elseif ($challenge_type == "company_goal") {
                            $title    = trans('notifications.challenge.challenge-won.company_goal_title');
                            $isMobile = config('notification.team_company_goal_challenge.won.is_mobile');
                            $isPortal = config('notification.team_company_goal_challenge.won.is_portal');

                            $message        = trans("notifications.challenge.challenge-won.message.$challenge_type");
                            $companyDetails = Company::find($this->challenge->company_id);
                            $message        = str_replace(["#company_name#"], [$companyDetails->name], $message);
                        } elseif ($challenge_type == "inter_company") {
                            $title    = trans('notifications.challenge.challenge-won.intercompany_title');
                            $isMobile = config('notification.intercompany_challenge.won.is_mobile');
                            $isPortal = config('notification.intercompany_challenge.won.is_portal');

                            if ($this->userName == "team_level") {
                                $message = trans('notifications.challenge.challenge-won.message.inter_company.team_level');
                                $message = str_replace(
                                    ["#user_name#", "#company_name#", "#challenge_name#"],
                                    [
                                        $this->invitedUser[0]->full_name,
                                        $this->invitedUser[0]->company()->first()->name,
                                        $this->challenge->title,
                                    ],
                                    $message
                                );
                            } elseif ($this->userName == "company_level") {
                                $message = trans('notifications.challenge.challenge-won.message.inter_company.company_level');
                            }
                        }

                        $message = str_replace(["#challenge_name#"], [$this->challenge->title], $message);

                        $membersData = collect($this->invitedUser);
                    }

                    $notificationData = [
                        'type'             => 'Auto',
                        'creator_id'       => $this->challenge->creator_id,
                        'company_id'       => $this->challenge->company_id,
                        'creator_timezone' => $this->challenge->timezone,
                        'title'            => $title,
                        'push'             => true,
                        'scheduled_at'     => now()->toDateTimeString(),
                        'deep_link_uri'    => $deepLink,
                        'is_mobile'        => $isMobile,
                        'is_portal'        => $isPortal,
                        'tag'              => 'challenge',
                    ];

                    if ($this->string == "challenge-created" || $this->string == 'challenge-created-updated') {
                        $challenge_start_date = Carbon::parse($this->challenge->start_date, \config('app.timezone'))
                            ->setTimezone($value->timezone)
                            ->format(\config('zevolifesettings.date_format.default_datetime'));

                        $message = str_replace(['#challenge_start_date#'], [$challenge_start_date], $message);

                        $message = __($message, [
                            'first_name' => $value->first_name,
                        ]);

                        $notificationData['message'] = $message;
                    } else {
                        $message = __($message, [
                            'first_name' => $value->first_name,
                        ]);

                        $notificationData['message'] = $message;
                    }

                    $notification = Notification::create($notificationData);

                    $value->notifications()->attach($notification, ['sent' => true, 'sent_on' => now()->toDateTimeString()]);

                    $sendPush = true;
                    
                    $userNotification = NotificationSetting::select('flag')
                        ->where(['flag' => 1, 'user_id' => $value->id])
                        ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['challenges', 'all'])
                        ->first();
                    $sendPush = ($userNotification->flag ?? false);

                    if ($sendPush) {
                        // send notification to all users
                        \Notification::send(
                            $value,
                            new SystemAutoNotification($notification, $this->string)
                        );
                    }
                }
            } else {
                $notificationData = [
                    'type'             => 'Auto',
                    'creator_id'       => $this->challenge->creator_id,
                    'company_id'       => $this->challenge->company_id,
                    'creator_timezone' => $this->challenge->timezone,
                    'title'            => $title,
                    'message'          => $message,
                    'push'             => true,
                    'scheduled_at'     => now()->toDateTimeString(),
                    'deep_link_uri'    => $deepLink,
                    'is_mobile'        => $isMobile,
                    'is_portal'        => $isPortal,
                    'tag'              => 'challenge',
                ];

                $notification = Notification::create($notificationData);
                $pushMembers  = [];
                foreach ($membersData as $value) {
                    $value->notifications()->attach($notification, ['sent' => true, 'sent_on' => now()->toDateTimeString()]);

                    $sendPush = true;
                    if (!in_array($this->string, [
                        "challenge-created",
                        "challenge-invitation",
                        "challenge-invitation-accepted",
                        "challenge-cancelled",
                        "challenge-updated-removed",
                        "challenge-deleted",
                        "challenge-started-reminder",
                        "challenge-end-reminder",
                        "challenge-finished",
                        "challenge-loss",
                        "challenge-won",
                    ])) {
                        $userNotification = NotificationSetting::select('flag')
                            ->where(['flag' => 1, 'user_id' => $value->id])
                            ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['challenges', 'all'])
                            ->first();
                        $sendPush = ($userNotification->flag ?? false);
                    }

                    if ($sendPush) {
                        $pushMembers[] = $value;
                    }
                }

                if (!empty($pushMembers)) {
                    // send notification to all users
                    \Notification::send(
                        $pushMembers,
                        new SystemAutoNotification($notification, $this->string)
                    );
                }
            }
        }
    }
}
