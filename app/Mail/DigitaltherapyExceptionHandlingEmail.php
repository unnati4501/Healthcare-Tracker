<?php

namespace App\Mail;

use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;

/**
 * Class DigitaltherapyExceptionHandlingEmail
 */
class DigitaltherapyExceptionHandlingEmail extends Mailable implements ShouldQueue
{
    use Queueable;

    private $data;

    private $userEmailString;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data, $userEmail)
    {
        $this->queue     = 'mail';
        $this->data      = $data;
        $this->userEmailString = $userEmail;
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
        $companyName         = "";
        $wsName              = "";
        $userName            = "";
        $userEmail           = "";
        $errorDetails        = [];
        $signOffSignature    = config('zevolifesettings.sign_off_signature');

        if (isset($this->data['company'])) {
            $company        = $this->data['company'];
            $companyName    = $this->data['company']->name;

            if($company->is_reseller || !is_null($company->parent_id)){
                $companyId    = ((!is_null($company->parent_id)) ? $company->parent_id : $company->id);
                $brandingData = getBrandingData($companyId);
                if ($company->parent_id == null) {
                    $name = $company->name;
                } else {
                    $childCompany = Company::select('name')->where('id', $companyId)->first();
                    $name         = $childCompany->name;
                }
                $address            = config('zevolifesettings.mail-front-email-address') . $brandingData->portal_domain;
                $signOffSignature   = "The " . $name . " Team";
            }
        }

        if (isset($this->data['wsDetails'])) {
            $wsName = $this->data['wsDetails']->first_name . ' ' . $this->data['wsDetails']->last_name;
        }

        if (isset($this->data['errorDetails'])) {
            $errorDetails = $this->data['errorDetails'];
        }

        if (isset($this->data['userDetails'])) {
            $userName  = $this->data['userDetails']->first_name . ' ' . $this->data['userDetails']->last_name;
            $userEmail = $this->data['userDetails']->email;
        }

        $dataArray = [
            'logo'                => $logo,
            'redirection'         => $redirection,
            'subject'             => "Digital Therapy flow error occurred",
            'type'                => $this->data['type'],
            'description'         => strip_tags($this->data['message']),
            'brandingRedirection' => route('login'),
            'isReseller'          => false,
            'signOffSignature'    => $signOffSignature,
            'companyName'         => $companyName,
            'wsName'              => $wsName,
            'userName'            => $userName,
            'userEmail'           => $userEmail,
            'errorDetails'        => $errorDetails,
        ];

        return $this->from($address, $dataArray['subject'])
            ->subject($dataArray['subject'])
            ->view('emails.DigitaltherapyExceptionHandling', $dataArray);
    }
}
