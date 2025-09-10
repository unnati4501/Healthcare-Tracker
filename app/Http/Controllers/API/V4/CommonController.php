<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V4;

use App\Http\Collections\V1\HomeCourseCollection;
use App\Http\Collections\V4\CategoryCollection;
use App\Http\Collections\V4\FeedCollection;
use App\Http\Collections\V4\SubCategoryCollection;
use App\Http\Controllers\API\V3\CommonController as v3CommonController;
use App\Http\Resources\V1\SurveyListResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Category;
use App\Models\Feed;
use App\Models\HsSurvey;
use App\Models\SubCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CommonController extends v3CommonController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Get list of master categories
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function categories(Request $request)
    {
        try {
            $records = Category::where('in_activity_level', 1)
                ->where('is_excluded', 0)
                ->get();

            return $this->successResponse(($records->count() > 0) ? new CategoryCollection($records) : ['data' => []], 'Master Categories Received Successfully.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get list of master categories
     *
     * @param Request $request, Category $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubCategories(Request $request, Category $category)
    {
        try {
            $records = SubCategory::where('category_id', $category->id)
                ->where('status', 1)
                ->orderBy('is_excluded', 'DESC')
                ->get();

            return $this->successResponse(($records->count() > 0) ? new SubCategoryCollection($records) : ['data' => []], 'Sub Categories Received Successfully.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHomeStatistics(Request $request)
    {
        try {
            $user        = $this->user();
            $company     = $user->company()->first();
            $appTimezone = config('app.timezone');
            $timezone    = $user->timezone ?? config('app.timezone');
            $data        = array();

            // Health score survey

            $usersurveyData = HsSurvey::where('user_id', $user->id)
                ->whereNotNull('survey_complete_time')
                ->orderBy('id', 'DESC')
                ->first();

            $headers = $request->headers->all();
            $payload = $request->all();

            if (!empty($usersurveyData)) {
                $version                                   = config('zevolifesettings.version.api_version');
                $surveyHistoryRequest                      = Request::create("api/" . $version . "/healthscore/report/" . $usersurveyData->id, 'GET', $headers, $payload);
                $surveyHistoryResponse                     = \Route::dispatch($surveyHistoryRequest);
                $surveyHistoryBody                         = json_decode($surveyHistoryResponse->getContent());
                $surveyHistoryBody->result->data->surveyId = $usersurveyData->id;
            }

            if (!empty($surveyHistoryBody)) {
                $data['surveyinfo'] = new SurveyListResource($surveyHistoryBody);
            }

            // User statistics data for current day
            $userCalorieHistory = $user->steps()->select(\DB::raw("SUM(user_step.calories) as calories"), \DB::raw("SUM(user_step.steps) as steps"))
                ->where(\DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$timezone}'))"), '=', now($timezone)->toDateString())
                ->first();

            $meditationCount = $user->completedMeditationTracks()
                ->where(\DB::raw("DATE(CONVERT_TZ(user_listened_tracks.created_at, '{$appTimezone}', '{$timezone}'))"), '=', now($timezone)->toDateString())
                ->count();

            $data['userstatistics']['steps'] = (!empty($userCalorieHistory) && !empty($userCalorieHistory['steps'])) ? (int) $userCalorieHistory['steps'] : 0;

            $data['userstatistics']['calories'] = (!empty($userCalorieHistory) && !empty($userCalorieHistory['calories'])) ? (double) $userCalorieHistory['calories'] : 0.0;

            $data['userstatistics']['meditation'] = $meditationCount;

            // Home Course Pannel Logic
            $runningContent = [];

            // get count of course and meditation
            $runningCourseCount = $user->courseLogs()
                ->wherePivot('joined', 1)
                ->wherePivot('completed_on', '=', null)
                ->count();

            // get user's running lessions with course data
            $runningCourseRecords = $user->courseLogs()
                ->wherePivot('joined', 1)
                ->wherePivot('completed_on', '=', null)
                ->orderByDesc('user_course.joined_on');

            // use count based on receieved data from course  API total data count must be 10 max.
            $runningCourseRecords = $runningCourseRecords->paginate(10);

            // collect required course data
            $data['courses'] = new HomeCourseCollection($runningCourseRecords);

            // Feed List get max 10 feed for home Statistics

            $feedRecords = Feed::join('feed_company', function ($join) use ($company) {
                $join->on('feeds.id', '=', 'feed_company.feed_id')
                    ->where('feed_company.company_id', '=', $company->getKey());
            })
                ->join('sub_categories', function ($join) {
                    $join->on('sub_categories.id', '=', 'feeds.sub_category_id');
                })
                ->select('feeds.*')
                ->where(function (Builder $query) use ($timezone) {
                    return $query->where(\DB::raw("CONVERT_TZ(feeds.start_date, 'UTC', feeds.timezone)"), '<=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))->orWhere('feeds.start_date', null);
                })
                ->where(function (Builder $query) use ($timezone) {
                    return $query->where(\DB::raw("CONVERT_TZ(feeds.end_date, 'UTC', feeds.timezone)"), '>=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))->orWhere('feeds.end_date', null);
                });

            $feedRecords = $feedRecords->groupBy('feeds.id')
                ->orderByDesc('feeds.updated_at')
                ->limit(10)
                ->get();

            $data['feeds'] = new FeedCollection($feedRecords, true);

            return $this->successResponse(['data' => $data], 'Data retrieved successfully.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
