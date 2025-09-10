<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\CronofySchedule;

class SendSessionNotesReminderEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * User model object
     *
     * @var User $user
     */
    protected $user;

    /**
     * Data which are required in email
     *
     * @var array $data
     */
    public $data;

    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param Event $event
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
        try {
            $logo                = asset('assets/dist/img/zevo-white-logo.png');
            $redirection         = url('/');
            $address             = config('mail.from.address');
            $name                = config('mail.from.name');
            $subject             = "Please add notes for the session below.";
            $portaldomain        = null;
            $signOffSignature    = config('zevolifesettings.sign_off_signature');
            $brandingRedirection = route('login');
            $userRecord          = User::findByEmail($this->data['userEmail']);
            $role                = getUserRole($userRecord);
            
            if (!empty($this->data['company'])) {
                $company      = Company::find($this->data['company']);
                $companyId    = ((!is_null($company->parent_id)) ? $company->parent_id : $company->id);
                $brandingData = getBrandingData($companyId);
                $logo         = $brandingData->company_logo;
                $redirection  = getBrandingUrl($redirection, $brandingData->sub_domain);

                if ($company->is_reseller || !is_null($company->parent_id)) {
                    if ($company->parent_id == null) {
                        $name = $company->name;
                    } else {
                        $childCompany = Company::select('name')->where('id', $companyId)->first();
                        $name         = $childCompany->name;
                    }
                    $address          = config('zevolifesettings.mail-front-email-address') . $brandingData->portal_domain;
                    $portaldomain     = addhttp($brandingData->portal_domain);
                    if ($role->slug == "user" && $role->default) {
                        $brandingRedirection = $portaldomain . config('zevolifesettings.portal_static_urls.login');
                    } else {
                        $brandingRedirection = getBrandingUrl($brandingRedirection, $brandingData->sub_domain);
                    }
                } elseif ($company->is_branding) {
                    $brandingRedirection = getBrandingUrl($brandingRedirection, $brandingData->sub_domain);
                    $logo                = $brandingData->company_logo;
                }
            }

            $dataArray = [
                'email'               => $this->data['email'],
                'subject'             => $subject,
                'company'             => !empty($company) ? $company->name : "",
                'wsName'              => (!empty($this->data['wsName']) ? $this->data['wsName'] : ""),
                'userEmail'           => (!empty($this->data['userEmail']) ? $this->data['userEmail'] : ""),
                'sessionDate'         => (!empty($this->data['sessionDate']) ? $this->data['sessionDate'] : ""),
                'addNotesUrl'         => (!empty($this->data['addNotesUrl']) ? $this->data['addNotesUrl'] : ""),
                'logo'                => $logo,
                'redirection'         => $redirection,
                'brandingRedirection' => $brandingRedirection,
                'emailHeader'         => (!empty($company) ? $company->email_header : null),
                'signOffSignature'    => $signOffSignature,
                'portaldomain'        => $portaldomain,
            ];
            
            $mail = $this
                ->from($address, $name)
                ->subject($subject)
                ->view('emails.session-notes-reminder', $dataArray);
            
            //Update the flag for session reminder email sent 
            CronofySchedule::where('id', $this->data['sessionId'])->update(['is_reminder_sent' => true]);

            return $mail;
        } catch (\Exception $exception) {
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}
