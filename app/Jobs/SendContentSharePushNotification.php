<?php

namespace App\Jobs;

use App\Models\Group;
use App\Models\Notification;
use App\Models\User;
use App\Notifications\SystemAutoNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendContentSharePushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Group
     */
    protected $group;
    protected $data;
    protected $sender;
    protected $extraContent;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Group $group, $data, $sender = "", $extraContent = [])
    {
        $this->queue        = 'notifications';
        $this->group        = $group;
        $this->data         = $data;
        $this->sender       = $sender;
        $this->extraContent = $extraContent;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $teamRestriction = null;
        if ($this->group->model_name == 'challenge') {
            $teamRestriction = $this->group->leftJoin('challenges', 'challenges.id', '=', 'groups.model_id')
                ->where('challenges.challenge_type', 'team')
                ->where('challenges.id', $this->group->model_id)
                ->first();
        }

        $userData = $this->group->members()
            ->leftJoin('user_team', 'user_team.user_id', '=', 'group_members.user_id')
            ->where(function ($query) use ($teamRestriction) {
                if (!empty($teamRestriction)) {
                    return $query->where('user_team.team_id', $this->sender->teams->first()->id);
                } else {
                    return $query->where('user_team.company_id', $this->sender->company->first()->id);
                }
            })
            ->wherePivot("group_id", $this->group->getKey())
            ->wherePivot('user_id', '!=', $this->sender->id)
            ->wherePivot("status", "Accepted")
            ->get();

        if (!isset($this->extraContent['is_mobile']) || !isset($this->extraContent['is_portal'])) {
            $this->extraContent['is_mobile'] = config('notification.home.story_shared.is_mobile');
            $this->extraContent['is_portal'] = config('notification.home.story_shared.is_portal');
        }

        if ($userData->count() > 0) {
            $storyType = '';
            $message   = trans('notifications.share.message');
            if ($this->extraContent['tag'] == 'recipe') {
                $message = trans('notifications.share.recipe_message');
            } elseif ($this->extraContent['tag'] == 'feed') {
                $message = trans('notifications.share.story_message');
                if ($this->extraContent['feedModelType'] == '4') {
                    $storyType = 'read';
                } elseif ($this->extraContent['feedModelType'] == '1') {
                    $storyType = 'listen';
                } else {
                    $storyType = 'watch';
                }
            }

            $message = str_replace(
                ['#user_name#', '#name#', '#module_name#', '#story_type#'],
                [$this->sender->first_name, $this->data['name'], $this->extraContent['module_name'], $storyType],
                $message
            );

            $notificationData = [
                'type'             => 'Auto',
                'creator_id'       => $this->group->creator_id,
                'company_id'       => $this->group->company_id,
                'creator_timezone' => $this->sender->timezone,
                'title'            => $this->data['title'],
                'message'          => $message,
                'push'             => true,
                'scheduled_at'     => now()->toDateTimeString(),
                'deep_link_uri'    => $this->data['deep_link_uri'],
            ] + $this->extraContent;

            $notification = Notification::create($notificationData);

            foreach ($userData as $value) {
                $value->notifications()->attach($notification, ['sent' => true, 'sent_on' => now()->toDateTimeString()]);

                $sendNotifaction = true;
                if ($value->pivot->notification_muted) {
                    $sendNotifaction = false;
                }

                if ($sendNotifaction) {
                    // send notification to all users
                    \Notification::send(
                        $value,
                        new SystemAutoNotification($notification, 'group-share-content')
                    );
                }
            }
        }
    }
}
