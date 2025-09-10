<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use App\Models\AdminAlert;

class AdminAlertEmail extends Mailable implements ShouldQueue
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
        $address             = config('mail.from.address');
        $name                = config('mail.from.name');
        if (!empty($this->data['action']) && $this->data['action'] == 'wbs_profile_verification') {
            $emailTemplate       = AdminAlert::select('description')->where('title',config('zevolifesettings.admin_alerts.wbs_profile_verification_title'))->first();
            $description         = $emailTemplate->description;
            $ws_name             = $this->data['ws_name'] ?? null;
            $ws_email            = $this->data['ws_email'] ?? null;
            $description         = str_replace(array('#user_name#', '#wellbeing_specialist_name#', '#wellbeing_specialist_email#'), array($this->data['alertName'], $ws_name, $ws_email), $description);
        } else {
            $emailTemplate       = AdminAlert::select('description')->where('title', config('zevolifesettings.admin_alerts.next_to_kin_info_title'))->first();
            $description         = $emailTemplate->description;
            $client_name         = $this->data['client_name'] ?? null;
            $client_email        = $this->data['client_email'] ?? null;
            $ws_name             = $this->data['ws_name'] ?? null;
            $ws_email            = $this->data['ws_email'] ?? null;
            $description         = str_replace(array('#user_name#', '#client_name#', '#client_email#', '#wellbeing_specialist_name#', '#wellbeing_specialist_email#'), array($this->data['alertName'] ,$client_name, $client_email, $ws_name, $ws_email), $description);
        }
        
        $dataArray = [
            'logo'                => $logo,
            'redirection'         => $redirection,
            'subject'             => !empty($this->data['subject']) ? $this->data['subject'] : "Access kin Information",
            'email'               => $this->data['email'],
            'name'                => $this->data['name'],
            'description'         => $description,
            'portaldomain'        => null,
            'brandingRedirection' => route('login'),
            'emailHeader'         => null,
            'isReseller'          => false,
            'signOffSignature'    => config('zevolifesettings.sign_off_signature')
        ];
       
        $mail = $this->from($address, $name)
            ->subject($dataArray['subject'])
            ->view('emails.admin-alert', $dataArray);
        
        if (!empty($attachment)) {
            $mail->attach($attachment);
        }
        return $mail;
    }
}
