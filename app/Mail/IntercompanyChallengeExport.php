<?php

namespace App\Mail;

use App\Models\ChallengeExportHistory;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Log;

class IntercompanyChallengeExport extends Mailable implements ShouldQueue
{
    use Queueable;

    public $user;
    public $challenge;
    public $tempPath;
    public $payload;
    public $challengeExportHistory;
    public $fileName;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $challenge, $tempPath, $payload, $challengeExportHistory, $fileName)
    {
        $this->queue                  = 'mail';
        $this->user                   = $user;
        $this->challenge              = $challenge;
        $this->tempPath               = $tempPath;
        $this->payload                = $payload;
        $this->challengeExportHistory = $challengeExportHistory;
        $this->fileName               = $fileName;
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
            if ($this->payload['email'] != null) {
                $email = $this->payload['email'];
            }

            $processStartedAt = Carbon::parse($this->challengeExportHistory['process_started_at'])->format(config('zevolifesettings.date_format.date_time_12_hours'));
            $address          = config('mail.from.address');
            $name             = config('mail.from.name');
            $company          = $this->user->company->first();

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
                'subject'             => "Intercompany Challenge Report",
                'logo'                => asset('assets/dist/img/zevo-white-logo.png'),
                'challengeName'       => $this->challenge->title,
                'requestDatetime'     => $processStartedAt,
                'brandingRedirection' => route('login'),
                'isReseller'          => ($company && ($company->is_reseller || $company->parent_id != null)),
                'emailHeader'         => (!empty($company) ? $company->email_header : null),
                'userName'            => (!empty($this->user->first_name) ? $this->user->first_name : ''),
                'signOffSignature'    => config('zevolifesettings.sign_off_signature'),
            ];

            $mailSend = $this->from($address, $name)
                ->subject($data['subject'])
                ->view('emails.intercompanychallenge-report', $data)
                ->attach($this->tempPath, [
                    'as'   => $this->fileName,
                    'mime' => 'application/vnd.ms-excel',
                ]);

            $challengeExporthistory = ChallengeExportHistory::where('challenge_id', $this->challenge->id)
                ->orderby('id', 'DESC')
                ->first();

            if (!$mailSend) {
                $challengeExporthistory->status = '3';
                $challengeExporthistory->update();
                unlink($file);
                return false;
            }
        } catch (\Exception $exception) {
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}
