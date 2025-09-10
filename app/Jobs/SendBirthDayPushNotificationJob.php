<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendBirthDayPushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var User
     */
    protected $user;
    protected $string;
    protected $extraParam;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, string $string, $extraParam = [])
    {
        $this->queue       = 'notifications';
        $this->user        = $user;
        $this->string      = $string;
        $this->extraParam  = $extraParam;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $title = $message = $notification = $deep_link_uri = '';

        $extraNotificationData = [
            'type'         => (isset($this->extraParam['type']) ? $this->extraParam['type'] : 'Auto'),
            'scheduled_at' => (isset($this->extraParam['scheduled_at']) ? $this->extraParam['scheduled_at'] : now()->toDateTimeString()),
            'push'         => (isset($this->extraParam['push']) ? $this->extraParam['push'] : true),
        ];

        if ($this->string == 'birthday-greet') {
            $title   = trans('notifications.greetings.birthday.title');
            $message = trans('notifications.greetings.birthday.message');
            $message = str_replace(
                ["#first_name#"],
                [$this->user->first_name],
                $message
            );
            $deep_link_uri = "zevolife://zevo/alerts";
        }

        $notificationData = [
            'creator_id'       => $this->user->id,
            'creator_timezone' => $this->user->timezone,
            'title'            => $title,
            'message'          => $message,
            'deep_link_uri'    => $deep_link_uri,
            'is_mobile'        => config('notification.general.happy_birth.is_mobile'),
            'is_portal'        => config('notification.general.happy_birth.is_portal'),
            'tag'              => 'general',
        ] + $extraNotificationData;

        $notification = Notification::create($notificationData);

        if (isset($notification)) {
            $this->user->notifications()->attach($notification, ['sent' => false]);
        }
    }
}
