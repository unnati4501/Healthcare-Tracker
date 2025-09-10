<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserForgotPasswordEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private $user;
    private $token;
    private $appUser;
    private $xDeviceOs;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, $token = "", $appUser = "", $xDeviceOs = "")
    {
        $this->queue     = 'mail';
        $this->user      = $user;
        $this->token     = $token;
        $this->appUser   = $appUser;
        $this->xDeviceOs = $xDeviceOs;
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
                //'subject'             => "Reset Password Notification",
                'logo'                => asset('assets/dist/img/zevo-white-logo.png'),
                'brandingRedirection' => route('login'),
                'title'               => ':company_name Reset Password',
                'description'         => 'You recently requested to reset your password for your Portal account. Use the button below to reset it.',
                'signOffSignature'    => config('zevolifesettings.sign_off_signature'),
                'portaldomain'        => '',
                'userFirstName'       => $this->user->first_name,
            ];
            $company        = $this->user->company->first();
            $role           = getUserRole($this->user);
            $companyName    = "Zevo Health";
            $subject        = "Reset Password Notification - Zevo Health Portal";
            $address        = config('mail.from.address');
            $name           = config('mail.from.name');
            $appEnvironment = app()->environment();

            if (!empty($company)) {
                $data['emailHeader'] = $company->email_header;
                if ($role->slug == 'user' && $this->appUser == 'no' && $company->allow_portal) {
                    $data['reset_password_url'] = config('zevolifesettings.portal_static_urls.reset_password') . $this->token;
                } elseif (!empty($this->appUser) && $this->appUser == 'yes') {
                    $data['reset_password_url'] = route('password.reset', array($this->token, 'appUser' => $this->appUser));
                } else {
                    $data['reset_password_url'] = route('password.reset', $this->token);
                }

                $companyId    = ((!is_null($company->parent_id)) ? $company->parent_id : $company->id);
                $brandingData = getBrandingData($companyId);
                $data['logo'] = $brandingData->company_logo;

                if ($company->is_reseller || !is_null($company->parent_id)) {
                    $companyName = $brandingData->company_name;
                    if ($company->parent_id == null) {
                        $name = $company->name;
                    } else {
                        $childCompany = Company::select('name')->where('id', $companyId)->first();
                        $name         = $childCompany->name;
                    }
                    $subject                  = "Reset Password Notification - " . $name . " Portal";
                    $data['signOffSignature'] = "The " . $name . " Team";
                    $data['portaldomain']     = addhttp($brandingData->portal_domain);
                    $address                  = config('zevolifesettings.mail-front-email-address') . $brandingData->portal_domain;
                }

                if ($role->slug == 'user' && $this->appUser == 'no' && $company->allow_portal) {
                    $portalDomainWithHttp        = addhttp($brandingData->portal_domain);
                    $data['reset_password_url']  = $portalDomainWithHttp . $data['reset_password_url'];
                    $data['brandingRedirection'] = $portalDomainWithHttp . config('zevolifesettings.portal_static_urls.login');
                    $data['title']               = $brandingData->company_name . ' Reset Password';
                } elseif ($company->is_branding) {
                    $data['reset_password_url']  = getBrandingUrl($data['reset_password_url'], $brandingData->sub_domain);
                    $data['brandingRedirection'] = getBrandingUrl($data['brandingRedirection'], $brandingData->sub_domain);
                }

                $isTikTokCompany = ($company->code == config('zevolifesettings.tiktok_company_code.'.$appEnvironment)[0]);
                if($isTikTokCompany){
                    $data['signOffSignature']   = config('zevolifesettings.sign_off_signature');
                }
            } else {
                $data['reset_password_url'] = route('password.reset', $this->token);
            }

            $data['isReseller'] = ($company && ($company->is_reseller || $company->parent_id != null));
            $data['title']      = __($data['title'], [
                'company_name' => $companyName,
            ]);

            return $this->from($address, $name)
                ->subject($subject)
                ->view('emails.reset-password', $data);
        }
    }
}
