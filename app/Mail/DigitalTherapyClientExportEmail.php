<?php

namespace App\Mail;

use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Log;

class DigitalTherapyClientExportEmail extends Mailable implements ShouldQueue
{
    use Queueable;

    public $user;
    public $url;
    public $fileName;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $url, $fileName)
    {
        $this->queue    = 'mail';
        $this->user     = $user;
        $this->url      = $url;
        $this->fileName = $fileName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        try {
            $email = $this->user->email;
            $address          = config('mail.from.address');
            $name             = config('mail.from.name');
            $company          = $this->user->company->first();
            $processStartedAt = Carbon::parse(now())->format(config('zevolifesettings.date_format.default_datetime'));

            if (!empty($company) && ($company->is_reseller || !is_null($company->parent_id))) {
                $companyId    = ((!is_null($company->parent_id)) ? $company->parent_id : $company->id);
                $brandingData = getBrandingData($companyId);
                if ($company->parent_id == null) {
                    $name = $company->name;
                } else {
                    $childCompany = Company::select('name')->where('id', $companyId)->first();
                    $name         = $childCompany->name;
                }
                $address = config('zevolifesettings.mail-front-email-address') . $brandingData->portal_domain;
            }

            $data = [
                'email'               => $email,
                'subject'             => "Digital Therapy Client Report",
                'reportName'          => "digital therapy Client",
                'logo'                => asset('assets/dist/img/zevo-white-logo.png'),
                'brandingRedirection' => route('login'),
                'requestDatetime'     => $processStartedAt,
                'isReseller'          => ($company && ($company->is_reseller || $company->parent_id != null)),
                'emailHeader'         => (!empty($company) ? $company->email_header : null),
                'userName'            => (!empty($this->user->first_name) ? $this->user->first_name : ''),
                'signOffSignature'    => config('zevolifesettings.sign_off_signature'),
            ];

            $mailSend = $this->from($address, $name)
                ->subject($data['subject'])
                ->view('emails.export-report', $data)
                ->attach($this->url, [
                    'as'   => $this->fileName,
                    'mime' => 'application/vnd.ms-excel',
                ]);
            if (!$mailSend) {
                unlink($file);
                return false;
            }
        } catch (\Exception $exception) {
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}
