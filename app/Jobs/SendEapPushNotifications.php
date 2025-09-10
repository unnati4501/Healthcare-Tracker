<?php

namespace App\Jobs;

use App\Models\Company;
use App\Models\EAP;
use App\Models\Notification;
use App\Notifications\SystemAutoNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendEapPushNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var eap
     */
    protected $eap;
    protected $string;
    protected $users;
    protected $userName;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(EAP $eap, $string, $users, $userName = '')
    {
        $this->queue    = 'notifications';
        $this->eap      = $eap;
        $this->string   = $string;
        $this->users    = $users;
        $this->userName = $userName;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $title   = trans('notifications.eap.eap-added.title');
        $message = trans('notifications.eap.eap-added.message');
        $message = str_replace(['#eap_title#'], [$this->eap->title], $message);

        $notificationData = [
            'type'          => 'Auto',
            'creator_id'    => $this->eap->creator_id,
            'company_id'    => $this->eap->company_id,
            'title'         => $title,
            'message'       => $message,
            'push'          => true,
            'scheduled_at'  => now()->toDateTimeString(),
            'is_mobile'     => config('notification.eap.added.is_mobile'),
            'is_portal'     => config('notification.eap.added.is_portal'),
            'deep_link_uri' => $this->eap->deep_link_uri,
            'tag'           => 'eap',
        ];

        $notification = Notification::create($notificationData);

        $planAccess          = true;
        foreach ($this->users as $value) {
            $company = $value->company()->first();
            if ($company->is_reseller || !is_null($company->parent_id)) {
                $planAccess = getCompanyPlanAccess($value, 'supports');
                if ($company->parent_id != null) {
                    $company = Company::where('id', $company->parent_id)->first();
                }
            }
            $companyPlan = $company->companyplan()->get()->first();

            if ($planAccess) {
                if (isset($companyPlan->slug) && ($companyPlan->slug == 'portal-digital-therapy' || $companyPlan->slug == 'portal-standard-with-digital-therapy')) {
                    $value->notifications()->attach($notification, ['sent' => true, 'sent_on' => now()->toDateTimeString()]);

                    // send notification to all users
                    \Notification::send(
                        $value,
                        new SystemAutoNotification($notification, 'eap-created')
                    );
                }
            }
        }
    }
}
