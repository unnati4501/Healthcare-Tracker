<?php

namespace App\Jobs;

use App\Models\CronofySchedule;
use App\Models\Notification;
use App\Models\User;
use App\Models\ConsentFormLogs;
use App\Notifications\SystemAutoNotification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendConsentPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Recipe
     */
    protected $cronofySchedule;
    protected $string;
    protected $user;
    protected $from;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(CronofySchedule $cronofySchedule, $string, $user, $from = '')
    {
        $this->queue           = 'notifications';
        $this->cronofySchedule = $cronofySchedule;
        $this->string          = $string;
        $this->user            = $user;
        $this->from            = $from;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $title            = trans('notifications.consent-form.' . $this->string . '.title');
        $isMobile         = config('notification.consent-form.' . $this->string . '.is_mobile');
        $isPortal         = config('notification.consent-form.' . $this->string . '.is_portal');
        $getWS            = User::where('id', $this->cronofySchedule->ws_id)->select('first_name')->first();
        
        if (!empty($this->user)) {
            $planAccess     = true;
            $company        = $this->user->company()->first();
            $digitalTherapy = $company->digitalTherapy()->first();
            
            if (!empty($digitalTherapy) && $digitalTherapy->consent) {
                $isOnline  = $digitalTherapy->dt_is_online;
                $isOffline = $digitalTherapy->dt_is_onsite;

                // When only Offline is selected send the offline form else send online form
                if (!$isOnline && $isOffline) {
                    $consentCategory = 2;
                } else {
                    $consentCategory = 1;
                }

                $deep_link_uri    = __(config('zevolifesettings.deeplink_uri.consent_form'), [
                    'id'    => (!empty($this->cronofySchedule->ws_id) ? $this->cronofySchedule->ws_id : 0),
                    'type'  => $consentCategory
                ]);

                if ($planAccess) {
                    $getConsentFormLogs = ConsentFormLogs::where(['user_id' => $this->user->id, 'ws_id' => $this->cronofySchedule->ws_id])->select('id')
                    ->count();
                    if ($this->string == 'consent-form-receive') {
                        $checkAlreadySend = Notification::leftJoin('notification_user', 'notification_user.notification_id', '=', 'notifications.id')
                            ->where('deep_link_uri', $deep_link_uri)
                            ->where('notification_user.user_id', $this->user->id)
                            ->where('title', 'Consent Form Received')
                            ->select('id')
                            ->count();

                        if (($checkAlreadySend > 0 || $getConsentFormLogs > 0) && ($this->from == 'api' || $this->from == 'portal')) {
                            return true;
                        } elseif ($getConsentFormLogs > 0 && $this->from == 'backend') {
                            return true;
                        }
                    }
                    
                    $message = trans('notifications.consent-form.' . $this->string . '.message', [
                        'service_name'  => $this->cronofySchedule->name,
                        'WS_first_name' => $getWS->first_name
                    ]);
    
                    $notificationData = [
                        'type'          => 'Auto',
                        'creator_id'    => $this->cronofySchedule->created_by,
                        'title'         => $title,
                        'message'       => $message,
                        'push'          => true,
                        'scheduled_at'  => now()->toDateTimeString(),
                        'deep_link_uri' => $deep_link_uri,
                        'is_mobile'     => $isMobile,
                        'is_portal'     => $isPortal,
                        'tag'           => 'consent-form',
                    ];
    
                    $notification = Notification::create($notificationData);
                    
                    $this->user->notifications()->attach($notification, ['sent' => true, 'sent_on' => now()->toDateTimeString()]);
    
                    // send notification to user
                    \Notification::send(
                        $this->user,
                        new SystemAutoNotification($notification, $this->string)
                    );
                }
            }
        }
    }
}
