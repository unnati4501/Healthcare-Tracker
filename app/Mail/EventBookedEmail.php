<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EventBookedEmail extends Mailable implements ShouldQueue
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
        $appEnvironment         = app()->environment();
        $logo                   = asset('assets/dist/img/zevo-white-logo.png');
        $redirection            = url('/');
        $address                = config('mail.from.address');
        $name                   = config('mail.from.name');
        $eventName              = $this->data['eventName'];
        $subject                = isset($this->data['subject']) && !empty($this->data['subject']) ? $this->data['subject'] : "{$eventName} - Event Booked";
        $portaldomain           = null;
        $isReseller             = false;
        $title                  = $eventName . " - Event Booked" ;
        $signOffSignature       = config('zevolifesettings.sign_off_signature');
        $duration               = (!empty($this->data['duration']) && strpos($this->data['duration'], ":") >= 1) ? convertToHoursMins(timeToDecimal($this->data['duration']), false, '%s %s') : '30 Minutes';
        $presenterName          = (!empty($this->data['presenterName']) ? $this->data['presenterName'] : null);
        $parentCompanyName      = null;
        $brandingRedirection    = route('login');
        $user                   = User::findByEmail($this->data['email']);
        $role                   = getUserRole($user);
        
        if ($this->data['type'] == 'user') {
            $subject           = "{$eventName} - Event Registration";
        }
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
                $duration         = (!empty($this->data['duration']) && strpos($this->data['duration'], ":") >= 1) ? convertToHoursMins(timeToDecimal($this->data['duration']), false, '%s %s') : '30 Minutes';
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
            if($isTikTokCompany || (!empty($this->data['type']) && $this->data['type'] == 'presenter')){
                $signOffSignature    = config('zevolifesettings.sign_off_signature');
            }
        }

        $dataArray = [
            'logo'                => $logo,
            'redirection'         => $redirection,
            'type'                => $this->data['type'],
            'title'               => $title,
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
            'duration'            => $duration,
            'presenter'           => $presenterName,
            'userName'            => (!empty($user->first_name) ? $user->first_name : null),
            'emailNotes'          => (!empty($this->data['emailNotes']) ? $this->data['emailNotes'] : null),
            'emailType'           => (!empty($this->data['emailType']) ? $this->data['emailType'] : null),
            'timezone'            => (!empty($this->data['timezone']) ? $this->data['timezone'] : null),
        ];

        $mail = $this
            ->from($address, $name)
            ->subject($subject)
            ->view('emails.eventbooked', $dataArray);

        if (in_array($this->data['type'], ["user", "admin"])) {
            $mail->attachData($this->data['iCal'], $eventName . ' invite.ics', [
                'mime' => 'text/calendar;charset=UTF-8;method=REQUEST',
            ]);
        }

        if (in_array($this->data['type'], ["presenter"]) && !empty($this->data['iCal'])) {
            $mail->attachData($this->data['iCal'], $eventName . ' invite.ics', [
                'mime' => 'text/calendar;charset=UTF-8;method=REQUEST',
            ]);
        }

        return $mail;
    }
}
