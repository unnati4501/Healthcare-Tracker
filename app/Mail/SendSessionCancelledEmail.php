<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\User;
use App\Models\ScheduleUsers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendSessionCancelledEmail extends Mailable implements ShouldQueue
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
        $logo                = asset('assets/dist/img/zevo-white-logo.png');
        $redirection         = url('/');
        $address             = config('mail.from.address');
        $name                = config('mail.from.name');
        $subject             = "1:1 Session Cancelled - ".$this->data['userName']." and ".$this->data['wsName'];
        $portaldomain        = null;
        $signOffSignature    = config('zevolifesettings.sign_off_signature');
        $brandingRedirection = route('login');
        $userRecords                = User::findByEmail($this->data['email']);
        $role                = getUserRole($userRecords);
        $totalParticipants   = 0;
        $isGroup             = $this->data['isGroup'];
        $cancelledBy         = $this->data['cancelledBy'];
        $appEnvironment      = app()->environment();

        if ($isGroup) {
            if (isset($this->data['to']) && $this->data['to'] == 'user') {
                //Send Email to user when group session is cancelled by ZCA or WBS
                if (!empty($cancelledBy) && $cancelledBy == 'wellbeing_specialist') {
                    $subject    = $this->data['serviceName'] ." Session Cancelled by ".$this->data['wsName'];
                } elseif (!empty($cancelledBy) && $cancelledBy == 'company_admin' && !empty($this->data['companyName'])) {
                    $subject    = $this->data['serviceName'] ." Session Cancelled by ".$this->data['companyName']." Admin";
                }
            } else {
                //WS receives an email for the Group Session Cancelled  by WBS or ZCA
                if (!empty($this->data['companyName'])){
                    $subject    = "Session Cancelled by ".$this->data['companyName'] ." Admin";
                }
            }

            if (!empty($this->data['sessionId'])) {
                $participantUsers  = ScheduleUsers::where('session_id', $this->data['sessionId'])->get();
                $totalParticipants = $participantUsers->count();
            }
        }
            
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
                $signOffSignature = "The " . $name . " Team.";
                $portaldomain     = addhttp($brandingData->portal_domain);
                if ($role->slug == "user" && $role->default) {
                    $brandingRedirection  = $portaldomain . config('zevolifesettings.portal_static_urls.login');
                } else {
                    $brandingRedirection  = getBrandingUrl($brandingRedirection, $brandingData->sub_domain);
                }
            } elseif ($company->is_branding) {
                $brandingRedirection = getBrandingUrl($brandingRedirection, $brandingData->sub_domain);
                $logo                = $brandingData->company_logo;
            }

            if (!empty($company)) {
                $isTikTokCompany = ($company->code == config('zevolifesettings.tiktok_company_code.'.$appEnvironment)[0]);
                if($isTikTokCompany){
                    $signOffSignature    = config('zevolifesettings.sign_off_signature');
                }
            }
        }

            $dataArray = [
                'email'               => $this->data['email'],
                'subject'             => $subject,
                'cancelledReason'     => (!empty($this->data['cancelledReason']) ? $this->data['cancelledReason'] : ""),
                'serviceName'         => (!empty($this->data['serviceName']) ? $this->data['serviceName'] : ""),
                'userName'            => (!empty($this->data['userName']) ? $this->data['userName'] : ""),
                'wsName'              => (!empty($this->data['wsName']) ? $this->data['wsName'] : ""),
                'userFirstName'       => (!empty($this->data['userFirstName']) ? $this->data['userFirstName'] : ""),
                'wsFirstName'         => (!empty($this->data['wsFirstName']) ? $this->data['wsFirstName'] : ""),
                'logo'                => $logo,
                'redirection'         => $redirection,
                'brandingRedirection' => $brandingRedirection,
                'emailHeader'         => (!empty($company) ? $company->email_header : null),
                'signOffSignature'    => $signOffSignature,
                'eventDate'           => (!empty($this->data['eventDate']) ? $this->data['eventDate'] : ""),
                'eventTime'           => (!empty($this->data['eventTime']) ? $this->data['eventTime'] : ""),
                'duration'            => (!empty($this->data['duration']) ? $this->data['duration'] : ""),
                'portaldomain'        => $portaldomain,
                'cancelledBy'         => $this->data['cancelledBy'],
                'isGroup'             => $isGroup,
                'totalParticipants'   => $totalParticipants,
                'companyName'         => (!empty($this->data['companyName']) ? $this->data['companyName'] : ""),
            ];

            if($isGroup && isset($this->data['to']) && $this->data['to'] == 'user'){
                $mail = $this
                ->from($address, $name)
                ->subject($dataArray['subject'])
                ->view('emails.group-session-cancelled', $dataArray);
            }else{
                $mail = $this
                ->from($address, $name)
                ->subject($dataArray['subject'])
                ->view('emails.session-cancelled', $dataArray);
            }
            
            if (!empty($this->data['iCal'])) {
                $mail->attachData($this->data['iCal'], 'session-cancelled.ics', [
                    'mime' => 'text/calendar;charset=UTF-8;method=REQUEST',
                ]);
            }
            
            return $mail;
    }
}
