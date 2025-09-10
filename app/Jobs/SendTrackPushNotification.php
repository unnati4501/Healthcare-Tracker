<?php

namespace App\Jobs;

use App\Models\MeditationTrack;
use App\Models\Notification;
use App\Models\User;
use App\Notifications\SystemAutoNotification;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTrackPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var MeditationTrack
     */
    protected $track;
    protected $string;
    protected $newlyAssociatedComps;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(MeditationTrack $track, $string, $newlyAssociatedComps = array())
    {
        $this->queue                = 'notifications';
        $this->track                = $track;
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
                        ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['meditations', 'all']);
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
                        ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['meditations', 'all']);
                })
                ->whereRaw(DB::raw('user_team.team_id IN ( SELECT team_id FROM `meditation_tracks_team` WHERE meditation_track_id = ? )'),[
                    $this->track->id
                ])
                ->where(['is_blocked' => false])
                ->groupBy('users.id')
                ->get();
        }

        $title = trans('notifications.meditation.track-added.title');
        $planAccess   = true;
        if ($membersData->count() > 0) {
            foreach ($membersData as $value) {
                $company = $value->company()->first();
                if($company->is_reseller || !is_null($company->parent_id)){
                    $planAccess = getCompanyPlanAccess($value, 'explore');
                }
                if($planAccess){
                    $message = trans('notifications.meditation.track-added.message', [
                        'first_name'  => $value->first_name,
                        'track_title' => $this->track->title,
                    ]);
    
                    $notificationData = [
                        'type'          => 'Auto',
                        'creator_id'    => $this->track->coach_id,
                        'title'         => $title,
                        'message'       => $message,
                        'push'          => true,
                        'scheduled_at'  => now()->toDateTimeString(),
                        'deep_link_uri' => $this->track->deep_link_uri,
                        'is_mobile'     => config('notification.meditation.added.is_mobile'),
                        'is_portal'     => config('notification.meditation.added.is_portal'),
                        'tag'           => 'meditation',
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
