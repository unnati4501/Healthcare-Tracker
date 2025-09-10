<?php

namespace App\Jobs;

use App\Models\Shorts;
use App\Models\Notification;
use App\Models\User;
use App\Notifications\SystemAutoNotification;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendShortPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Short
     */
    protected $short;
    protected $string;
    protected $newlyAssociatedComps;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Shorts $short, $string, $newlyAssociatedComps = array())
    {
        $this->queue                = 'notifications';
        $this->short                = $short;
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
            $membersData = User::select('users.id', 'users.first_name', 'user_notification_settings.flag AS notification_flag')
                ->join("user_team", "user_team.user_id", "=", "users.id")
                ->leftJoin('user_notification_settings', function ($join) {
                    $join->on('user_notification_settings.user_id', '=', 'users.id')
                        ->where('user_notification_settings.flag', '=', 1)
                        ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['shorts', 'all']);
                })
                ->whereRaw("user_team.team_id IN (?)",[
                    implode(',', $this->newlyAssociatedComps)
                ])
                ->where(['is_blocked' => false])
                ->groupBy('users.id')
                ->get();
        } else {
            $membersData = User::select('users.id', 'users.first_name', 'user_notification_settings.flag AS notification_flag')
                ->join("user_team", "user_team.user_id", "=", "users.id")
                ->leftJoin('user_notification_settings', function ($join) {
                    $join->on('user_notification_settings.user_id', '=', 'users.id')
                        ->where('user_notification_settings.flag', '=', 1)
                        ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['shorts', 'all']);
                })
                ->whereRaw(DB::raw('user_team.team_id IN ( SELECT team_id FROM `shorts_team` WHERE short_id = ? )'),[
                    $this->short->id
                ])
                ->where(['is_blocked' => false])
                ->groupBy('users.id')
                ->get();
        }
        $title = trans('notifications.shorts.short-added.title');
        $planAccess   = true;
        if ($membersData->count() > 0) {
            foreach ($membersData as $value) {
                $company = $value->company()->first();
                if($company->is_reseller || !is_null($company->parent_id)){
                    $planAccess = getCompanyPlanAccess($value, 'explore');
                }
                if($planAccess){
                    $message = trans('notifications.shorts.short-added.message', [
                        'first_name'  => $value->first_name,
                        'short_title' => $this->short->title,
                    ]);
    
                    $notificationData = [
                        'type'          => 'Auto',
                        'creator_id'    => $this->short->author_id,
                        'title'         => $title,
                        'message'       => $message,
                        'push'          => true,
                        'scheduled_at'  => now()->toDateTimeString(),
                        'deep_link_uri' => $this->short->deep_link_uri,
                        'is_mobile'     => config('notification.shorts.added.is_mobile'),
                        'is_portal'     => config('notification.shorts.added.is_portal'),
                        'tag'           => 'short',
                    ];

                    $notification = Notification::create($notificationData);
    
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
