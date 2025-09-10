<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\User;
use App\Models\Webinar;
use App\Notifications\SystemAutoNotification;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWebinarPushNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Webinar
     */
    protected $webinar;
    protected $string;
    protected $newlyAssociatedComps;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Webinar $webinar, $string, $newlyAssociatedComps = array())
    {
        $this->queue                = 'notifications';
        $this->webinar              = $webinar;
        $this->string               = $string;
        $this->newlyAssociatedComps = $newlyAssociatedComps;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->newlyAssociatedComps) {
            $membersData = User::select('users.*', 'user_notification_settings.flag AS notification_flag')
                ->join("user_team", "user_team.user_id", "=", "users.id")
                ->leftJoin('user_notification_settings', function ($join) {
                    $join->on('user_notification_settings.user_id', '=', 'users.id')
                        ->where('user_notification_settings.flag', '=', 1)
                        ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['webinars', 'all']);
                })
                ->whereRaw("user_team.company_id IN (?)",[
                    implode(',', $this->newlyAssociatedComps)
                ])
                ->where('is_blocked', false)
                ->groupBy('users.id')
                ->get();
        } else {
            $membersData = User::select('users.*', 'user_notification_settings.flag AS notification_flag')
                ->join("user_team", "user_team.user_id", "=", "users.id")
                ->leftJoin('user_notification_settings', function ($join) {
                    $join->on('user_notification_settings.user_id', '=', 'users.id')
                        ->where('user_notification_settings.flag', '=', 1)
                        ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['webinars', 'all']);
                })
                ->whereRaw(DB::raw('user_team.team_id IN ( SELECT team_id FROM `webinar_team` WHERE webinar_id = ? )'),[
                    $this->webinar->id
                ])
                ->where('is_blocked', false)
                ->groupBy('users.id')
                ->get();
        }

        if ($membersData->count() > 0) {
            $title   = trans('notifications.webinar.track-added.title');
            $message = trans('notifications.webinar.track-added.message', [
                'webinar_title' => $this->webinar->title,
            ]);

            $notificationData = [
                'type'          => 'Auto',
                'creator_id'    => $this->webinar->author_id,
                'title'         => $title,
                'message'       => $message,
                'push'          => true,
                'scheduled_at'  => now()->toDateTimeString(),
                'deep_link_uri' => $this->webinar->deep_link_uri,
                'is_mobile'     => config('notification.workshop.added.is_mobile'),
                'is_portal'     => config('notification.workshop.added.is_portal'),
                'tag'           => 'webinar',
            ];
            $notification = Notification::create($notificationData);
            $planAccess   = true;
            foreach ($membersData as $value) {
                $company = $value->company()->first();
                if($company->is_reseller || !is_null($company->parent_id)){
                    $planAccess = getCompanyPlanAccess($value, 'explore');
                }
                if($planAccess){
                    $value->notifications()->attach($notification, ['sent' => true, 'sent_on' => now()->toDateTimeString()]);

                    if ($value->notification_flag) {
                        // send notification to all users
                        \Notification::send(
                            $value,
                            new SystemAutoNotification($notification, $this->string)
                        );
                    }
                }
            }
        }
    }
}
