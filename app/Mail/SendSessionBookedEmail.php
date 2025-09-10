<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\User;
use App\Models\ScheduleUsers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Log;

class SendSessionBookedEmail extends Mailable implements ShouldQueue
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
            $toEmail             = $this->data['to'];
            $isGroup             = $this->data['isGroup'];
            $bookedBy            = (isset($this->data['bookedBy']) ? $this->data['bookedBy'] : null);
            $subject             = "1:1 Session Booked - ".$this->data['userName']." and ".$this->data['wsName'];
            $totalParticipants   = 0;
            $userRecord                = User::findByEmail($this->data['email']);
            $role                = getUserRole($userRecord);
            $isRescheduled       = $this->data['isRescheduled'];
            $appEnvironment      = app()->environment();

            if (!$isRescheduled){
                $action = 'Booked';
            }else{
                $action = 'Rescheduled';
            }

            if ($isGroup) {
                if ($toEmail == 'user') {
                    //Send Email to user when group session is booked by ZCA or WBS
                    if (!empty($bookedBy) && $bookedBy == 'wellbeing_specialist') {
                        $subject    = $this->data['serviceName'] ." Session ".$action." by ".$this->data['wsName'];
                    } elseif (!empty($bookedBy) && $bookedBy == 'company_admin' && !empty($this->data['companyName'])) {
                        $subject    = $this->data['serviceName'] ." Session ".$action." by ".$this->data['companyName']." Admin";
                    }
                } else {
                    //WS receives an email for the Group Session Cancelled  by WBS or ZCA
                    if (!empty($this->data['companyName'])){
                        $subject    = "Session ".$action." by ".$this->data['companyName'] ." Admin";
                    }
                }
                
                if (!empty($this->data['sessionId'])) {
                    $participantUsers  = ScheduleUsers::where('session_id', $this->data['sessionId'])->get();
                    $totalParticipants = $participantUsers->count();
                }
            }else {
                $subject = "1:1 Session ".$action." - ".$this->data['userName']." and ".$this->data['wsName'];
            }
            $portaldomain        = null;
            $signOffSignature    = config('zevolifesettings.sign_off_signature');
            $brandingRedirection = route('login');
            
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
                'serviceName'         => (!empty($this->data['serviceName']) ? $this->data['serviceName'] : ""),
                'userFirstName'       => (!empty($this->data['userFirstName']) ? $this->data['userFirstName'] : ""),
                'userName'            => (!empty($this->data['userName']) ? $this->data['userName'] : ""),
                'wsFirstName'         => (!empty($this->data['wsFirstName']) ? $this->data['wsFirstName'] : ""),
                'wsName'              => (!empty($this->data['wsName']) ? $this->data['wsName'] : ""),
                'logo'                => $logo,
                'redirection'         => $redirection,
                'brandingRedirection' => $brandingRedirection,
                'emailHeader'         => (!empty($company) ? $company->email_header : null),
                'signOffSignature'    => $signOffSignature,
                'eventDate'           => (!empty($this->data['eventDate']) ? $this->data['eventDate'] : ""),
                'eventTime'           => (!empty($this->data['eventTime']) ? $this->data['eventTime'] : ""),
                'duration'            => (!empty($this->data['duration']) ? $this->data['duration'] : ""),
                'portaldomain'        => $portaldomain,
                'companyName'         => (!empty($this->data['companyName']) ? $this->data['companyName'] : ""),
                'toEmail'             => $toEmail,
                'joinSessionLink'     => (!empty($this->data['location']) ? $this->data['location'] : ""),
                'isRescheduled'       => $isRescheduled,
                'isGroup'             => $isGroup,
                'totalParticipants'   => $totalParticipants,
                'isOnline'            => $this->data['isOnline'],
                'sessionNotes'        => (!empty($this->data['notes']) ? $this->data['notes'] : ""),
            ];

            if($isGroup && $toEmail == 'user'){
                $mail = $this
                ->from($address, $name)
                ->subject($dataArray['subject'])
                ->view('emails.group-session-booked', $dataArray);
            }else{
                $mail = $this
                ->from($address, $name)
                ->subject($dataArray['subject'])
                ->view('emails.session-booked', $dataArray);
            }

            if (!empty($this->data['iCal']) && !empty($toEmail) && $toEmail == 'user') {
                $mail->attachData($this->data['iCal'], ($this->data['isRescheduled']) ? 'session-reschduled.ics' : 'session-booked.ics', [
                    'mime' => 'text/calendar;charset=UTF-8;method=REQUEST',
                ]);
            }
            
            return $mail;
        } catch (\Exception $exception) {
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}
