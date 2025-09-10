<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendZCUserSurveyEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private $user;
    private $dataParam;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, $dataParam = array())
    {
        $this->queue     = 'mail';
        $this->user      = $user;
        $this->dataParam = $dataParam;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if (null != $this->user &&
            isset($this->user->email) &&
            filter_var($this->user->email, FILTER_VALIDATE_EMAIL)
        ) {
            $data = [
                'email'               => $this->user->email,
                'subject'             => "Wellbeing Survey - ".$this->dataParam['companyName'],
                'logo'                => $this->dataParam['logo'],
                'brandingRedirection' => route('login'),
                'companyName'         => $this->dataParam['companyName'],
                'fullName'            => (!empty($this->user->first_name) ? $this->user->first_name : ''),
                'signOffSignature'    => config('zevolifesettings.sign_off_signature'),
                'portaldomain'        => ''
            ];
            $appEnvironment         = app()->environment();
            $company                = $this->user->company->first();
            $role                   = getUserRole($this->user);
            $address                = config('mail.from.address');
            $name                   = config('mail.from.name');
            $key                    = encrypt($this->user->email . ":" . $this->dataParam['surveyLogId']);
            $data['takeSurveylink'] = route('responseSurvey', $key);

            if (!empty($company)) {
                $data['emailHeader'] = $company->email_header;
                $companyId           = ((!is_null($company->parent_id)) ? $company->parent_id : $company->id);
                $brandingData        = getBrandingData($companyId);
                $data['logo']        = $brandingData->company_logo;

                if ($company->is_reseller || !is_null($company->parent_id)) {
                    if ($company->parent_id == null) {
                        $name         = $company->name;
                    } else {
                        $childCompany = Company::select('name')->where('id', $companyId)->first();
                        $name         = $childCompany->name;
                    }
                    $data['companyName']        = $name;
                    $data['signOffSignature']   = "The " . $name . " Team";
                    $data['portaldomain']       = $brandingData->portal_domain;
                    $data['subject']            = "Wellbeing Survey - ".$name;
                    $address                    = config('zevolifesettings.mail-front-email-address') . $brandingData->portal_domain;
                }

                if ($company->is_reseller || !is_null($company->parent_id)) {
                    // if user's role 'user' and 'default' is true then need to send mail based on company/parent company type
                    if ($role->slug == "user" && $role->default) {
                        $portalDomainWithHttp        = addhttp($brandingData->portal_domain);
                        $data['takeSurveylink']      = $portalDomainWithHttp . config('zevolifesettings.portal_static_urls.survey') . '/' . $this->dataParam['surveyLogId'];
                        $data['brandingRedirection'] = $portalDomainWithHttp . config('zevolifesettings.portal_static_urls.login');
                    } else {
                        $data['takeSurveylink']      = getBrandingUrl($data['takeSurveylink'], $brandingData->sub_domain);
                        $data['brandingRedirection'] = getBrandingUrl($data['brandingRedirection'], $brandingData->sub_domain);
                    }
                } elseif ($company->is_branding) {
                    // Is branding yes
                    $data['takeSurveylink']      = getBrandingUrl($data['takeSurveylink'], $brandingData->sub_domain);
                    $data['brandingRedirection'] = getBrandingUrl($data['brandingRedirection'], $brandingData->sub_domain);
                }

                if (!empty($company)) {
                    $isTikTokCompany = ($company->code == config('zevolifesettings.tiktok_company_code.'.$appEnvironment)[0]);
                    if($isTikTokCompany){
                        $data['signOffSignature']   = config('zevolifesettings.sign_off_signature');
                    }
                }
            }
            $data['isReseller'] = ($company && ($company->is_reseller || $company->parent_id != null));
            return $this->from($address, $name)
                ->subject($data['subject'])
                ->view('emails.user-zc-survey', $data);
        }
    }
}
