<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EventStatusChangeEmail extends Mailable implements ShouldQueue
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
        $logo              = asset('assets/dist/img/zevo-white-logo.png');
        $redirection       = url('/');
        $address           = config('mail.from.address');
        $name              = config('mail.from.name');
        $eventName         = $this->data['eventName'];
        $eventStatus       = !empty($this->data['eventStatus']) ? $this->data['eventStatus'] : null;
        $eventDate         = Carbon::parse($this->data['bookingDate'])->format('M d, Y');
        $subject           = !empty($this->data['subject']) ? $this->data['subject'] : "{$eventName} - {$eventDate} {$eventStatus}";
        $portaldomain      = null;
        $isReseller        = false;
        $signOffSignature  = config('zevolifesettings.sign_off_signature');
        $presenterName     = (!empty($this->data['presenterName']) ? $this->data['presenterName'] : null);
        $parentCompanyName = null;
        $duration          = (!empty($this->data['duration']) && strpos($this->data['duration'], ":") >= 1) ? convertToHoursMins(timeToDecimal($this->data['duration']), false, '%s %s') : '30 Minutes';
        $company           = Company::find($this->data['company']) ?? null;

        $dataArray = [
            'logo'                => $logo,
            'redirection'         => $redirection,
            'eventName'           => $eventName,
            'dateTime'            => $this->data['bookingDate'],
            'companyName'         => (!empty($this->data['companyName'])) ? $this->data['companyName'] : "",
            'parentCompanyName'   => $parentCompanyName,
            'portaldomain'        => $portaldomain,
            'brandingRedirection' => route('login'),
            'isReseller'          => $isReseller,
            'emailHeader'         => (!empty($company) ? $company->email_header : null),
            'signOffSignature'    => $signOffSignature,
            'eventStartTime'      => Carbon::parse($this->data['bookingDate'])->format('h:i A'),
            'eventStartDate'      => Carbon::parse($this->data['bookingDate'])->format('M d, Y'),
            'presenter'           => $presenterName,
            'duration'            => $duration,
            'eventStatus'         => $eventStatus,
            'messageType'         => (!empty($this->data['messageType']) ? $this->data['messageType'] : ""),
            'emailType'           => (!empty($this->data['emailType']) ? $this->data['emailType'] : ""),
            'timezone'            => (!empty($this->data['timezone']) ? $this->data['timezone'] : "UTC"),
        ];
        
        return $this
            ->from($address, $name)
            ->subject($subject)
            ->view('emails.eventstatuschange', $dataArray);
    }
}
