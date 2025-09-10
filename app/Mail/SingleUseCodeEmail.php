<?php

namespace App\Mail;

use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class SingleUseCodeEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Data which are required in email
     *
     * @var array $data
     */
    public $data;

    /**
     * Create a new event instance.
     *
     * @param array $data
     * @return void
     */
    public function __construct(array $data)
    {
        $this->queue = 'mail';
        $this->data  = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $appEnvironment      = app()->environment();
        $address             = config('mail.from.address');
        $name                = config('mail.from.name');
        $redirection         = url('/');
        $singleUseCode       = $this->data['singleUseCode'];
        $subject             = "Verify your Email";
        $signOffSignature    = config('zevolifesettings.sign_off_signature');
        $logo                = $emailHeader                = asset('assets/dist/img/zevo-white-logo.png');
        $user                = User::findByEmail($this->data['email']);
        $role                = getUserRole($user);
        $brandingRedirection = route('login');
        if (!empty($this->data['companyDetails'])) {
            $company      = Company::find($this->data['companyDetails']);
            $companyId    = ((!is_null($company->parent_id)) ? $company->parent_id : $company->id);
            $emailHeader  = $company->email_header;
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
                
                $signOffSignature = "The " . $name . " Team";
                $portaldomain = addhttp($brandingData->portal_domain);
                
                if ($role->slug == "user" && $role->default) {
                    $brandingRedirection = $portaldomain . config('zevolifesettings.portal_static_urls.login');
                } else {
                    $brandingRedirection = getBrandingUrl($brandingRedirection, $brandingData->sub_domain);
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
            'logo'                => $logo,
            'singleUseCode'       => $singleUseCode,
            'email'               => $this->data['email'],
            'signOffSignature'    => $signOffSignature,
            'emailHeader'         => $emailHeader,
            'brandingRedirection' => $brandingRedirection,
        ];

        return $this
            ->from($address, $name)
            ->subject($subject)
            ->view('emails.send-single-use-code', $dataArray);
    }
}
