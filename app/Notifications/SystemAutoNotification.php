<?php
declare (strict_types = 1);

namespace App\Notifications;

use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification as NotificationLib;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

/**
 * Class SystemAutoNotification
 *
 * @package App\Notifications\Challenge
 */
class SystemAutoNotification extends NotificationLib
{
    use Queueable;
    /**
     * @var \App\Models\Notification
     */
    protected $notification;

    /**
     * Get the project key
     * @var $key
     */
    protected $key;

    public function __construct(Notification $notification, String $key = null)
    {
        $this->notification = $notification;
        $this->key          = config('zevolifesettings.notification_project_id');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        return [FcmChannel::class];
    }

    /**
     * @param $notifiable
     *
     * @return mixed
     */
    public function toFcm($notifiable)
    {
        if ($this->notification->is_mobile) {
            // The FcmNotification holds the notification parameters
            $sendData = [
                'action'          => 'deep-link',
                'key'             => $this->key,
                'notification_id' => strval($this->notification->getKey()),
            ];

            if (!empty($this->notification->deep_link_uri)) {
                $sendData['redirect_url'] = $this->notification->deep_link_uri;
            }

            return (new FcmMessage(notification: new FcmNotification(
                title: $this->notification->title,
                body: $this->notification->message
            )))
            ->data($sendData);
        }
    }
}
