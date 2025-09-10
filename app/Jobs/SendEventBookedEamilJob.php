<?php

namespace App\Jobs;

use App\Events\EventBookedEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class SendEventBookedEamilJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Collection $emails
     */
    private $emails;

    /**
     * array $data
     */
    private $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Collection $emails, array $data)
    {
        $this->emails = $emails;
        $this->data   = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!empty($this->emails)) {
            // prepare iCal invite array for registered users
            $usersiCal = ((!empty($this->data['iCalData'])) ? generateiCal($this->data['iCalData']) : "");

            foreach ($this->emails as $emailChunks) {
                foreach ($emailChunks as $email) {
                    $this->data['email'] = $email;
                    if (!empty($usersiCal)) {
                        $this->data['iCal'] = $usersiCal;
                    }
                    event(new EventBookedEvent($this->data));
                }
            }
        }
    }
}
