<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V8;

use App\Http\Collections\V6\HomeCourseCollection;
use App\Http\Collections\V7\FeedListCollection;
use App\Http\Collections\V8\HomeLeaderboardCollection;
use App\Http\Collections\V8\RecommendationCollection;
use App\Http\Controllers\API\V7\CommonController as v7CommonController;
use App\Http\Requests\Api\V1\ShareContentRequest;
use App\Http\Requests\Api\V8\NpsFeedBackRequest;
use App\Http\Resources\V1\SurveyListResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Jobs\SendContentSharePushNotification;
use App\Models\Course;
use App\Models\Feed;
use App\Models\Goal;
use App\Models\Group;
use App\Models\HsSurvey;
use App\Models\MeditationTrack;
use App\Models\Recipe;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CommonController extends v7CommonController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     *store nps feed back given by the user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeNPSAppTabFeedback(NpsFeedBackRequest $request)
    {
        try {
            \DB::beginTransaction();

            // logged-in user
            $user = $this->user();

            $userNpsData = $user->npsSurveyLinkLogs()->whereNull("survey_received_on")
                ->where("user_id", $user->id)
                ->orderBy("id", "DESC")
                ->first();

            if (!empty($userNpsData)) {
                $npsData = [
                    'feedback_type'      => $request->feedbackType,
                    'feedback'           => $request->feedback,
                    'survey_received_on' => now()->toDateTimeString(),
                ];
                $userNpsData->update($npsData);
                \DB::commit();
                return $this->successResponse(['data' => ["isFeedBackSubmitted" => false]], "Thanks, we really appreciate your feedback.");
            } else {
                \DB::rollback();
                return $this->successResponse(['data' => ["isFeedBackSubmitted" => true]], "Thanks, we really appreciate your feedback. Why not leave a review on the App store?");
            }
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Home statistics for move nourish inspire sliders
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHomeStatistics(Request $request)
    {
        try {
            $user             = $this->user();
            $company          = $user->company()->first();
            $appTimezone      = config('app.timezone');
            $timezone         = $user->timezone ?? config('app.timezone');
            $data             = array();
            $userSelectedGoal = $user->userGoalTags()->pluck("goals.id")->toArray();

            // Health score survey

            $usersurveyData = HsSurvey::where('user_id', $user->id)
                ->whereNotNull('survey_complete_time')
                ->orderBy('id', 'DESC')
                ->first();

            $headers = $request->headers->all();
            $payload = $request->all();

            if (!empty($usersurveyData)) {
                $version               = config('zevolifesettings.version.api_version');
                $surveyHistoryRequest  = Request::create("api/" . $version . "/healthscore/report/" . $usersurveyData->id, 'GET', $headers, $payload);
                $surveyHistoryResponse = \Route::dispatch($surveyHistoryRequest);
                $surveyHistoryBody     = json_decode($surveyHistoryResponse->getContent());
            }

            if (!empty($surveyHistoryBody)) {
                $surveyHistoryBody->result->data->surveyId = $usersurveyData->id;
                $data['surveyinfo']                        = new SurveyListResource($surveyHistoryBody);
            }

            // User statistics data for current day
            $userCalorieHistory = $user->steps()->select(\DB::raw("SUM(user_step.calories) as calories"), \DB::raw("SUM(user_step.steps) as steps"))
                ->where(\DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$timezone}'))"), '=', now($timezone)->toDateString())
                ->first();

            $companyUsersList = $company->members()->pluck('users.id')->toArray();

            $end   = Carbon::today()->subDay()->toDateTimeString();
            $start = Carbon::parse($end)->subDays(7)->toDateTimeString();

            $companyLeader = User::leftJoin('user_step', 'user_step.user_id', '=', 'users.id')
                ->whereIn('user_step.user_id', $companyUsersList)
                ->whereBetween('user_step.log_date', array($start, $end))
                ->select('users.id', \DB::raw("SUM(user_step.steps) as steps"))
                ->groupBy('user_step.user_id')
                ->orderBy('steps', 'DESC')
                ->orderBy('user_step.created_at', 'ASC')
                ->first();

            $data['userstatistics']['move'] = [
                'dailySteps' => (!empty($userCalorieHistory) && !empty($userCalorieHistory['steps'])) ? (int) $userCalorieHistory['steps'] : 0,
            ];

            if (!empty($companyLeader) && $companyLeader->steps > 0) {
                $companyLeaderArray = [
                    'id'    => $companyLeader->id,
                    'steps' => (int) $companyLeader->steps,
                    'image' => $companyLeader->getMediaData('logo', ['w' => 320, 'h' => 320, 'zc' => 0]),
                ];
                $data['userstatistics']['move']['companyLeader'] = $companyLeaderArray;
            }

            $latestRecipe = Recipe::where('status', 1)
                ->where(function ($query) use ($company) {
                    return $query->where('company_id', null)
                        ->orWhere('company_id', $company->id);
                })
                ->orderBy('id', 'DESC')
                ->first();

            $data['userstatistics']['nourish'] = [
                'dailyCalories' => (!empty($userCalorieHistory) && !empty($userCalorieHistory['calories'])) ? (double) $userCalorieHistory['calories'] : 0.0,
            ];

            if (!empty($latestRecipe)) {
                $latestRecipeArray = [
                    'id'    => $latestRecipe->id,
                    'title' => $latestRecipe->title,
                    'image' => $latestRecipe->getMediaData('logo', ['w' => 320, 'h' => 320, 'zc' => 0]),
                ];
                $data['userstatistics']['nourish']['recipe'] = $latestRecipeArray;
            }

            $meditationCount = $user->completedMeditationTracks()
                ->where(\DB::raw("DATE(CONVERT_TZ(user_listened_tracks.created_at, '{$appTimezone}', '{$timezone}'))"), '=', now($timezone)->toDateString())
                ->count();

            $latestMeditation = MeditationTrack::orderBy('id', 'DESC')
                ->first();

            $data['userstatistics']['inspire'] = [
                'dailyMeditations' => $meditationCount,
            ];

            if (!empty($latestMeditation)) {
                $latestMeditationArray = [
                    'id'         => $latestMeditation->id,
                    'title'      => $latestMeditation->title,
                    'categoryId' => $latestMeditation->sub_category_id,
                    'image'      => $latestMeditation->getMediaData('cover', ['w' => 320, 'h' => 320, 'zc' => 0]),
                ];
                $data['userstatistics']['inspire']['meditation'] = $latestMeditationArray;
            }

            // $data['userstatistics']['steps'] = (!empty($userCalorieHistory) && !empty($userCalorieHistory['steps'])) ? (int) $userCalorieHistory['steps'] : 0;

            // $data['userstatistics']['calories'] = (!empty($userCalorieHistory) && !empty($userCalorieHistory['calories'])) ? (double) $userCalorieHistory['calories'] : 0.0;

            // $data['userstatistics']['meditation'] = $meditationCount;

            // get user's running lessions with course data
            $runningCourseRecords = $user->courseLogs()
                ->wherePivot('joined', 1)
                ->wherePivot('started_course', 1)
                ->wherePivot('completed_on', '=', null)
                ->orderByDesc('user_course.joined_on');

            // use count based on receieved data from course  API total data count must be 10 max.
            $runningCourseRecords = $runningCourseRecords->paginate(10);

            // collect required course data
            $data['masterclasses'] = new HomeCourseCollection($runningCourseRecords);

            // Feed List get max 10 feed for home Statistics

            $feedRecords = Feed::join('feed_company', function ($join) use ($company) {
                $join->on('feeds.id', '=', 'feed_company.feed_id')
                    ->where('feed_company.company_id', '=', $company->getKey());
            })
                ->join('sub_categories', function ($join) {
                    $join->on('sub_categories.id', '=', 'feeds.sub_category_id');
                })
                ->select('feeds.*', 'sub_categories.name AS sub_category_name')
                ->selectRaw("(CASE feeds.type WHEN 1 THEN 'feed_audio' WHEN 2 THEN 'feed_video' WHEN 3 THEN 'feed_youtube' WHEN 4 THEN 'feed' ELSE 'feed' END) as 'goalContentType'")
                ->where(function (Builder $query) use ($timezone) {
                    return $query->where(\DB::raw("CONVERT_TZ(feeds.start_date, 'UTC', feeds.timezone)"), '<=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))->orWhere('feeds.start_date', null);
                })
                ->where(function (Builder $query) use ($timezone) {
                    return $query->where(\DB::raw("CONVERT_TZ(feeds.end_date, 'UTC', feeds.timezone)"), '>=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))->orWhere('feeds.end_date', null);
                });

            $userGoalFeed = $feedRecords;
            $feedRecords  = $feedRecords
                ->groupBy('feeds.id')
                ->orderBy('feeds.is_stick', 'DESC')
                ->orderBy('feeds.id', 'DESC')
                ->limit(10)
                ->get();

            $data['feeds'] = new FeedListCollection($feedRecords, true);

            $recomendedSection          = array();
            $data['showRecommendation'] = true;

            $goalObj     = new Goal();
            $goalRecords = $goalObj->getAssociatedGoalTags();

            if ($goalRecords->count() > 0) {
                $data['showRecommendation'] = true;
            } else {
                $data['showRecommendation'] = false;
            }

            if (!empty($userSelectedGoal)) {
                $userGoalFeed = $userGoalFeed->join('feed_tag', function ($join) {
                    $join->on('feed_tag.feed_id', '=', 'feeds.id');
                })
                    ->whereIn("feed_tag.goal_id", $userSelectedGoal)
                    ->groupBy('feeds.id')
                    ->get();

                $userGoalRecipe = Recipe::select("recipe.*", DB::raw("'recipe' goalContentType"))
                    ->join('recipe_tag', function ($join) {
                        $join->on('recipe_tag.recipe_id', '=', 'recipe.id');
                    })
                    ->where("recipe.status", true)
                    ->whereIn("recipe_tag.goal_id", $userSelectedGoal)
                    ->groupBy('recipe.id')
                    ->get();

                $userGoalMeditation = MeditationTrack::select("meditation_tracks.*", DB::raw("'meditation' goalContentType"))
                    ->join('meditation_tracks_tag', function ($join) {
                        $join->on('meditation_tracks_tag.meditation_track_id', '=', 'meditation_tracks.id');
                    })
                    ->whereIn("meditation_tracks_tag.goal_id", $userSelectedGoal)
                    ->groupBy('meditation_tracks.id')
                    ->get();

                $userGoalCourse = Course::select("courses.*", DB::raw("'masterclass' goalContentType"))
                    ->join('course_tag', function ($join) {
                        $join->on('course_tag.course_id', '=', 'courses.id');
                    })
                    ->join('masterclass_company', function ($join) use ($company) {
                        $join->on('masterclass_company.masterclass_id', '=', 'courses.id')
                            ->where('masterclass_company.company_id', $company->id);
                    })
                    ->where("courses.status", true)
                    ->whereIn("course_tag.goal_id", $userSelectedGoal)
                    ->groupBy('courses.id')
                    ->get();
                $recomendedCollection = new Collection();
                $recomendedCollection = $recomendedCollection->merge($userGoalFeed);
                $recomendedCollection = $recomendedCollection->merge($userGoalRecipe);
                $recomendedCollection = $recomendedCollection->merge($userGoalMeditation);
                $recomendedCollection = $recomendedCollection->merge($userGoalCourse);

                if ($recomendedCollection->count() > 0) {
                    if ($recomendedCollection->count() > 8) {
                        $recomendedSection = new RecommendationCollection($recomendedCollection->random(8));
                    } else {
                        $recomendedSection = new RecommendationCollection($recomendedCollection);
                    }
                }
            }
            $data['recommendation'] = $recomendedSection;

            return $this->successResponse(['data' => $data], 'Data retrieved successfully.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Home leaderboard screen
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function homeLeaderboard(Request $request)
    {
        try {
            $user    = $this->user();
            $company = $user->company()->first();

            $companyUsersList = $company->members()
                ->where('users.is_blocked', 0)
                ->where('users.can_access_app', 1)
                ->pluck('users.id')
                ->toArray();

            $days  = isset($request->days) ? (int) $request->days : 7;
            $end   = Carbon::today()->subDay()->toDateTimeString();
            $start = Carbon::parse($end)->subDays($days)->toDateTimeString();

            $records = User::leftJoin('user_step', 'user_step.user_id', '=', 'users.id')
                ->whereIn('user_step.user_id', $companyUsersList)
                ->whereBetween('user_step.log_date', array($start, $end))
                ->select('users.id', \DB::raw("CONCAT(first_name,' ',last_name) AS name"), \DB::raw("SUM(user_step.steps) as steps"))
                ->groupBy('user_step.user_id')
                ->orderBy('steps', 'DESC')
                ->orderBy('user_step.created_at', 'ASC')
                ->limit(5)
                ->get();

            $data = new HomeLeaderboardCollection($records);

            return $this->successResponse(['data' => $data], 'Home Leaderboard data retrieved successfully.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Set view count
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function setViewCount(Request $request, $id, $modelType)
    {
        try {
            // logged-in user
            $user      = $this->user();
            $tableName = "";
            if ($modelType == 'feed') {
                $modelData = Feed::find($id);
                $tableName = "feeds";
            } elseif ($modelType == 'meditation') {
                $modelData = MeditationTrack::find($id);
                $tableName = "meditation_tracks";
            } elseif ($modelType == 'recipe') {
                $modelData = Recipe::find($id);
                $tableName = "recipe";
            } else {
                return $this->notFoundResponse("Requested data not found");
            }

            if (!empty($modelData)) {
                if ($modelType == 'feed') {
                    $pivotExsisting = $modelData->feedUserLogs()->wherePivot('user_id', $user->getKey())->wherePivot('feed_id', $modelData->getKey())->first();
                } elseif ($modelType == 'meditation') {
                    $pivotExsisting = $modelData->trackUserLogs()->wherePivot('user_id', $user->getKey())->wherePivot('meditation_track_id', $modelData->getKey())->first();
                } elseif ($modelType == 'recipe') {
                    $pivotExsisting = $modelData->recipeUserLogs()->wherePivot('user_id', $user->getKey())->wherePivot('recipe_id', $modelData->getKey())->first();
                }
                $updateCount = false;
                if (!empty($pivotExsisting)) {
                    if ($pivotExsisting->pivot->view_count < 2) {
                        $pivotExsisting->pivot->view_count = $pivotExsisting->pivot->view_count + 1;
                        $pivotExsisting->pivot->save();
                        $updateCount = true;
                    }
                } else {
                    if ($modelType == 'feed') {
                        $modelData->feedUserLogs()->attach($user, ['view_count' => 1]);
                    } elseif ($modelType == 'meditation') {
                        $modelData->trackUserLogs()->attach($user, ['view_count' => 1]);
                    } elseif ($modelType == 'recipe') {
                        $modelData->recipeUserLogs()->attach($user, ['view_count' => 1]);
                    }
                    $updateCount = true;
                }
                $view_count = $modelData->view_count;
                if ($updateCount) {
                    $view_count = $modelData->view_count + 1;

                    DB::table($tableName)
                        ->where("id", $modelData->id)
                        ->increment('view_count');
                }

                return $this->successResponse(['data' => ['viewCount' => $view_count]], 'View Count updated successfully.');
            } else {
                return $this->notFoundResponse("Requested data not found");
            }
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Share content as group message
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function shareContent(ShareContentRequest $request)
    {
        try {
            $user                  = $this->user();
            $groupIds              = isset($request->groupIds) ? $request->groupIds : [];
            $notificationModelData = array();
            $tag                   = '';
            $moduleName            = ucfirst($request->modelType);

            if ($request->modelType == 'feed') {
                $model      = Feed::find($request->modelId);
                $moduleName = 'Story';
                $tag        = 'feed';
            } elseif ($request->modelType == 'masterclass') {
                $model = Course::find($request->modelId);
                $tag   = 'masterclass';
            } elseif ($request->modelType == 'meditation') {
                $model = MeditationTrack::find($request->modelId);
                $tag   = 'meditation';
            } elseif ($request->modelType == 'recipe') {
                $model = Recipe::find($request->modelId);
                $tag   = 'recipe';
            }

            if (!empty($groupIds)) {
                if (!empty($model)) {
                    \DB::beginTransaction();

                    foreach ($groupIds as $key => $value) {
                        $group = Group::find($value);

                        if (!empty($group)) {
                            $group->groupMessages()
                                ->attach($user, ['model_id' => $request->modelId, 'model_name' => $request->modelType]);

                            $group->update(['updated_at' => now()->toDateTimeString()]);

                            $title = trans('notifications.share.title');
                            $title = str_replace(['#module_name#'], [$moduleName], $title);

                            $notificationModelData['title']         = $title;
                            $notificationModelData['name']          = $model->title;
                            $notificationModelData['deep_link_uri'] = (!empty($model->deep_link_uri)) ? $model->deep_link_uri : "";

                            // dispatch job to send shared content notification to specified group members
                            \dispatch(new SendContentSharePushNotification($group, $notificationModelData, $user, ['tag' => $tag]));
                        }
                    }

                    \DB::commit();

                    return $this->successResponse(['data' => []], "{$moduleName} shared successfully.");
                } else {
                    return $this->notFoundResponse("Sorry! Unable to find {$moduleName}");
                }
            }

            \DB::rollback();
            return $this->successResponse(['data' => []], "Unable to share {$moduleName}");
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
