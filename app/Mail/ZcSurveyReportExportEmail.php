<?php

namespace App\Mail;

use App\Models\ZcSurveyReportExportLogs;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class ZcSurveyReportExportEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * ZcSurveyReportExportLogs model object
     *
     * @var ZcSurveyReportExportLogs
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
     * @param ZcSurveyReportExportLogs $logRecord
     * @param array $payload
     * @return void
     */
    public function __construct(ZcSurveyReportExportLogs $logRecord, $payload)
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
            ->subject("Survey Report")
            ->view('emails.survey-report', [
                'companyName'         => $this->payload['companyName'],
                'dateTime'            => Carbon::parse($this->logRecord->process_started_at)->format(config('zevolifesettings.date_format.default_datetime')),
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
