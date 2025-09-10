<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendProjectSurveyEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private $user;
    private $dataParam;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, $dataParam = array())
    {
        $this->queue     = 'mail';
        $this->user      = $user;
        $this->dataParam = $dataParam;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if (null != $this->user &&
            isset($this->user->email) &&
            filter_var($this->user->email, FILTER_VALIDATE_EMAIL)
        ) {
            $data = [
                'email'               => $this->user->email,
                'subject'             => $this->dataParam['companyName'] . " " . $this->dataParam['surveyName'],
                'logo'                => $this->dataParam['logo'],
                'brandingRedirection' => route('login'),
                'surveyName'          => $this->dataParam['surveyName'],
                'userName'            => (!empty($this->user->first_name) ? $this->user->first_name : ''),
                'signOffSignature'    => config('zevolifesettings.sign_off_signature'),
                'portaldomain'        => ''
            ];

            $key                    = encrypt($this->dataParam['surveyLogId'] . ":" . $this->user->email);
            $data['takeSurveylink'] = route('projectSurveyResponse', $key);
            $address                = config('mail.from.address');
            $name                   = config('mail.from.name');
            $company                = $this->user->company->first();
            $appEnvironment         = app()->environment();

            if (!empty($company)) {
                $data['emailHeader'] = $company->email_header;

                $isTikTokCompany = ($company->code == config('zevolifesettings.tiktok_company_code.'.$appEnvironment)[0]);
                if($isTikTokCompany){
                    $data['signOffSignature']    = config('zevolifesettings.sign_off_signature');
                }
            }

            if (!empty($company) && ($company->is_reseller || !is_null($company->parent_id))) {
                $companyId    = ((!is_null($company->parent_id)) ? $company->parent_id : $company->id);
                $brandingData = getBrandingData($companyId);
                if ($company->parent_id == null) {
                    $name = $company->name;
                } else {
                    $childCompany = Company::select('name')->where('id', $companyId)->first();
                    $name         = $childCompany->name;
                }
                $data['signOffSignature']   = "The " . $name . " Team";
                $data['portaldomain']       = $brandingData->portal_domain;
                $data['subject']            = $name . " " . $this->dataParam['surveyName'];
                $address                    = config('zevolifesettings.mail-front-email-address') . $brandingData->portal_domain;
            }

            if (!empty($this->dataParam['sub_domain'])) {
                $data['takeSurveylink']      = getBrandingUrl($data['takeSurveylink'], $this->dataParam['sub_domain']);
                $data['brandingRedirection'] = getBrandingUrl($data['brandingRedirection'], $this->dataParam['sub_domain']);
            }
            $data['isReseller'] = ($company && ($company->is_reseller || $company->parent_id != null));
            return $this->from($address, $name)
                ->subject($data['subject'])
                ->view('emails.user-project-survey', $data);
        }
    }
}
