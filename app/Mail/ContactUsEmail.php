<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;

class ContactUsEmail extends Mailable implements ShouldQueue
{
    use Queueable;

    private $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
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
        $logo                = asset('assets/dist/img/zevo-white-logo.png');
        $redirection         = url('/');
        $attachment          = (!empty($this->data['attachment'][0]['url']) ? $this->data['attachment'][0]['url'] : null);
        
        $dataArray = [
            'logo'                => $logo,
            'redirection'         => $redirection,
            'subject'             => "Contact Us request received",
            'email'               => $this->data['email'],
            'name'                => $this->data['name'],
            'description'         => $this->data['description'],
            'portaldomain'        => null,
            'brandingRedirection' => route('login'),
            'emailHeader'         => null,
            'isReseller'          => false,
            'signOffSignature'    => config('zevolifesettings.sign_off_signature')
        ];
       
        $mail = $this->from($dataArray['email'], $dataArray['name'])
            ->subject($dataArray['subject'])
            ->view('emails.contact-us', $dataArray);
        
        if (!empty($attachment)) {
            $mail->attach($attachment);
        }
        return $mail;
    }
}
