<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ExceptionOccured extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The body of the message.
     *
     * @var string
     */
    public $content;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($content)
    {
        $this->content = $content;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $appEnvironment = app()->environment();
        $time           = now()->toDateTimeString();
        $subject        = config('app.name') . " - Error exception for $appEnvironment  environment on $time ( UTC )";
        return $this->subject($subject)->view('emails.exception')->with('content', $this->content);
    }
}
