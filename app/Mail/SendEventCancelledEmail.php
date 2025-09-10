<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\Event;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendEventCancelledEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * User model object
     *
     * @var User $user
     */
    protected $user;

    /**
     * Data which are required in email
     *
     * @var array $data
     */
    public $data;

    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param Event $event
     * @return void
     */
    public function __construct(User $user, array $data)
    {
        $this->queue = 'mail';
        $this->user  = $user;
        $this->data  = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if (!is_null($this->user) &&
            !empty($this->user->email) &&
            filter_var($this->user->email, FILTER_VALIDATE_EMAIL)
        ) {
            $appEnvironment      = app()->environment();
            $logo                = asset('assets/dist/img/zevo-white-logo.png');
            $redirection         = url('/');
            $company             = $this->user->company->first();
            $address             = config('mail.from.address');
            $name                = config('mail.from.name');
            $portaldomain        = null;
            $signOffSignature    = config('zevolifesettings.sign_off_signature');
            $moderatorName       = null;
            $brandingRedirection = route('login');
            $role                = getUserRole($this->user);

            if (!empty($company)) {
                $companyId    = ((!is_null($company->parent_id)) ? $company->parent_id : $company->id);
                $brandingData = getBrandingData($companyId);
                $logo         = $brandingData->company_logo;
                $redirection  = getBrandingUrl($redirection, $brandingData->sub_domain);

                if ($company->is_reseller || !is_null($company->parent_id)) {
                    if ($company->parent_id == null) {
                        $name = $company->name;
                    } else {
                        $childCompany = Company::select('name')->where('id', $companyId)->first();
                        $name         = $childCompany->name;
                    }
                    $address          = config('zevolifesettings.mail-front-email-address') . $brandingData->portal_domain;
                    $signOffSignature = "The " . $name . " Team.";
                    $portaldomain     = addhttp($brandingData->portal_domain);
                    $moderatorName    = (!empty($this->data['moderatorName']) ? $this->data['moderatorName'] : "");
                    if ($role->slug == "user" && $role->default) {
                        $brandingRedirection  = $portaldomain . config('zevolifesettings.portal_static_urls.login');
                    } else {
                        $brandingRedirection  = getBrandingUrl($brandingRedirection, $brandingData->sub_domain);
                    }
                } elseif ($company->is_branding) {
                    $brandingRedirection = getBrandingUrl($brandingRedirection, $brandingData->sub_domain);
                    $logo                = $brandingData->company_logo;
                }

                $isTikTokCompany = ($company->code == config('zevolifesettings.tiktok_company_code.'.$appEnvironment)[0]);
                if($isTikTokCompany){
                    $signOffSignature    = config('zevolifesettings.sign_off_signature');
                }
            }

            $dataArray = [
                'email'               => $this->user->email,
                'subject'             => (!empty($this->data['subject']) ? $this->data['subject'] : " - Event Cancelled"),
                'message_text'        => (!empty($this->data['message']) ? $this->data['message'] : ""),
                'logo'                => $logo,
                'redirection'         => $redirection,
                'brandingRedirection' => $brandingRedirection,
                'isReseller'          => ($company && ($company->is_reseller || $company->parent_id != null)),
                'emailHeader'         => (!empty($company) ? $company->email_header : null),
                'signOffSignature'    => $signOffSignature,
                'portaldomain'        => $portaldomain,
                'moderatorName'       => $moderatorName,
            ];

            $mail = $this
                ->from($address, $name)
                ->subject($dataArray['subject'])
                ->view('emails.eventcancelled', $dataArray);

            if (!empty($this->data['iCal'])) {
                $mail->attachData($this->data['iCal'], 'event-cancelled.ics', [
                    'mime' => 'text/calendar;charset=UTF-8;method=REQUEST',
                ]);
            }

            return $mail;
        }
    }
}
