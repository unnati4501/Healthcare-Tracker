<?php

namespace App\Mail;

use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ImportStatusMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $uploaded_file;
    public $validated_file;
    public $userName;
    public $module;
    public $company;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($emailData, $company = [])
    {
        $this->queue          = 'mail';
        $this->uploaded_file  = $emailData['uploaded_file'];
        $this->validated_file = (!empty($emailData['validated_file'])) ? $emailData['validated_file'] : "";
        $this->userName       = $emailData['userName'];
        $this->module         = $emailData['module'];
        $this->company        = $company;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = "Zevo Health - ".$this->module . ' Import Status';

        $data['logo']                = asset('assets/dist/img/zevo-white-logo.png');
        $data['brandingRedirection'] = route('login');
        $data['redirectURL']         = route('admin.imports.index');
        $address                     = config('mail.from.address');
        $name                        = config('mail.from.name');
        $data['userName']            = (!empty($this->userName) ? $this->userName : '');
        $data['signOffSignature']    = config('zevolifesettings.sign_off_signature');

        if (!empty($this->company)) {
            $data['emailHeader'] = $this->company->email_header;
            $companyId           = ((!is_null($this->company->parent_id)) ? $this->company->parent_id : $this->company->id);
            $brandingData        = getBrandingData($companyId);

            if ($this->company->is_branding) {
                $data['logo']                = $brandingData->company_logo;
                $data['brandingRedirection'] = getBrandingUrl($data['brandingRedirection'], $brandingData->sub_domain);
                $data['redirectURL']         = getBrandingUrl($data['redirectURL'], $brandingData->sub_domain);
            }

            if ($this->company->is_reseller || !is_null($this->company->parent_id)) {
                if ($this->company->parent_id == null) {
                    $name = $this->company->name;
                } else {
                    $childCompany = Company::select('name')->where('id', $companyId)->first();
                    $name         = $childCompany->name;
                }
                $address = config('zevolifesettings.mail-front-email-address') . $brandingData->portal_domain;
            }
        }
        $data['isReseller'] = ($this->company && ($this->company->is_reseller || $this->company->parent_id != null));
        return $this->from($address, $name)
            ->subject($subject)
            ->view('emails.userimportstatus', $data);
    }
}
