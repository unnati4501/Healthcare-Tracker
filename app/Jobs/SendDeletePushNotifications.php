<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\NotificationSetting;
use App\Notifications\SystemAutoNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendDeletePushNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    protected $options;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data = [], $options = [])
    {
        $this->queue   = 'notifications';
        $this->data    = $data;
        $this->options = $options;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $notificationData = [
            'type'             => 'Auto',
            'creator_id'       => $this->data['creator_id'],
            'company_id'       => $this->data['company_id'],
            'creator_timezone' => $this->data['creator_timezone'],
            'title'            => $this->data['title'],
            'message'          => $this->data['message'],
            'push'             => true,
            'scheduled_at'     => now()->toDateTimeString(),
            'is_mobile'        => (isset($this->data['is_mobile'])) ? $this->data['is_mobile'] : config('notification.challenge.deleted.is_mobile'),
            'is_portal'        => (isset($this->data['is_portal'])) ? $this->data['is_portal'] : config('notification.challenge.deleted.is_portal'),
            'deep_link_uri'    => '',
            'tag'              => 'challenge',
        ];

        $notification = Notification::create($notificationData);

        $membersData = $this->data['membersData'];
        $pushMembers = [];

        foreach ($membersData as $value) {
            $value->notifications()->attach($notification, ['sent' => true, 'sent_on' => now()->toDateTimeString()]);

            $sendPush = true;
            if (!in_array($this->data['string'], ['challenge-deleted'])) {
                $userNotification = NotificationSetting::select('flag')
                    ->where(['flag' => 1, 'user_id' => $value->id])
                    ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', [$this->data['module'], 'all'])
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
                new SystemAutoNotification($notification, $this->data['string'])
            );
        }
    }
}
