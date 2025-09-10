<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V10;

use App\Http\Collections\V6\HomeCourseCollection;
use App\Http\Collections\V7\FeedListCollection;
use App\Http\Collections\V8\RecommendationCollection;
use App\Http\Controllers\API\V9\CommonController as v9CommonController;
use App\Http\Resources\V1\SurveyListResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\CompanyWiseLabelString;
use App\Models\Course;
use App\Models\Feed;
use App\Models\Goal;
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

class CommonController extends v9CommonController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

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

            $companyLabelString = CompanyWiseLabelString::where('company_id', $company->id)->get()->pluck('label_name', 'field_name')->toArray();

            if (count($companyLabelString) <= 0) {
                $companyLabelString = [
                    'recent_stories'      => config('zevolifesettings.company_label_string.home.recent_stories.default_value'),
                    'lbl_company'         => config('zevolifesettings.company_label_string.home.lbl_company.default_value'),
                    'get_support'         => config('zevolifesettings.company_label_string.support.get_support.default_value'),
                    'employee_assistance' => config('zevolifesettings.company_label_string.support.employee_assistance.default_value'),
                    'lbl_faq'             => config('zevolifesettings.company_label_string.support.lbl_faq.default_value'),
                ];
            }
            $data['companyLabelString'] = $companyLabelString;

            return $this->successResponse(['data' => $data], 'Data retrieved successfully.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
