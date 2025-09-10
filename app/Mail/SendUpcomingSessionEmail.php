<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;

class SendUpcomingSessionEmail extends Mailable implements ShouldQueue
{
    use Queueable;

    private $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->queue    = 'mail';
        $this->data     = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        try {
            $logo                = asset('assets/dist/img/zevo-white-logo.png');
            $redirection         = url('/');
            $address             = config('mail.from.address');
            $name                = config('mail.from.name');
            $subject             = "You received a message from ".$this->data['wbsName']." regarding your ".$this->data['serviceName']." session";
            $portaldomain        = null;
            $signOffSignature    = config('zevolifesettings.sign_off_signature');
            $brandingRedirection = route('login');
            $user                = User::findByEmail($this->data['email']);
            $role                = getUserRole($user);
            $appEnvironment      = app()->environment();

            if (!empty($this->data['company'])) {
                $company      = Company::find($this->data['company']);
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
                    if ($role->slug == "user" && $role->default) {
                        $brandingRedirection = $portaldomain . config('zevolifesettings.portal_static_urls.login');
                    } else {
                        $brandingRedirection = getBrandingUrl($brandingRedirection, $brandingData->sub_domain);
                    }
                } elseif ($company->is_branding) {
                    $brandingRedirection = getBrandingUrl($brandingRedirection, $brandingData->sub_domain);
                    $logo                = $brandingData->company_logo;
                }

                if (!empty($company)) {
                    $isTikTokCompany = ($company->code == config('zevolifesettings.tiktok_company_code.'.$appEnvironment)[0]);
                    if($isTikTokCompany){
                        $signOffSignature    = config('zevolifesettings.sign_off_signature');
                    }
                }
            }

            $dataArray = [
                'email'               => $this->data['email'],
                'subject'             => $subject,
                'emailBody'           => (!empty($this->data['email_message']) ? $this->data['email_message'] : ""),
                'logo'                => $logo,
                'redirection'         => $redirection,
                'brandingRedirection' => $brandingRedirection,
                'emailHeader'         => (!empty($company) ? $company->email_header : null),
                'signOffSignature'    => $signOffSignature,
                'portaldomain'        => $portaldomain,
                'cronofySessionEmails'=> true 
            ];
            return $this
                ->from($address, $name)
                ->subject($subject)
                ->view('emails.session-email-logs', $dataArray);
        } catch (\Exception $exception) {
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}
