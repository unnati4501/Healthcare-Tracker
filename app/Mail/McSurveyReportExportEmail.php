<?php

namespace App\Mail;

use App\Models\McSurveyReportExportLogs;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class McSurveyReportExportEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * McSurveyReportExportLogs model object
     *
     * @var McSurveyReportExportLogs
     **/
    public $logRecord;

    /**
     * Request data
     *
     * @var array
     **/
    public $payload;

    /**
     * Create a new message instance.
     *
     * @param McSurveyReportExportLogs $logRecord
     * @param array $payload
     * @return void
     */
    public function __construct(McSurveyReportExportLogs $logRecord, $payload)
    {
        $this->queue     = 'mail';
        $this->logRecord = $logRecord;
        $this->payload   = $payload;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $brandingRedirection = route('login');
        $logo                = asset('assets/dist/img/zevo-white-logo.png');
        $company             = $this->logRecord->company()->first();
        $companyId           = ((!is_null($company->parent_id)) ? $company->parent_id : $company->id);

        if (!empty($company) && $company->is_branding) {
            $brandingData        = getBrandingData($companyId);
            $brandingRedirection = getBrandingUrl($brandingRedirection, $brandingData->sub_domain);
            $logo                = $brandingData->company_logo;
        }

        $mail = $this->from(config('mail.from.address'), config('mail.from.name'))
            ->subject("Masterclass Survey Report")
            ->view('emails.survey-report', [
                'companyName'         => $this->payload['companyName'],
                'dateTime'            => $this->logRecord->process_started_at->format(config('zevolifesettings.date_format.default_datetime')),
                'brandingRedirection' => $brandingRedirection,
                'logo'                => $logo,
                'isReseller'          => ($company && ($company->is_reseller || $company->parent_id != null)),
                'emailHeader'         => (!empty($company) ? $company->email_header : null),
            ])
            ->attach($this->payload['spaceUrl'], [
                'as'   => $this->payload['fileName'],
                'mime' => 'application/vnd.ms-excel',
            ]);

        if ($mail) {
            $this->logRecord->status               = '2';
            $this->logRecord->process_completed_at = now(config('app.timezone'))->toDateTimeString();
        } else {
            $this->logRecord->status = '3';
        }
        $this->logRecord->save();
        return $mail;
    }
}
