<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\User;
use App\Notifications\SystemAutoNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTeamChangePushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var user
     * @var teamName
     * @var creatorId - Set other user as a creator instead of admin(1), default value will be 1
     */
    protected $user;
    protected $teamName;
    protected $creatorId;
    protected $newTeamId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, $teamName = "", $creatorId = 1, $newTeamId = "")
    {
        $this->queue     = 'notifications';
        $this->user      = $user;
        $this->teamName  = $teamName;
        $this->creatorId = $creatorId;
        $this->newTeamId = $newTeamId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $deep_link_uri = ((!empty($this->newTeamId)) ? "zevolife://zevo/team/" . $this->newTeamId : '');

        $title   = trans('notifications.team.added-to-team.title');
        $message = trans('notifications.team.added-to-team.message');
        $message = str_replace(["#team_name#"], [$this->teamName], $message);

        $notificationData = [
            'type'             => 'Auto',
            'creator_id'       => $this->creatorId,
            'company_id'       => $this->user->company()->first()->id,
            'creator_timezone' => $this->user->timezone,
            'title'            => $title,
            'message'          => $message,
            'push'             => true,
            'scheduled_at'     => now()->toDateTimeString(),
            'deep_link_uri'    => $deep_link_uri,
            'is_mobile'        => config('notification.team.updated.is_mobile'),
            'is_portal'        => config('notification.team.updated.is_portal'),
            'tag'              => 'team',
        ];

        $notification = Notification::create($notificationData);

        $this->user->notifications()->attach($notification, ['sent' => true, 'sent_on' => now()->toDateTimeString()]);

        \Notification::send(
            $this->user,
            new SystemAutoNotification($notification, 'Team change')
        );
    }
}
