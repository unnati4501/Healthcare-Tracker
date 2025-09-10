<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\EventBookingLogs;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EventExpiredEmail extends Mailable implements ShouldQueue
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
        $logo                = asset('assets/dist/img/zevo-white-logo.png');
        $redirection         = url('/');
        $address             = config('mail.from.address');
        $name                = config('mail.from.name');
        $eventName           = $this->data['eventName'];
        $subject             = "{$eventName} - Event Expired";
        $portaldomain        = null;
        $isReseller          = false;
        $title               = $eventName . " - Event Expired" ;
        $signOffSignature    = config('zevolifesettings.sign_off_signature');
        $presenterName       = (!empty($this->data['presenterName']) ? $this->data['presenterName'] : null);
        $parentCompanyName   = null;
        $brandingRedirection = route('login');
        $user                = User::findByEmail($this->data['email']);
        $this->data['type']  = 'presenter';
        $role                = getUserRole($user);
        
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
                $portaldomain     = addhttp($brandingData->portal_domain);
                $signOffSignature = "The " . $name . " Team";
                $presenterName    = (!empty($this->data['presenterName'])) ? $this->data['presenterName'] : null;
                if ($role->slug == "user" && $role->default) {
                    $brandingRedirection  = $portaldomain . config('zevolifesettings.portal_static_urls.login');
                } else {
                    $brandingRedirection  = getBrandingUrl($brandingRedirection, $brandingData->sub_domain);
                }
            } elseif ($company->is_branding) {
                $brandingRedirection = getBrandingUrl($brandingRedirection, $brandingData->sub_domain);
                $logo                = $brandingData->company_logo;
            }
            $isReseller = ($company && ($company->is_reseller || $company->parent_id != null));
            $parentCompanyName = $brandingData->company_name;


            $isTikTokCompany = ($company->code == config('zevolifesettings.tiktok_company_code.'.$appEnvironment)[0]);
            if($isTikTokCompany){
                $signOffSignature    = config('zevolifesettings.sign_off_signature');
            }
        }
        
        $dataArray = [
            'logo'                => $logo,
            'redirection'         => $redirection,
            'title'               => $title,
            'type'                =>'presenter',
            'eventName'           => $eventName,
            'dateTime'            => $this->data['bookingDate'],
            'companyName'         => (!empty($this->data['companyName'])) ? $this->data['companyName'] : "",
            'parentCompanyName'   => $parentCompanyName,
            'portaldomain'        => $portaldomain,
            'brandingRedirection' => $brandingRedirection,
            'isReseller'          => $isReseller,
            'emailHeader'         => (!empty($company) ? $company->email_header : null),
            'signOffSignature'    => $signOffSignature,
            'eventStartTime'      => Carbon::parse($this->data['bookingDate'])->format('h:i A'),
            'eventStartDate'      => Carbon::parse($this->data['bookingDate'])->format('M d, Y'),
            'presenter'           => $presenterName,
            'userName'            => (!empty($user->first_name) ? $user->first_name : null),
        ];
        
        return $this
            ->from($address, $name)
            ->subject($subject)
            ->view('emails.eventexpired', $dataArray);
    }
}
