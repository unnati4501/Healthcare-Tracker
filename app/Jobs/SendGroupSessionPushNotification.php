<?php

namespace App\Jobs;

use App\Models\CronofySchedule;
use App\Models\Notification;
use App\Models\ServiceSubCategory;
use App\Models\User;
use App\Notifications\SystemAutoNotification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendGroupSessionPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Recipe
     */
    protected $cronofySchedule;
    protected $string;
    protected $users;
    protected $extraData;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(CronofySchedule $cronofySchedule, $string, $users, $extraData = [])
    {
        $this->queue           = 'notifications';
        $this->cronofySchedule = $cronofySchedule;
        $this->string          = $string;
        $this->users           = $users;
        $this->extraData       = $extraData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->users      = User::hydrate($this->users);
        $title            = trans('notifications.digital-therapy.' . $this->string . '.title');
        $message          = trans('notifications.digital-therapy.' . $this->string . '.message');
        $isMobile         = config('notification.digital-therapy.' . $this->string . '.is_mobile');
        $isPortal         = config('notification.digital-therapy.' . $this->string . '.is_portal');
        $getWS            = User::where('id', $this->cronofySchedule->ws_id)->select('first_name')->first();
        $getSubCategories = ServiceSubCategory::where('id', $this->cronofySchedule->topic_id)->select('name')->first();
        $deepLinkUri      = 'zevolife://zevo/dt-sessions/' . $this->cronofySchedule->id;

        if ($this->users->isNotEmpty()) {
            $planAccess = true;
            foreach ($this->users as $value) {
                $company = $value->company()->first();
                if($company->is_reseller || !is_null($company->parent_id)){
                    $planAccess = getCompanyPlanAccess($value, 'digital-therapy');
                }
                if($planAccess){
                    if ($this->string == 'session-start-reminder') {
                        $checkAlreadySend = Notification::leftJoin('notification_user', 'notification_user.notification_id', '=', 'notifications.id')
                            ->where('deep_link_uri', $deepLinkUri)
                            ->where('notification_user.user_id', $value->id)
                            ->where('title', 'Session Reminder')
                            ->select('id')
                            ->count();
                        if ($checkAlreadySend > 0) {
                            return true;
                        }
                    }
    
                    $startTime = Carbon::parse($this->cronofySchedule->start_time, config('app.timezone'))->setTimezone($value->timezone)->format('d-M');
                    if ($this->string == 'group-session-reschedule' || $this->string == 'group-session-cancel' || $this->string == 'session-start-reminder') {
                        $message = trans('notifications.digital-therapy.' . $this->string . '.message', [
                            'WS_first_name' => $getWS->first_name,
                        ]);
                    } else {
                        $message = trans('notifications.digital-therapy.' . $this->string . '.message', [
                            'date'              => $startTime,
                            'WS_first_name'     => $getWS->first_name,
                            'sub_category_name' => $getSubCategories->name,
                        ]);
                    }
    
                    $notificationData = [
                        'type'          => 'Auto',
                        'creator_id'    => $this->cronofySchedule->created_by,
                        'title'         => $title,
                        'message'       => $message,
                        'push'          => true,
                        'scheduled_at'  => now()->toDateTimeString(),
                        'deep_link_uri' => $deepLinkUri,
                        'is_mobile'     => $isMobile,
                        'is_portal'     => $isPortal,
                        'tag'           => 'digital-therapy',
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
