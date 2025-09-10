<?php

namespace App\Events;

use App\Models\ZcSurveyReportExportLogs;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendZcSurveyReportExportEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

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
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(ZcSurveyReportExportLogs $logRecord, $payload)
    {
        $this->logRecord = $logRecord;
        $this->payload   = $payload;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
