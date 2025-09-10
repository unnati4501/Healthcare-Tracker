<?php
declare (strict_types = 1);

namespace App\Notifications;

use App\Models\Notification;
use Illuminate\Bus\Queueable;
//use Illuminate\Notifications\Notification as NotificationLib;
//use Benwilkins\FCM\FcmMessage;

use Illuminate\Notifications\Notification as NotificationLib;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;



/**
 * Class SystemManualNotification
 *
 * @package App\Notifications
 */
class SystemManualNotification extends NotificationLib
{
    use Queueable;
    const PRIORITY_HIGH = 'high';
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
        $sendData = [
            'action'            => 'deep-link',
            'key'               => 'zevo-life-alert',
            'notification_id'   => strval($this->notification->getKey()),
            'mediaType'         => 'image',
            'mutable_content'   => strval(true),
            'content_available' => strval(true),
        ];

        if (!empty($this->notification->deep_link_uri)) {
            $sendData['redirect_url'] = $this->notification->deep_link_uri;
        }

        // The FcmMessage contains other options for the notification
        return (new FcmMessage(notification: new FcmNotification(
            title: $this->notification->title,
            body: strip_tags($this->notification->message)
        )))
        ->data($sendData);
    }
}
