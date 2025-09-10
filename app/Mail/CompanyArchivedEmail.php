<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use App\Models\AdminAlert;
use Log;
class CompanyArchivedEmail extends Mailable implements ShouldQueue
{
    use Queueable;

    public $company;
    public $user;
    public $tempPath;
    public $fileName;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($company, $user, $tempPath, $fileName)
    {
        $this->queue    = 'mail';
        $this->company  = $company;
        $this->user     = $user;
        $this->tempPath = $tempPath;
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
            $logo                = asset('assets/dist/img/zevo-white-logo.png');
            $redirection         = url('/');
            $emailTemplate       = AdminAlert::select('description')->where('title','Digital Therapy Record Deletion')->first();
            $description         = $emailTemplate->description;
            $description         = str_replace(array('#user_name#','#company_name#'), array('demo user',$this->company->name), $description);
            $address             = config('mail.from.address');
            $name                = config('mail.from.name');

            $dataArray = [
                'logo'                => $logo,
                'redirection'         => $redirection,
                'subject'             => $this->company->name. " company removed from the platform",
                'description'         => $description,
                'portaldomain'        => null,
                'brandingRedirection' => route('login'),
                'emailHeader'         => null,
                'isReseller'          => false,
                'signOffSignature'    => config('zevolifesettings.sign_off_signature')
            ];
            $this->from($address, $name)
                ->subject($dataArray['subject'])
                ->view('emails.admin-alert', $dataArray)
                ->attach($this->tempPath, [
                    'as'   => $this->fileName,
                    'mime' => 'application/vnd.ms-excel',
                ]);
            return true;
        } catch (\Exception $exception) {
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}
