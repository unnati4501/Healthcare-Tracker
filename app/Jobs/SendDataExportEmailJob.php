<?php

namespace App\Jobs;

use App\Events\SendDataExportEmailEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class SendDataExportEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Collection $email
     */
    private $email;

    /**
     * array $data
     */
    private $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($email, array $data)
    {
        $this->email = $email;
        $this->data   = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!empty($this->email)) {
            event(new SendDataExportEmailEvent($this->email, $this->data));
        }
    }
}
