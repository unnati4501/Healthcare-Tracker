<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;

class AdminRegisterEmail extends Mailable implements ShouldQueue
{
    use Queueable;

    private $user;
    private $type;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $type = 'company')
    {
        $this->queue = 'mail';
        $this->user  = $user;
        $this->type  = $type;
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
                'user'                => $this->user,
                'email'               => $this->user->email,
                'token'               => encrypt($this->user->email),
                'logo'                => asset('assets/dist/img/zevo-white-logo.png'),
                'brandingRedirection' => route('login'),
                'setPasswordLink'     => config('zevolifesettings.portal_static_urls.reset_password'),
            ];
            $subject                  = "Admin Registration Email - Zevo Health Portal";
            $data['loginlink']        = route('login');
            $company                  = $this->user->company->first();
            $role                     = getUserRole($this->user);
            $data['subDescription']   = "You have been granted Administration access to the Zevo Health Portal. Start by clicking ‘Accept Invitation’ and lets get started.";
            $data['portaldomain']     = null;
            $data['signOffSignature'] = config('zevolifesettings.sign_off_signature');
            $address                  = config('mail.from.address');
            $name                     = config('mail.from.name');
            $footerRightMsg           = "Have a question?";
            $footerRightMsgSecond     = "Wellbeing specialists are available";
            $companyMail              = false;
            $userName                 = $this->user->first_name;
            $appEnvironment           = app()->environment();
            $token                    = $this->user->saveToken(['email' => $this->user->email]);

            if (!empty($company)) {
                if ($role->slug == 'user' && $company->allow_portal) {
                    $data['reset_password_url'] = config('zevolifesettings.portal_static_urls.reset_password') . $token;
                } else {
                    $data['reset_password_url'] = route('password.reset', $token);
                }

                $data['emailHeader'] = $company->email_header;
                $companyId           = ((!is_null($company->parent_id)) ? $company->parent_id : $company->id);
                $brandingData        = getBrandingData($companyId);
                $data['logo']        = $brandingData->company_logo;
                if ($company->is_reseller || !is_null($company->parent_id)) {
                    if ($company->parent_id == null) {
                        $name = $company->name;
                    } else {
                        $childCompany = Company::select('name')->where('id', $companyId)->first();
                        $name         = $childCompany->name;
                    }
                    $subject = "Admin Registration Email - " . $name . " Portal";
                    $address = config('zevolifesettings.mail-front-email-address') . $brandingData->portal_domain;
                }

                if ($role->group == 'zevo') {
                    $subDescription           = "You have been granted Administration access to the Zevo Health Portal. Start by clicking ‘Accept Invitation’ and lets get started.";
                    $footerRightMsg           = "Have a question?";
                    $footerRightMsgSecond     = "Wellbeing specialists are available";
                    $userName                 = $this->user->first_name;
                    $data['signOffSignature'] = config('zevolifesettings.sign_off_signature');
                } elseif ($role->group == 'company') {
                    $subDescription           = "You have been granted Administration access to the Zevo Health Portal. Start by clicking ‘Accept Invitation’ and lets get started.";
                    $footerRightMsg           = "Need more information";
                    $footerRightMsgSecond     = "We are available";
                    $companyMail              = true;
                    $userName                 = $this->user->first_name;
                    $data['signOffSignature'] = config('zevolifesettings.sign_off_signature');
                } elseif ($role->group == 'reseller') {
                    $footerRightMsg       = "More information";
                    $footerRightMsgSecond = "";
                    $userName             = $this->user->first_name;
                    if ($company->parent_id != null) {
                        $subDescription = "You have been granted Administration access to the " . $company->name . " Portal. Start by clicking ‘Accept Invitation’ and lets get started.";
                    } else {
                        $subDescription = "You have been granted Administration access to the " . $company->name . " Portal. Start by clicking 'Accept Invitation' and lets get started.";
                    }
                    $data['portaldomain']     = addhttp($brandingData->portal_domain);
                    $data['signOffSignature'] = "The " . $name . " Team";
                }

                if ($this->type == 'changed_user_permission') {
                    if ($role->slug == 'reseller_company_admin') {
                        $userName                 = $this->user->first_name;
                        $subDescription           = "You have been granted Administration access to the " . $name . " Portal. <br/><br/>Start by clicking 'Accept Invitation' and lets get started.";
                        $data['signOffSignature'] = "The " . $name . " Team";
                    } elseif ($role->slug == 'company_admin') {
                        $subDescription           = "You have been granted Administration access to the Zevo Health Portal. <br/><br/>Start by clicking 'Accept Invitation' and lets get started.";
                        $userName                 = $this->user->first_name;
                        $data['signOffSignature'] = config('zevolifesettings.sign_off_signature');
                    } else {
                        $userName                 = $this->user->first_name;
                        $subDescription           = "You have been granted Administration access to the " . $name . " Portal. <br/><br/>Start by clicking 'Accept Invitation' and lets get started.";
                        $data['signOffSignature'] = "The " . $name . " Team";
                    }
                    $footerRightMsg       = "Need more information";
                    $footerRightMsgSecond = "We are available";
                    $companyMail          = true;
                }

                if ($this->type == 'moderator') {
                    $subDescription       = "You have been granted admin access to " . $company->name . ". Click accept below to continue.";
                    $footerRightMsg       = "Need more information ";
                    $footerRightMsgSecond = "We are available";
                    $companyMail          = !($company->is_reseller || !is_null($company->parent_id));
                }
                $data['subDescription'] = $subDescription;

                if ($company->is_reseller || !is_null($company->parent_id)) {
                    // if user's role 'user' and 'default' is true then need to send mail based on company/parent company type
                    if ($role->slug == "user" && $role->default) {
                        $token                       = $this->user->saveToken(['email' => $this->user->email]);
                        $portalDomainWithHttp        = addhttp($brandingData->portal_domain);
                        $data['setPasswordLink']     = config('zevolifesettings.portal_static_urls.reset_password') . $token;
                        $data['brandingRedirection'] = $portalDomainWithHttp . config('zevolifesettings.portal_static_urls.login');
                    } else {
                        $data['setPasswordLink']     = getBrandingUrl($data['reset_password_url'], $brandingData->sub_domain);
                        $data['brandingRedirection'] = getBrandingUrl($data['brandingRedirection'], $brandingData->sub_domain);
                    }
                } elseif ($company->is_branding) {
                    // Is branding yes
                    $data['setPasswordLink']     = getBrandingUrl($data['reset_password_url'], $brandingData->sub_domain);
                    $data['brandingRedirection'] = getBrandingUrl($data['brandingRedirection'], $brandingData->sub_domain);
                }

                $isTikTokCompany = ($company->code == config('zevolifesettings.tiktok_company_code.'.$appEnvironment)[0]);
                if($isTikTokCompany){
                    $data['signOffSignature']    = config('zevolifesettings.sign_off_signature');
                }
            }
            $data['footerRightMsg']       = $footerRightMsg;
            $data['footerRightMsgSecond'] = $footerRightMsgSecond;
            $data['companyMail']          = $companyMail;
            $data['userName']             = $userName;
            $data['isReseller']           = ($company && ($company->is_reseller || $company->parent_id != null));
            $data['subject']              = $subject;

            return $this->from($address, $name)
                ->subject($data['subject'])
                ->view('emails.admin-registration', $data);
        }
    }
}
