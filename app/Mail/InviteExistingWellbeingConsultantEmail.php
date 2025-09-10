<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;

/**
 * Class InviteExistingWellbeingConsultantEmail
 */
class InviteExistingWellbeingConsultantEmail extends Mailable implements ShouldQueue
{
    use Queueable;

    /**
     * @var array data
     */
    private $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->queue     = 'mail';
        $this->data      = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $logo                = asset('assets/dist/img/zevo-white-logo.png');
        $address             = config('mail.from.address');
        $redirection         = url('/');
        $wcName              = "";

        if (isset($this->data['name'])) {
            $wcName = $this->data['name'];
        }

        $dataArray = [
            'logo'                => $logo,
            'redirection'         => $redirection,
            'subject'             => "Registration Email - Zevo Health Portal",
            'description'         => '',
            'brandingRedirection' => route('login'),
            'isReseller'          => false,
            'signOffSignature'    => config('zevolifesettings.sign_off_signature'),
            'wcName'              => $wcName,
        ];

        return $this->from($address, $dataArray['subject'])
            ->subject($dataArray['subject'])
            ->view('emails.InviteExistingWellbeingConsultant', $dataArray);
    }
}
