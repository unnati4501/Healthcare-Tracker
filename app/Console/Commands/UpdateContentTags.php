<?php

namespace App\Console\Commands;

use App\Models\MeditationTrack;
use App\Models\Feed;
use App\Models\Recipe;
use App\Models\Course;
use App\Models\Webinar;
use App\Models\Podcast;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateContentTags extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'content:updatetags';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update tags for all contents';

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
            // Meditation track
            MeditationTrack::where('caption', '!=', null)->update(['caption' => null]);
            $allRecentlyAddedTrackIds   = MeditationTrack::select('id')->orderByDesc('created_at')->take(5)->pluck('id')->toArray();
            $allPopularTrackIds         = MeditationTrack::select('id','title')->orderByDesc('view_count')->where('view_count', '>' ,0)->whereNotIn('id', $allRecentlyAddedTrackIds)->take(5)->pluck('id')->toArray();
            MeditationTrack::whereIn('id', $allRecentlyAddedTrackIds)->update(['caption' => 'New']);
            MeditationTrack::whereIn('id', $allPopularTrackIds)->update(['caption' => 'Popular']);

            // Webinar
            Webinar::where('caption', '!=', null)->update(['caption' => null]);
            $allRecentlyAddedWebinarIds   = Webinar::select('id')->orderByDesc('created_at')->take(5)->pluck('id')->toArray();
            $allPopularWebinarIds         = Webinar::select('id','title')->orderByDesc('view_count')->where('view_count', '>' ,0)->whereNotIn('id', $allRecentlyAddedWebinarIds)->take(5)->pluck('id')->toArray();
            Webinar::whereIn('id', $allRecentlyAddedWebinarIds)->update(['caption' => 'New']);
            Webinar::whereIn('id', $allPopularWebinarIds)->update(['caption' => 'Popular']);

            // Podcast
            Podcast::where('caption', '!=', null)->update(['caption' => null]);
            $allRecentlyAddedPodcastIds   = Podcast::select('id')->orderByDesc('created_at')->take(5)->pluck('id')->toArray();
            $allPopularPodcastIds         = Podcast::select('id','title')->orderByDesc('view_count')->where('view_count', '>' ,0)->whereNotIn('id', $allRecentlyAddedPodcastIds)->take(5)->pluck('id')->toArray();
            Podcast::whereIn('id', $allRecentlyAddedPodcastIds)->update(['caption' => 'New']);
            Podcast::whereIn('id', $allPopularPodcastIds)->update(['caption' => 'Popular']);

            // Recipe
            Recipe::where('caption', '!=', null)->update(['caption' => null]);
            $allRecentlyAddedRecipeIds   = Recipe::select('id')->orderByDesc('created_at')->take(5)->pluck('id')->toArray();
            $allPopularRecipeIds         = Recipe::select('id','title')->orderByDesc('view_count')->where('view_count', '>' ,0)->whereNotIn('id', $allRecentlyAddedRecipeIds)->take(5)->pluck('id')->toArray();
            Recipe::whereIn('id', $allRecentlyAddedRecipeIds)->update(['caption' => 'New']);
            Recipe::whereIn('id', $allPopularRecipeIds)->update(['caption' => 'Popular']);
            
            // Feed
            Feed::where('caption', '!=', null)->update(['caption' => null]);
            $allRecentlyAddedFeedIds   = Feed::select('id')->orderByDesc('created_at')->take(5)->pluck('id')->toArray();
            $allPopularFeedIds         = Feed::select('id','title')->orderByDesc('view_count')->where('view_count', '>' ,0)->whereNotIn('id', $allRecentlyAddedFeedIds)->take(5)->pluck('id')->toArray();
            Feed::whereIn('id', $allRecentlyAddedFeedIds)->update(['caption' => 'New']);
            Feed::whereIn('id', $allPopularFeedIds)->update(['caption' => 'Popular']);

            // Course
            Course::where('caption', '!=', null)->update(['caption' => null]);
            $allRecentlyAddedCourseIds   = Course::select('id')->where('status',true)->orderByDesc('created_at')->take(5)->pluck('id')->toArray();
            $allPopularCourseIds         = Course::leftJoin('user_course', 'user_course.course_id', '=', 'courses.id')->select('courses.id','courses.title', DB::raw("(SELECT COUNT(NULLIF(liked, 0)) FROM user_course WHERE course_id = courses.id) AS most_liked"))->whereNotIn('courses.id', $allRecentlyAddedCourseIds)->where('courses.status',true)->havingRaw("`most_liked` > 0 ")->orderByRaw("`most_liked` DESC")->groupBy('user_course.course_id')->take(5)->pluck('id')->toArray();
            Course::whereIn('id', $allRecentlyAddedCourseIds)->update(['caption' => 'New']);
            Course::whereIn('id', $allPopularCourseIds)->update(['caption' => 'Popular']);
            
            cronlog($cronData, 1);
        } catch (\Exception $exception) {
            \Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
            $cronData['is_exception'] = 1;
            $cronData['log_desc']     = $exception->getMessage();
            cronlog($cronData, 1);
        }
    }
}
