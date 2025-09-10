<?php

namespace App\Jobs;

use App\Models\Group;
use App\Models\Notification;
use App\Models\NotificationSetting;
use App\Models\User;
use App\Notifications\SystemAutoNotification;
use App\Notifications\SystemDirectNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendGroupPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Group
     */
    protected $group;
    protected $string;
    protected $userName;
    protected $senderId;
    protected $invitedUser;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Group $group, $string, $userName = "", $senderId = "", $invitedUser = array())
    {
        $this->queue       = 'notifications';
        $this->group       = $group;
        $this->string      = $string;
        $this->userName    = $userName;
        $this->senderId    = $senderId;
        $this->invitedUser = $invitedUser;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $createrData = User::find($this->group->creator_id);
        $title       = "";
        $message     = "";

        $isMobile = $isPortal = '';

        $deepLink           = $this->group->deep_link_uri;
        $membersData        = array();
        $directNotification = array();

        if ($this->string == "user-assigned-group") {
            $title   = trans('notifications.group.user-assigned-group.title');
            $message = trans('notifications.group.user-assigned-group.message');

            $isMobile = config('notification.group.enrolled.is_mobile');
            $isPortal = config('notification.group.enrolled.is_portal');

            $message = str_replace(["#group_name#"], [$this->group->title], $message);

            if (!$this->group->is_visible) {
                $this->group->update(['is_visible' => 1]);
            }

            $membersData = $this->group
                ->members()
                ->wherePivot('user_id', '!=', $this->group->creator_id)
                ->wherePivot('status', "Accepted")
                ->get();
        } elseif ($this->string == "user-joined-group") {
            $title   = "Joined the group";
            $message = "User " . $this->userName . " joined the " . $this->group->title . " group.";

            $isMobile = config('notification.group.enrolled.is_mobile');
            $isPortal = config('notification.group.enrolled.is_portal');

            $membersData = $this->group->
                members()
                ->wherePivot('user_id', $this->group->creator_id)
                ->get();
        } elseif ($this->string == "new-group") {
            if($this->group->created_by == 'User'){
                $deepLink = __(config('zevolifesettings.deeplink_uri.group_invite'), [
                    'id' => $this->group->id,
                ]);
            }
            $title   = trans('notifications.group.new-group.title');
            $message = trans('notifications.group.new-group.message');
            $message = str_replace(["#group_name#", "#creator_name#"], [$this->group->title, $createrData->full_name], $message);
            $isMobile = config('notification.group.enrolled.is_mobile');
            $isPortal = config('notification.group.enrolled.is_portal');

            $membersData = $this->group
                ->members()
                ->wherePivot('user_id', '!=', $this->group->creator_id)
                ->wherePivot('status', "Accepted")
                ->where('accept_decline_status', 0)
                ->get();
        } elseif ($this->string == "user-assigned-updated-group") {
            $title   = trans('notifications.group.user-assigned-updated-group.title');
            $message = trans('notifications.group.user-assigned-updated-group.message');

            $isMobile = config('notification.group.enrolled.is_mobile');
            $isPortal = config('notification.group.enrolled.is_portal');

            $message = str_replace(["#group_name#"], [$this->group->title], $message);

            $membersData = $this->group
                ->members()
                ->wherePivotIn('user_id', $this->invitedUser)
                ->get();
        } elseif ($this->string == "group-deleted") {
            $deepLink = '';

            $title   = trans('notifications.group.group-deleted.title');
            $message = trans('notifications.group.group-deleted.message');
            $message = str_replace(["#group_name#"], [$this->group->title], $message);

            $isMobile = config('notification.group.deleted.is_mobile');
            $isPortal = config('notification.group.deleted.is_portal');
            if ($this->group->created_by == 'User') {
                $membersData = $this->group
                    ->members()
                    ->wherePivot('user_id', '!=', $this->group->creator_id)
                    ->get();
            } else {
                $membersData = $this->group->members;
            }
        }

        if (!empty($membersData) && $membersData->count() > 0) {
            if ($this->string != 'message-in-group') {
                if ($this->string == 'user-joined-group' || $this->string == 'group-deleted') {
                    $notification = Notification::create([
                        'type'             => 'Auto',
                        'creator_id'       => $this->group->creator_id,
                        'company_id'       => $this->group->company_id,
                        'creator_timezone' => $createrData->timezone,
                        'title'            => $title,
                        'message'          => $message,
                        'push'             => true,
                        'scheduled_at'     => now()->toDateTimeString(),
                        'deep_link_uri'    => $deepLink,
                        'is_mobile'        => $isMobile,
                        'is_portal'        => $isPortal,
                        'tag'              => 'group',
                    ]);
                }
            }

            foreach ($membersData as $value) {
                $sendNotifaction = true;

                if ($this->string == "user-joined-group") {
                    $title   = "Joined the group";
                    $message = "User " . $this->userName . " joined the " . $this->group->title . " group.";

                    $isMobile = config('notification.group.enrolled.is_mobile');
                    $isPortal = config('notification.group.enrolled.is_portal');

                    $membersData = $this->group->
                        members()
                        ->wherePivot('user_id', $this->group->creator_id)
                        ->get();
                } elseif ($this->string == "group-deleted") {
                    $deepLink = '';

                    $title   = trans('notifications.group.group-deleted.title');
                    $message = trans('notifications.group.group-deleted.message');
                    $message = str_replace(["#group_name#"], [$this->group->title], $message);

                    $isMobile = config('notification.group.deleted.is_mobile');
                    $isPortal = config('notification.group.deleted.is_portal');

                    if ($this->group->created_by == 'User') {
                        $membersData = $this->group
                            ->members()
                            ->wherePivot('user_id', '!=', $this->group->creator_id)
                            ->get();
                    } else {
                        $membersData = $this->group->members;
                    }
                }

                if ($this->string == 'user-assigned-group' || $this->string == 'new-group' || $this->string == 'user-assigned-updated-group') {
                    $notification = Notification::create([
                        'type'             => 'Auto',
                        'creator_id'       => $this->group->creator_id,
                        'company_id'       => $this->group->company_id,
                        'creator_timezone' => $createrData->timezone,
                        'title'            => $title,
                        'message'          => __($message, ['first_name' => $value->first_name]),
                        'push'             => true,
                        'scheduled_at'     => now()->toDateTimeString(),
                        'deep_link_uri'    => $deepLink,
                        'is_mobile'        => $isMobile,
                        'is_portal'        => $isPortal,
                        'tag'              => 'group',
                    ]);
                }

                $userNotification = NotificationSetting::select('flag')
                    ->where(['flag' => 1, 'user_id' => $value->id])
                    ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['groups', 'all'])
                    ->first();
                $sendPush = ($userNotification->flag ?? false);

                if ($this->string != "new-group" && $this->string != "group-deleted") {
                    if ($value->pivot->notification_muted) {
                        $sendNotifaction = false;
                    }
                }

                if ($sendNotifaction) {
                    $sendNotifaction = $sendPush;
                }

                if ($this->string != 'message-in-group') {
                    $value->notifications()->attach($notification, ['sent' => true, 'sent_on' => now()->toDateTimeString()]);
                }

                if ($sendNotifaction && isset($notification)) {
                    // send notification to all users
                    \Notification::send(
                        $value,
                        new SystemAutoNotification($notification, $this->string)
                    );
                }

                if ($sendNotifaction && !isset($notification) && isset($directNotification)) {
                    // send notification to all users
                    \Notification::send(
                        $value,
                        new SystemDirectNotification($directNotification, $this->string)
                    );
                }
            }
        }
    }
}
