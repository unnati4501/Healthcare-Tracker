<?php

namespace App\Jobs;

use App\Events\EventStatusChangeEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class SendEventStatusChangeEmailJob implements ShouldQueue
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
    public function __construct(array $emails, array $data)
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
            foreach ($this->emails as $emailChunks) {
                    $this->data['email'] = $emailChunks;
                    event(new EventStatusChangeEvent($this->data));
            }
        } else {
            event(new EventStatusChangeEvent($this->data));
        }
    }
}
