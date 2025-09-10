<?php

namespace App\Mail;

use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Log;

class SendCreditHistoryEmail extends Mailable implements ShouldQueue
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
            $appEnvironment   = app()->environment();
            $processStartedAt = Carbon::parse(now())->format(config('zevolifesettings.date_format.default_datetime'));
            $address          = config('mail.from.address');
            $name             = config('mail.from.name');
            $company          = $this->user->company->first();
            $signOffSignature = config('zevolifesettings.sign_off_signature');

            if (!empty($company) && ($company->is_reseller || !is_null($company->parent_id))) {
                $companyId    = ((!is_null($company->parent_id)) ? $company->parent_id : $company->id);
                $brandingData = getBrandingData($companyId);
                if ($company->parent_id == null) {
                    $name = $company->name;
                } else {
                    $childCompany = Company::select('name')->where('id', $companyId)->first();
                    $name         = $childCompany->name;
                }
                $signOffSignature   = "The " . $name . " Team";
                $address            = config('zevolifesettings.mail-front-email-address') . $brandingData->portal_domain;
            }

            if (!empty($company)){
                $isTikTokCompany = ($company->code == config('zevolifesettings.tiktok_company_code.'.$appEnvironment)[0]);
                if($isTikTokCompany){
                    $signOffSignature    = config('zevolifesettings.sign_off_signature');
                }
            }
            

            $data = [
                'email'               => $email,
                'subject'             => "Credit History",
                'logo'                => asset('assets/dist/img/zevo-white-logo.png'),
                'requestDatetime'     => $processStartedAt,
                'brandingRedirection' => route('login'),
                'isReseller'          => ($company && ($company->is_reseller || $company->parent_id != null)),
                'emailHeader'         => (!empty($company) ? $company->email_header : null),
                'userName'            => (!empty($this->user->first_name) ? $this->user->first_name : ''),
                'reportName'          => "credit history",
                'signOffSignature'    => $signOffSignature
            ];

            $mailSend = $this->from($address, $name)
                ->subject($data['subject'])
                ->view('emails.export-report', $data)
                ->attach($this->url, [
                    'as'   => $this->fileName,
                    'mime' => 'application/vnd.ms-excel',
                ]);

            if (!$mailSend) {
                //Unlink the attachement file from local
                removeFileToSpaces(config('zevolifesettings.report-export.intercomapnychallenge') . $this->fileName);
                return true;
            }
        } catch (\Exception $exception) {
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}
