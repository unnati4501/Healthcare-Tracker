<?php

namespace App\Jobs;

use App\Models\HsSurvey;
use App\Models\Notification;
use App\Models\User;
use App\Notifications\SystemAutoNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendGeneralPushNotification implements ShouldQueue
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
        $title = $message = $key = $deep_link_uri = '';

        $isMobile = $isPortal = '';

        $extraNotificationData = [
            'type'         => (isset($this->extraParam['type']) ? $this->extraParam['type'] : 'Auto'),
            'scheduled_at' => (isset($this->extraParam['scheduled_at']) ? $this->extraParam['scheduled_at'] : now()->toDateTimeString()),
            'push'         => (isset($this->extraParam['push']) ? $this->extraParam['push'] : true),
        ];

        if (isset($this->extraParam['company_id'])) {
            $extraNotificationData['company_id'] = $this->extraParam['company_id'];
        }

        if ($this->string == 'survey-reminder') {
            $hsSurvey = HsSurvey::select('id')->where('user_id', $this->extraParam['user_id'])->get()->count();
            $title    = trans('notifications.survey.reminder.title');
            $message  = ($hsSurvey > 1) ? trans('notifications.survey.reminder.second_message') : trans('notifications.survey.reminder.message');

            $isMobile = config('notification.personal_challenge.reminder.is_mobile');
            $isPortal = config('notification.personal_challenge.reminder.is_portal');

            $message = str_replace(["#user_name#"], [($this->user->first_name)], $message);

            $deep_link_uri = 'zevolife://zevo/health-score';

            $key                          = 'health-score-survey';
            $extraNotificationData['tag'] = 'survey';
        } elseif ($this->string == 'survey-feedback') {
            $title = trans('notifications.survey.feedback.title');
            $message = trans('notifications.survey.feedback.message');

            $isMobile = config('notification.CSAT.feedback.is_mobile');
            $isPortal = config('notification.CSAT.feedback.is_portal');

            $deep_link_uri = config('zevolifesettings.deeplink_uri.nps');

            $key                          = 'nps-survey';
            $extraNotificationData['tag'] = 'survey';
        } elseif ($this->string == 'set-profile-picture') {
            $title   = trans('notifications.profile.picture.title');
            $message = trans('notifications.profile.picture.message');

            $isMobile = config('notification.users.set_profile_picture.is_mobile');
            $isPortal = config('notification.users.set_profile_picture.is_portal');

            $deep_link_uri = config('zevolifesettings.deeplink_uri.self-profile');

            $key                          = 'set-profile-picture';
            $extraNotificationData['tag'] = 'user';
        } elseif ($this->string == 'audit-survey') {
            $title   = trans('notifications.survey.audit.title');
            $message = trans('notifications.survey.audit.message', [
                'survey_name' => $this->extraParam['surveyName'],
            ]);

            $isMobile = config('notification.survey.available.is_mobile');
            $isPortal = config('notification.survey.available.is_portal');

            $deep_link_uri = __(config('zevolifesettings.deeplink_uri.audit-survey'), [
                'survey_log_id' => (!empty($this->extraParam['survey_log_id']) ? $this->extraParam['survey_log_id'] : 0),
            ]);

            $key                          = 'audit-survey';
            $extraNotificationData['tag'] = 'audit-survey';
        }

        if ($this->string == 'survey-feedback') {
            if ($this->user->can_access_app == 1) {
                $message = trans('notifications.survey.feedback.message');
                $notificationData = [
                    'creator_id'       => $this->user->id,
                    'creator_timezone' => $this->user->timezone,
                    'title'            => $title,
                    'message'          => $message,
                    'deep_link_uri'    => $deep_link_uri,
                    'is_mobile'        => $isMobile,
                    'is_portal'        => false,
                ] + $extraNotificationData;

                $notification = Notification::create($notificationData);
            }

            if ($this->user->can_access_portal == 1) {
                $message = trans('notifications.survey.feedback.portal_message');
                $notificationData = [
                    'creator_id'       => $this->user->id,
                    'creator_timezone' => $this->user->timezone,
                    'title'            => $title,
                    'message'          => $message,
                    'deep_link_uri'    => $deep_link_uri,
                    'is_mobile'        => false,
                    'is_portal'        => $isPortal,
                ] + $extraNotificationData;

                $notificationPortal = Notification::create($notificationData);
            }
        } else {
            $notificationData = [
                'creator_id'       => $this->user->id,
                'creator_timezone' => $this->user->timezone,
                'title'            => $title,
                'message'          => $message,
                'deep_link_uri'    => $deep_link_uri,
                'is_mobile'        => $isMobile,
                'is_portal'        => $isPortal,
            ] + $extraNotificationData;

            $notification = Notification::create($notificationData);
        }

        if (isset($notification)) {
            if (in_array($this->string, ['set-profile-picture'])) {
                $this->user->notifications()->attach($notification, ['sent' => false]);
            } else {
                $this->user->notifications()->attach($notification, [
                    'sent'    => true,
                    'sent_on' => now()->toDateTimeString(),
                ]);

                if ($notification->push) {
                    \Notification::send(
                        $this->user,
                        new SystemAutoNotification($notification, $key)
                    );
                }
            }
        }


        if (isset($notificationPortal)) {
            $this->user->notifications()->attach($notificationPortal, [
                'sent'    => true,
                'sent_on' => now()->toDateTimeString(),
            ]);

            if ($notificationPortal->push) {
                \Notification::send(
                    $this->user,
                    new SystemAutoNotification($notificationPortal, $key)
                );
            }
        }
    }
}
