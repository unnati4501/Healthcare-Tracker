<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSyncTrackerPushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var User
     */
    protected $user;
    protected $string;
    protected $extra_param;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, string $string, $extra_param = [])
    {
        $this->queue       = 'notifications';
        $this->user        = $user;
        $this->string      = $string;
        $this->extra_param = $extra_param;
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
            'type'         => (isset($this->extra_param['type']) ? $this->extra_param['type'] : 'Auto'),
            'scheduled_at' => (isset($this->extra_param['scheduled_at']) ? $this->extra_param['scheduled_at'] : now()->toDateTimeString()),
            'push'         => (isset($this->extra_param['push']) ? $this->extra_param['push'] : true),
        ];

        if ($this->string == 'sync-tracker') {
            $title   = trans('notifications.tracker.synch.title');
            $message = trans('notifications.tracker.synch.message', [
                'first_name' => $this->user->first_name,
            ]);
        }

        $notificationData = [
            'creator_id'       => $this->user->id,
            'creator_timezone' => $this->user->timezone,
            'title'            => $title,
            'message'          => $message,
            'deep_link_uri'    => $deep_link_uri,
            'is_mobile'        => config('notification.sync.is_mobile'),
            'is_portal'        => config('notification.sync.is_portal'),
            'tag'              => 'sync',
        ] + $extraNotificationData;

        $notification = Notification::create($notificationData);

        if (isset($notification)) {
            $this->user->notifications()->attach($notification, ['sent' => false]);
        }
    }
}
