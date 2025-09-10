<?php

namespace App\Console\Commands;

use App\Jobs\SendFeedPushNotification;
use App\Models\Feed;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateFeedStickFlag extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feed:removestickyflag';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update sticky flag to false when story is expired';

    /**
     * Feed model object
     *
     * @var Feed $feed
     */
    protected $feed;
    
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Feed $feed)
    {
        parent::__construct();
        $this->feed = $feed;
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
            $now   = now(config('appTimezone'))->toDateTimeString();
            $feeds = $this->feed->select('feeds.id')
                ->where('is_stick', true)
                ->whereRaw("feeds.end_date <= ?",[$now])
                ->get()
                ->pluck('id')
                ->toArray();
            
            if (!empty($feeds)) {
                // update sticky flag
                $this->feed->whereIn('id', $feeds)->update(['is_stick' => false]);
            }
            cronlog($cronData, 1);
        } catch (\Exception $exception) {
            \Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
            $cronData['is_exception'] = 1;
            $cronData['log_desc']     = $exception->getMessage();
            cronlog($cronData, 1);
        }
    }
}
