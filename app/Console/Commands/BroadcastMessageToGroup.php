<?php

namespace App\Console\Commands;

use App\Jobs\BroadcastMessageToGroup as BroadcastMessageJob;
use App\Models\BroadcastMessage;
use Illuminate\Console\Command;

class BroadcastMessageToGroup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'group:broadcast';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send scheduled broadcast to group.';

    /**
     * BroadcastMessage model object
     *
     * @var BroadcastMessage $model
     */
    protected $model;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(BroadcastMessage $model)
    {
        parent::__construct();
        $this->model = $model;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $cronData = [
            'cron_name'  => class_basename(__CLASS__),
            'unique_key' => generateProcessKey(),
        ];
        cronlog($cronData);

        try {
            $appTimeZone = config('app.timezone');
            $now         = now($appTimeZone)->toDateTimeString();
            $this->model
                ->where('broadcast_messages.status', '1')
                ->where('broadcast_messages.type', 'scheduled')
                ->whereNotNull('broadcast_messages.scheduled_at')
                ->where('broadcast_messages.scheduled_at', '<=', $now)
                ->get()
                ->each(function ($message) {
                    dispatch(new BroadcastMessageJob($message));
                });

            cronlog($cronData, 1);
        } catch (\Exception $exception) {
            \Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
            $cronData['is_exception'] = 1;
            $cronData['log_desc']     = $exception->getMessage();
            cronlog($cronData, 1);
        }
    }
}
