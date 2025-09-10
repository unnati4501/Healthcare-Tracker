<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V38;

use App\Http\Controllers\API\V36\CommonController as v36CommonController;
use App\Http\Collections\V36\SubCategoryCollection as v36subcategorycollection;
use App\Http\Collections\V38\SavedContentImagesCollection;
use App\Http\Collections\V26\HomeLeaderboardCollection;
use App\Http\Collections\V8\RecommendationCollection;
use App\Http\Collections\V20\FeedListCollection;
use App\Http\Collections\V36\RecentPodcastCollection;
use App\Http\Collections\V6\HomeCourseCollection;
use App\Http\Requests\Api\V1\ShareContentRequest;
use App\Http\Resources\V17\GroupMessagesResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Jobs\SendContentSharePushNotification;
use App\Models\Badge;
use App\Models\Course;
use App\Models\EAP;
use App\Models\Feed;
use App\Models\Group;
use App\Models\MeditationTrack;
use App\Models\Recipe;
use App\Models\User;
use App\Models\Webinar;
use App\Models\SubCategory;
use App\Models\Podcast;
use App\Models\Category;
use App\Models\Challenge;
use App\Models\MoodUser;
use App\Models\UserGoal;
use App\Models\ZcSurveyLog;
use App\Models\ZcSurveyUserLog;
use App\Models\ZcSurveyResponse;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CommonController extends v36CommonController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;
    /**
     * Display max 3 images of all the content like webinar, feed, meditation, masterclass and recipes
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSavedContentImages(Request $request)
    {
        try {
            $user    = $this->user();
            $company = $user->company()->first();
            $team    = $user->teams()->first();

            $trackIds = MeditationTrack::join('user_meditation_track_logs', 'meditation_tracks.id', '=', 'user_meditation_track_logs.meditation_track_id')
                ->join('sub_categories', 'sub_categories.id', '=', 'meditation_tracks.sub_category_id')
                ->join('meditation_tracks_team', function ($join) use ($team) {
                    $join->on('meditation_tracks_team.meditation_track_id', '=', 'meditation_tracks.id')
                        ->where('meditation_tracks_team.team_id', $team->id);
                })
                ->where("user_meditation_track_logs.user_id", $user->id)
                ->where(["user_meditation_track_logs.saved" => 1, "sub_categories.status" => 1])
                ->pluck("meditation_tracks.id")
                ->toArray();
            if (!empty($trackIds)) {
                $meditationRecords = MeditationTrack::whereIn("meditation_tracks.id", $trackIds)
                    ->leftJoin("user_incompleted_tracks", function ($join) use ($user) {
                        $join->on('meditation_tracks.id', '=', 'user_incompleted_tracks.meditation_track_id')
                            ->where('user_incompleted_tracks.user_id', '=', $user->getKey());
                    })
                    ->leftJoin('user_meditation_track_logs', function ($join) use ($user) {
                        $join->on('meditation_tracks.id', '=', 'user_meditation_track_logs.meditation_track_id')
                            ->where('user_meditation_track_logs.user_id', '=', $user->getKey());
                    })
                    ->select("meditation_tracks.*")
                    ->orderBy('user_meditation_track_logs.saved_at', 'DESC')
                    ->orderBy('meditation_tracks.id', 'DESC')
                    ->groupBy('meditation_tracks.id')
                    ->get();
            }

            $feedRecords = $user->feedLogs()
                ->join('sub_categories', function ($join) {
                    $join->on('sub_categories.id', '=', 'feeds.sub_category_id');
                })
                ->join('feed_team', function ($join) use ($team) {
                    $join->on('feeds.id', '=', 'feed_team.feed_id')
                        ->where('feed_team.team_id', '=', $team->getKey());
                })
                ->wherePivot('user_id', $user->getKey())
                ->wherePivot('saved', true)
                ->orderBy('feed_user.saved_at', 'DESC')
                ->orderBy('feed_user.id', 'DESC')
                ->groupBy('feeds.id')
                ->get();

            $recipeRecords = Recipe::
                with('recipesubcategories')
                ->select(
                    'recipe.id'
                )
                ->join('recipe_user', 'recipe_user.recipe_id', '=', 'recipe.id')
                ->join('recipe_team', function ($join) use ($team) {
                    $join
                        ->on('recipe_team.recipe_id', '=', 'recipe.id')
                        ->where('recipe_team.team_id', $team->id);
                })
                ->whereHas('recipesubcategories', function ($query) {
                    $query->where('status', 1);
                })
                ->where('recipe_user.user_id', $user->getKey())
                ->where('recipe_user.saved', true)
                ->orderBy('recipe_user.saved_at', 'DESC')
                ->orderBy('recipe_user.id', 'DESC')
                ->groupBy('recipe.id')
                ->get();

            $masterclassRecords = Course::select("courses.id", "courses.title", "courses.creator_id")
                ->leftJoin('user_course', function ($join) use ($user) {
                    $join->on('courses.id', '=', 'user_course.course_id')
                        ->where('user_course.user_id', '=', $user->getKey());
                })
                ->join('masterclass_team', function ($join) use ($team) {
                    $join->on('masterclass_team.masterclass_id', '=', 'courses.id')
                        ->where('masterclass_team.team_id', $team->id);
                })
                ->where("courses.status", true)
                ->where("user_course.saved", true)
                ->orderBy('courses.created_at', 'DESC')
                ->groupBy('courses.id')->get();

            $webinarRecords = $user->webinarLogs()
                ->join('sub_categories', function ($join) {
                    $join->on('sub_categories.id', '=', 'webinar.sub_category_id');
                })
                ->join('webinar_team', function ($join) use ($team) {
                    $join->on('webinar.id', '=', 'webinar_team.webinar_id')
                        ->where('webinar_team.team_id', '=', $team->getKey());
                })
                ->wherePivot('user_id', $user->getKey())
                ->wherePivot('saved', true)
                ->orderBy('webinar_user.saved_at', 'DESC')
                ->orderBy('webinar_user.id', 'DESC')
                ->groupBy('webinar.id')
                ->get();

            $podcastIds = Podcast::join('user_podcast_logs', 'podcasts.id', '=', 'user_podcast_logs.podcast_id')
                ->join('sub_categories', 'sub_categories.id', '=', 'podcasts.sub_category_id')
                ->join('podcast_team', function ($join) use ($team) {
                    $join->on('podcast_team.podcast_id', '=', 'podcasts.id')
                        ->where('podcast_team.team_id', $team->id);
                })
                ->where("user_podcast_logs.user_id", $user->id)
                ->where(["user_podcast_logs.saved" => 1, "sub_categories.status" => 1])
                ->pluck("podcasts.id")
                ->toArray();

            if (!empty($podcastIds)) {
                $podcastRecords = Podcast::whereIn("podcasts.id", $podcastIds)
                    ->leftJoin("user_incompleted_podcasts", function ($join) use ($user) {
                        $join->on('podcasts.id', '=', 'user_incompleted_podcasts.podcast_id')
                            ->where('user_incompleted_podcasts.user_id', '=', $user->getKey());
                    })
                    ->leftJoin('user_podcast_logs', function ($join) use ($user) {
                        $join->on('podcasts.id', '=', 'user_podcast_logs.podcast_id')
                            ->where('user_podcast_logs.user_id', '=', $user->getKey());
                    })
                    ->select("podcasts.*")
                    ->orderBy('user_podcast_logs.saved_at', 'DESC')
                    ->orderBy('podcasts.id', 'DESC')
                    ->groupBy('podcasts.id')
                    ->get();
            }

            $contentData['meditation']  = $meditationRecords ?? [];
            $contentData['feed']        = $feedRecords ?? [];
            $contentData['recipe']      = $recipeRecords ?? [];
            $contentData['masterclass'] = $masterclassRecords ?? [];
            $contentData['webinar']     = $webinarRecords ?? [];
            $contentData['podcast']     = $podcastRecords ?? [];

            if (!empty($meditationRecords) || $feedRecords->count() > 0 || $recipeRecords->count() > 0 || $masterclassRecords->count() > 0 || $webinarRecords->count() > 0 || (!empty($podcastRecords) && $podcastRecords->count() > 0)) {
                return $this->successResponse((!empty($contentData)) ? new SavedContentImagesCollection($contentData) : ['data' => []], 'Content retrieved successfully.');
            } else {
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
