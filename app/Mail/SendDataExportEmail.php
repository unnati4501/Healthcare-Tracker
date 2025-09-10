<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Log;

class SendDataExportEmail extends Mailable implements ShouldQueue
{
    use Queueable;

    public $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
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
            $appEnvironment      = app()->environment();
            $logo                = asset('assets/dist/img/zevo-white-logo.png');
            $redirection         = url('/');
            $address             = config('mail.from.address');
            $name                = config('mail.from.name');
            $foldername          = config('data-extract.excelfolderpath');
            $doSpaceDomain       = ($appEnvironment == 'production') ? config('data-extract.DO_SPACES_DOMAIN') : env('DO_SPACES_DOMAIN') . "/{$foldername}";
            $dataArray                = [
                'logo'                => $logo,
                'redirection'         => $redirection,
                'subject'             => "Irish Life Data Extract",
                'portaldomain'        => null,
                'brandingRedirection' => route('login'),
                'emailHeader'         => null,
                'isReseller'          => false,
                'signOffSignature'    => config('zevolifesettings.sign_off_signature'),
                'fileNames'           => $this->data['fileNames'],
                'path'                => $doSpaceDomain,
            ];

            return $this->from($address, $name)
                ->subject($dataArray['subject'])
                ->view('emails.irish-life-export', $dataArray);

        } catch (\Exception $exception) {
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}
