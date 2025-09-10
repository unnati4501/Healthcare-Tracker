<?php

namespace App\Jobs;

use App\Models\Calendly;
use App\Models\Notification;
use App\Models\User;
use App\Models\ZdTicket;
use App\Notifications\SystemAutoNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNewEapNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Calendly|ZdTicket| null $record
     */
    protected $record;

    /**
     * @var string $string
     */
    protected $string;

    /**
     * @var array $extraParams
     */
    protected $extraParams;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($record, $string, $extraParams = [])
    {
        $this->queue       = 'notifications';
        $this->record      = $record;
        $this->string      = $string;
        $this->extraParams = $extraParams;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $deep_link_uri         = $title         = $message         = "";
        $extraNotificationData = [
            'type'             => (isset($this->extraParams['type']) ? $this->extraParams['type'] : 'Auto'),
            'scheduled_at'     => (isset($this->extraParams['scheduled_at']) ? $this->extraParams['scheduled_at'] : now()->toDateTimeString()),
            'debug_identifier' => (isset($this->extraParams['debug_identifier']) ? $this->extraParams['debug_identifier'] : null),
        ];
        $counsellor = User::find($this->record->therapist_id, ['id', 'first_name']);
        $user       = User::where('users.id', $this->record->user_id)
            ->select('users.id', 'user_notification_settings.flag AS notification_flag', 'user_team.company_id')
            ->leftJoin('user_notification_settings', function ($join) {
                $join->on('user_notification_settings.user_id', '=', 'users.id')
                    ->where('user_notification_settings.flag', '=', 1)
                    ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['events', 'all']);
            })
            ->leftJoin('user_team', 'user_team.user_id', '=', 'users.id')
            ->groupBy('users.id')
            ->first();

        if (in_array($this->string, ['booked', 'cancelled', 'rescheduled'])) {
            $title   = trans('notifications.new-eap.' . $this->string . '.title');
            $message = trans('notifications.new-eap.' . $this->string . '.message', [
                'counsellor' => $counsellor->first_name,
            ]);
            if ($this->string == 'rescheduled') {
                $deep_link_uri = "";
            } else {
                $deep_link_uri = 'zevolife://zevo/eap-sessions/' . $this->record->id;
            }
        } elseif ($this->string == 'assigned') {
            $title   = trans('notifications.new-eap.' . $this->string . '.title');
            $message = trans('notifications.new-eap.' . $this->string . '.message', [
                'counsellor' => $counsellor->first_name,
            ]);
            $deep_link_uri = 'zevolife://zevo/book-session';
        } elseif ($this->string == 'reminder') {
            $title   = trans('notifications.new-eap.' . $this->string . '.title');
            $message = trans('notifications.new-eap.' . $this->string . '.message', [
                'session_name' => $this->record->name,
            ]);
            $deep_link_uri = 'zevolife://zevo/eap-sessions/' . $this->record->id;
        }

        if ($user) {
            $notificationData = [
                'creator_id'    => $this->record->therapist_id,
                'company_id'    => $user->company_id,
                'title'         => $title,
                'message'       => $message,
                'push'          => (!empty($user->notification_flag) ? $user->notification_flag : false),
                'deep_link_uri' => $deep_link_uri,
                'is_mobile'     => config('notification.new-eap.' . $this->string . '.is_mobile'),
                'is_portal'     => config('notification.new-eap.' . $this->string . '.is_portal'),
                'tag'           => 'new-eap',
            ] + $extraNotificationData;

            $notification = Notification::create($notificationData);

            if ($notification) {
                if ($notification->type != 'Auto') {
                    $user->notifications()
                        ->attach($notification, ['sent' => false]);
                } else {
                    $user->notifications()
                        ->attach($notification, ['sent' => true, 'sent_on' => now()->toDateTimeString()]);
                }

                if ($notification->push && $notification->type == 'Auto') {
                    \Notification::send(
                        $user,
                        new SystemAutoNotification($notification, $this->string)
                    );
                }
            }
        }
    }
}
