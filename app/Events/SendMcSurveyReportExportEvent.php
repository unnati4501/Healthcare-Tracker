<?php

namespace App\Events;

use App\Models\McSurveyReportExportLogs;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendMcSurveyReportExportEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

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
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(McSurveyReportExportLogs $logRecord, $payload)
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
