<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserChangePasswordEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private $user;
    private $newPassword;
    private $xDeviceOs;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, $newPassword = "", $xDeviceOs = "")
    {
        $this->queue       = 'mail';
        $this->user        = $user;
        $this->newPassword = $newPassword;
        $this->xDeviceOs   = $xDeviceOs;
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
                'user_name'           => $this->user->first_name . " " . $this->user->last_name,
                'password'            => $this->newPassword,
                'subject'             => "Change Password Notification - Zevo Health Portal",
                'signOffSignature'    => config('zevolifesettings.sign_off_signature'),
                'logo'                => asset('assets/dist/img/zevo-white-logo.png'),
                'brandingRedirection' => route('login'),
                'portaldomain'        => '',
            ];

            $data['title'] = 'Zevo Health Account Change Password';

            $company        = $this->user->company->first();
            $role           = getUserRole($this->user);
            $address        = config('mail.from.address');
            $name           = config('mail.from.name');
            $appEnvironment = app()->environment();

            if (!empty($company) && ($company->is_reseller || !is_null($company->parent_id))) {
                $companyId    = ((!is_null($company->parent_id)) ? $company->parent_id : $company->id);
                $brandingData = getBrandingData($companyId);
                if ($company->parent_id == null) {
                    $name = $company->name;
                } else {
                    $childCompany = Company::select('name')->where('id', $companyId)->first();
                    $name         = $childCompany->name;
                }
                $data['signOffSignature'] = "The " . $name . " Team";
                $data['portaldomain']     = addhttp($brandingData->portal_domain);
                $data['subject']          = "Change Password Notification - " . $name . " Portal";
                $address                  = config('zevolifesettings.mail-front-email-address') . $brandingData->portal_domain;
            }

            if (!empty($company) && $company->is_branding) {
                $companyId                   = ((!is_null($company->parent_id)) ? $company->parent_id : $company->id);
                $brandingData                = getBrandingData($companyId);
                $data['logo']                = $brandingData->company_logo;
                $data['brandingRedirection'] = getBrandingUrl($data['brandingRedirection'], $brandingData->sub_domain);
                if ($company->is_reseller || !is_null($company->parent_id)) {
                    // if user's role 'user' and 'default' is true then need to send mail based on company/parent company type
                    if ($role->slug == "user" && $role->default) {
                        $data['title'] = 'Portal Account Change Password';
                    }
                }
            }

            if (!empty($company)) {
                $data['emailHeader'] = $company->email_header;

                $isTikTokCompany = ($company->code == config('zevolifesettings.tiktok_company_code.'.$appEnvironment)[0]);
                if($isTikTokCompany){
                    $data['signOffSignature']   = config('zevolifesettings.sign_off_signature');
                }
            }

            $data['isReseller'] = ($company && ($company->is_reseller || $company->parent_id != null));
            return $this->from($address, $name)
                ->subject($data['subject'])
                ->view('emails.change-password', $data);
        }
    }
}
