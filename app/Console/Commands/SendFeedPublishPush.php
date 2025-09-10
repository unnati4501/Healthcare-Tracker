<?php

namespace App\Console\Commands;

use App\Jobs\SendFeedPushNotification;
use App\Models\Feed;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendFeedPublishPush extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feed:sendpublishpush';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send feed publish to user based on user time zone.';

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
            $feeds = Feed::select('feeds.id', 'feeds.start_date', 'feeds.end_date', 'feeds.title', 'feeds.deep_link_uri', 'feeds.creator_id')
                ->where(\DB::raw("CONVERT_TZ(feeds.start_date, 'UTC', feeds.timezone)"), '<=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))
                ->where(\DB::raw("CONVERT_TZ(feeds.end_date, 'UTC', feeds.timezone)"), '>=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))
                ->get();

            foreach ($feeds as $feed) {
                $getFeedUsers = User::
                    join('user_team', 'users.id', '=', 'user_team.user_id')
                    ->join('companies', 'companies.id', '=', 'user_team.company_id')
                    ->join('feed_company', 'feed_company.company_id', '=', 'companies.id')
                    ->where("users.is_blocked", false)
                    ->where("feed_company.feed_id", "=", $feed->id)
                    ->where("users.created_at", "<", $feed->start_date)
                    ->select('users.id', 'users.first_name')
                    ->groupBy('users.id')
                    ->get();

                if ($getFeedUsers->count() > 0) {
                    $feedStartDate = Carbon::parse($feed->start_date)->setTimezone($feed->timezone)->toDateTimeString();

                    $feedEndDate2 = Carbon::parse($feedStartDate)->addHours(2)->toDateTimeString();

                    $feedEndDate1 = Carbon::parse($feed->end_date)->setTimezone($feed->timezone)->toDateTimeString();

                    if ($feedEndDate1 < $feedEndDate2) {
                        $feedEndDate = $feedEndDate1;
                    } else {
                        $feedEndDate = $feedEndDate2;
                    }

                    $currentTime = now($feed->timezone)->toDateTimeString();

                    if ($currentTime >= $feedStartDate && $currentTime <= $feedEndDate) {
                        $feedUserArray = [];
                        foreach ($getFeedUsers as $getFeedUser) {
                            $alreadySendFeed = DB::table('feed_user_publish_notification')
                                ->select('id')
                                ->where('feed_id', $feed->id)
                                ->where('user_id', $getFeedUser->id)
                                ->first();

                            if (empty($alreadySendFeed)) {
                                $feedUserArray[] = $getFeedUser;
                                DB::table('feed_user_publish_notification')->insert([
                                    'feed_id'   => $feed->id,
                                    'user_id'   => $getFeedUser->id,
                                    'is_pushed' => true,
                                    'pushed_at' => now()->toDateTimeString(),
                                ]);
                            }
                        }
                        if (!empty($feedUserArray)) {
                            // dispatch job to send feed published push notification to userss
                            \dispatch(new SendFeedPushNotification($feed, "feed-publish", $feedUserArray));
                        }
                    }
                }
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
