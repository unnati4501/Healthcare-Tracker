<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EventPendingEmail extends Mailable implements ShouldQueue
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
        $subject           = "{$eventName} - Event Pending. Please confirm";
        $portaldomain      = null;
        $isReseller        = false;
        $title             = $eventName . " - Event Pending" ;
        $signOffSignature  = config('zevolifesettings.sign_off_signature');
        $presenterName     = (!empty($this->data['presenterName']) ? $this->data['presenterName'] : null);
        $parentCompanyName = null;
        $user              = User::findByEmail($this->data['email']);
        $eventAcceptUrl    = route('acceptEvent', $this->data['eventBookingId']);
        $eventRejectUrl    = route('rejectEvent', $this->data['eventBookingId']);
        
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
            'brandingRedirection' => route('login'),
            'isReseller'          => $isReseller,
            'signOffSignature'    => $signOffSignature,
            'eventStartTime'      => Carbon::parse($this->data['bookingDate'])->format('h:i A'),
            'eventStartDate'      => Carbon::parse($this->data['bookingDate'])->format('M d, Y'),
            'presenter'           => $presenterName,
            'userName'            => (!empty($user->first_name) ? $user->first_name : null),
            'eventAcceptUrl'      => $eventAcceptUrl,
            'eventRejectUrl'      => $eventRejectUrl,
        ];
        
        return $this
            ->from($address, $name)
            ->subject($subject)
            ->view('emails.eventpending', $dataArray);
    }
}
