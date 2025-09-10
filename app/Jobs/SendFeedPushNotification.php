<?php

namespace App\Jobs;

use App\Models\Feed;
use App\Models\Notification;
use App\Models\NotificationSetting;
use App\Notifications\SystemAutoNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendFeedPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Feed
     */
    protected $feed;
    protected $string;
    protected $users;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Feed $feed, string $string, $users)
    {
        $this->queue  = 'notifications';
        $this->feed   = $feed;
        $this->string = $string;
        $this->users  = $users;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $notification = '';
        $title        = '';
        $message      = '';
        $planAccess   = true;

        foreach ($this->users as $value) {
            $company = $value->company()->first();
            if($company->is_reseller || !is_null($company->parent_id)){
                $planAccess = getCompanyPlanAccess($value, 'explore');
            }
            if($planAccess){
                $title   = trans('notifications.feed.publish.title');
                $message = trans('notifications.feed.publish.message', [
                    'first_name'    => $value->first_name,
                    'article_title' => $this->feed->title,
                ]);
    
                $notificationData = [
                    'type'          => 'Auto',
                    'creator_id'    => $this->feed->creator_id,
                    'title'         => $title,
                    'message'       => $message,
                    'push'          => true,
                    'scheduled_at'  => now()->toDateTimeString(),
                    'deep_link_uri' => $this->feed->deep_link_uri,
                    'is_mobile'     => config('notification.home.new_story.is_mobile'),
                    'is_portal'     => config('notification.home.new_story.is_portal'),
                    'tag'           => 'feed',
                ];
    
                $notification = Notification::create($notificationData);
    
                $value->notifications()->attach($notification, ['sent' => true, 'sent_on' => now()->toDateTimeString()]);
                $userNotification = NotificationSetting::select('flag')
                    ->where(['flag' => 1, 'user_id' => $value->id])
                    ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['feeds', 'all'])
                    ->groupBy('user_notification_settings.user_id')
                    ->first();
    
                if (isset($userNotification->flag) && $userNotification->flag) {
                    \Notification::send(
                        $value,
                        new SystemAutoNotification($notification, $this->string)
                    );
                }
            }
        }
    }
}
