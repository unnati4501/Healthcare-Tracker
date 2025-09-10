<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\PersonalChallenge;
use App\Notifications\SystemAutoNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPersonalChallengePushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var PersonalChallenge
     */
    protected $challenge;
    protected $string;
    protected $user;
    protected $extra_param;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(PersonalChallenge $challenge, string $string, $user, $extra_param = [])
    {
        $this->queue       = 'notifications';
        $this->challenge   = $challenge;
        $this->string      = $string;
        $this->user        = $user;
        $this->extra_param = $extra_param;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $title = $message = $key = '';

        $isMobile = $isPortal = '';

        $extraNotificationData = [
            'type'         => (isset($this->extra_param['type']) ? $this->extra_param['type'] : 'Auto'),
            'scheduled_at' => (isset($this->extra_param['scheduled_at']) ? $this->extra_param['scheduled_at'] : now()->toDateTimeString()),
            'push'         => (isset($this->extra_param['push']) ? $this->extra_param['push'] : true),
        ];

        $deepLink = $this->challenge->deep_link_uri . '/' . $this->extra_param['mapping_id'];

        if ($this->string == 'challenge-start') {
            $title   = trans('notifications.personal-challenge.challenge-start.title');
            $message = trans('notifications.personal-challenge.challenge-start.message');

            $isMobile = config('notification.personal_challenge.start.is_mobile');
            $isPortal = config('notification.personal_challenge.start.is_portal');

            $message = str_replace(["#challenge_name#"], [$this->challenge->title], $message);
        } elseif ($this->string == 'challenge-end') {
            $title   = trans('notifications.personal-challenge.challenge-end.title');
            $message = trans('notifications.personal-challenge.challenge-end.message');

            $isMobile = config('notification.personal_challenge.end.is_mobile');
            $isPortal = config('notification.personal_challenge.end.is_portal');

            $message = str_replace(
                ["#challenge_name#", "#end_time#"],
                [$this->challenge->title, $this->extra_param['end_date']],
                $message
            );
        } elseif ($this->string == 'challenge-finished') {
            $title   = trans('notifications.personal-challenge.challenge-finished.title');
            $message = trans('notifications.personal-challenge.challenge-finished.message');

            $isMobile = config('notification.personal_challenge.finished.is_mobile');
            $isPortal = config('notification.personal_challenge.finished.is_portal');

            $message  = str_replace(["#challenge_name#"], [$this->challenge->title], $message);
            $key      = 'Challenge finished';
            $deepLink = 'zevolife://zevo/challenge/leaderboad/' . $this->challenge->id;
        } elseif ($this->string == 'challenge-won') {
            $title   = trans('notifications.personal-challenge.challenge-won.title');
            $message = trans('notifications.personal-challenge.challenge-won.message');

            $isMobile = config('notification.personal_challenge.won.is_mobile');
            $isPortal = config('notification.personal_challenge.won.is_portal');

            $message = str_replace(["#challenge_name#"], [$this->challenge->title], $message);
            $key     = 'Challenge won';
        } elseif ($this->string == 'yesterday-reminder') {
            $title   = trans('notifications.personal-challenge.yesterday-reminder.title');
            $message = trans('notifications.personal-challenge.yesterday-reminder.message');

            $isMobile = config('notification.personal_challenge.reminder.is_mobile');
            $isPortal = config('notification.personal_challenge.reminder.is_portal');

            $message = str_replace(
                ["#user_name#", "#challenge_name#"],
                [$this->user->first_name, $this->challenge->title],
                $message
            );
            $key = 'Reminder';
        } elseif ($this->string == 'reminder') {
            $title   = trans('notifications.personal-challenge.reminder.title');
            $message = trans('notifications.personal-challenge.reminder.message');

            $isMobile = config('notification.personal_challenge.today_reminder.is_mobile');
            $isPortal = config('notification.personal_challenge.today_reminder.is_portal');

            $message = str_replace(
                ["#user_name#", "#challenge_name#"],
                [$this->user->first_name, $this->challenge->title],
                $message
            );
            $key = 'Reminder';
        } elseif ($this->string == 'pfc-reminder') {
            $title   = trans('notifications.personal-challenge.pfc-reminder.title');
            $message = trans('notifications.personal-challenge.pfc-reminder.message');

            $isMobile = config('notification.personal_challenge.today_reminder.is_mobile');
            $isPortal = config('notification.personal_challenge.today_reminder.is_portal');

            $message = str_replace(
                ["#user_name#", "#challenge_name#"],
                [$this->user->first_name, $this->challenge->title],
                $message
            );
            $key = 'Reminder';
        }

        $notificationData = [
            'creator_id'       => $this->user->id,
            'creator_timezone' => $this->user->timezone,
            'title'            => $title,
            'message'          => __($message, ['first_name' => $this->user->first_name]),
            'deep_link_uri'    => $deepLink,
            'is_mobile'        => $isMobile,
            'is_portal'        => $isPortal,
            'tag'              => 'challenge',
        ] + $extraNotificationData;

        $notification = Notification::create($notificationData);

        if (isset($notification)) {
            if (in_array($this->string, ['challenge-start', 'challenge-end'])) {
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
    }
}
