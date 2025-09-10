<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\Notification;
use App\Models\NotificationSetting;
use App\Notifications\SystemAutoNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendEventPushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Event|null $event
     */
    protected $event;

    /**
     * @var string $string
     */
    protected $string;

    /**
     * @var array $users
     */
    protected $users;

    /**
     * @var array $extraParams
     */
    protected $extraParams;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(? Event $event, $string, $users, $extraParams = [])
    {
        $this->queue       = 'notifications';
        $this->event       = $event;
        $this->string      = $string;
        $this->users       = $users;
        $this->extraParams = $extraParams;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $title               = $message               = "";
        if (!empty($this->extraParams['booking_id'])) {
            $deep_link_uri = $this->event->deep_link_uri . '/' . $this->extraParams['booking_id'];
        } else {
            $deep_link_uri = $this->event->deep_link_uri;
        }
        $extraNotificationData = [
            'type'         => (isset($this->extraParams['type']) ? $this->extraParams['type'] : 'Auto'),
            'scheduled_at' => (isset($this->extraParams['scheduled_at']) ? $this->extraParams['scheduled_at'] : now()->toDateTimeString()),
            'push'         => (isset($this->extraParams['push']) ? $this->extraParams['push'] : true),
            'company_id'   => (isset($this->extraParams['company_id']) ? $this->extraParams['company_id'] : $this->event->company_id),
        ];

        if ($this->string == "removed") {
            $title         = trans('notifications.events.event-deleted.title');
            $message       = trans('notifications.events.event-deleted.message', ['event_name' => $this->event->name]);
            $deep_link_uri = "";
        } elseif ($this->string == "added") {
            $title   = trans('notifications.events.event-added.title');
            $message = trans('notifications.events.event-added.message', ['event_name' => $this->event->name]);
        } elseif ($this->string == "updated") {
            $title   = trans('notifications.events.event-updated.title');
            $message = trans('notifications.events.event-updated.message', ['event_name' => $this->event->name]);
        } elseif ($this->string == "registered") {
            $title   = trans('notifications.events.event-registered.title');
            $message = trans('notifications.events.event-registered.message', ['event_name' => $this->event->name]);
        } elseif ($this->string == "reminder-today") {
            $eventTime = ($this->extraParams['event_time'] ?? "");
            $title     = trans('notifications.events.event-reminder-today.title');
            $message   = trans('notifications.events.event-reminder-today.message', [
                'event_name' => $this->event->name,
                'event_time' => $eventTime,
            ]);
        } elseif ($this->string == "reminder-tomorrow") {
            $eventTime = ($this->extraParams['event_time'] ?? "");
            $title     = trans('notifications.events.event-reminder-tomorrow.title');
            $message   = trans('notifications.events.event-reminder-tomorrow.message', [
                'event_name' => $this->event->name,
                'event_time' => $eventTime,
            ]);
        } elseif ($this->string == "csat") {
            $title   = trans('notifications.events.csat.title');
            $message = trans('notifications.events.csat.message', [
                'event_name' => $this->event->name,
            ]);
            $deep_link_uri = trans(config('zevolifesettings.deeplink_uri.event_csat'), [
                'id' => $this->extraParams['booking_id'],
            ]);
        }

        if ($this->users->count() > 0) {
            $notificationData = [
                'creator_id'    => $this->event->creator_id,
                'title'         => $title,
                'message'       => $message,
                'deep_link_uri' => $deep_link_uri,
                'is_mobile'     => config("notification.events.{$this->string}.is_mobile", true),
                'is_portal'     => config("notification.events.{$this->string}.is_portal", true),
                'tag'           => 'event',
            ] + $extraNotificationData;

            $notification = Notification::create($notificationData);

            $comapny         = $notification->company()->select('companies.id', 'companies.is_reseller', 'companies.parent_id')->first();
            $checkValidation = (!$comapny->is_reseller && is_null($comapny->parent_id));
            $planAccess      = true;
            
            foreach ($this->users as $value) {
                $company = $value->company()->first();
                if($company->is_reseller || !is_null($company->parent_id)){
                    $planAccess = getCompanyPlanAccess($value, 'event');
                }
                if($planAccess){
                    $sendPush = true;
                    if ($checkValidation) {
                        $userNotification = NotificationSetting::select('flag')
                            ->where(['flag' => 1, 'user_id' => $value->id])
                            ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['events', 'all'])
                            ->first();
                        $sendPush = ($userNotification->flag ?? false);
                    }

                    $value->notifications()->attach($notification, ['sent' => true, 'sent_on' => now()->toDateTimeString()]);
                    if ($sendPush) {
                    \Notification::send(
                            $value,
                            new SystemAutoNotification($notification, "event-{$this->string}")
                        );
                    }
                }
            }
        }
    }
}
