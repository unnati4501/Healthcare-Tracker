<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V6;

use App\Http\Collections\V6\CompanyDepartmentsCollection;
use App\Http\Collections\V6\CompanyTeamsCollection;
use App\Http\Collections\V6\FeedListCollection;
use App\Http\Collections\V6\HomeCourseCollection;
use App\Http\Controllers\API\V5\CommonController as v5CommonController;
use App\Http\Requests\Api\V1\ShareContentRequest;
use App\Http\Resources\V1\SurveyListResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Jobs\SendContentSharePushNotification;
use App\Models\Course;
use App\Models\Feed;
use App\Models\Group;
use App\Models\HsSurvey;
use App\Models\MeditationTrack;
use App\Models\Recipe;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DB;

class CommonController extends v5CommonController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

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
                ->where(function (Builder $query) use ($timezone) {
                    return $query->where(\DB::raw("CONVERT_TZ(feeds.start_date, 'UTC', feeds.timezone)"), '<=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))->orWhere('feeds.start_date', null);
                })
                ->where(function (Builder $query) use ($timezone) {
                    return $query->where(\DB::raw("CONVERT_TZ(feeds.end_date, 'UTC', feeds.timezone)"), '>=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))->orWhere('feeds.end_date', null);
                });

            $feedRecords = $feedRecords
                ->groupBy('feeds.id')
                ->orderBy('feeds.is_stick', 'DESC')
                ->orderBy('feeds.id', 'DESC')
                ->limit(10)
                ->get();

            $data['feeds'] = new FeedListCollection($feedRecords, true);

            return $this->successResponse(['data' => $data], 'Data retrieved successfully.');
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
    public function getAllTeams(Request $request)
    {
        try {
            $user     = $this->user();
            $company  = $user->company()->first();
            $timezone = $user->timezone ?? config('app.timezone');

            $deptRecords = $company->teams()->where('teams.name', 'like', '%' . $request->search . '%');

            if (!empty($request->search)) {
                $deptRecords = $deptRecords->where('teams.name', 'like', '%' . $request->search . '%');
            }

            if (!empty($request->department)) {
                $deptRecords = $deptRecords->whereIn('department_id', $request->department);
            }

            $deptRecords = $deptRecords->orderByDesc('teams.name')
                ->get();

            return $this->successResponse(
                ($deptRecords->count() > 0) ? new CompanyTeamsCollection($deptRecords) : ['data' => []],
                ($deptRecords->count() > 0) ? 'Teams list retrieved successfully.' : 'No results'
            );
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
    public function getAllDepartments(Request $request)
    {
        try {
            $user     = $this->user();
            $company  = $user->company()->first();
            $timezone = $user->timezone ?? config('app.timezone');

            $deptRecords = $company->departments()->where('departments.name', 'like', '%' . $request->search . '%');

            if (!empty($request->search)) {
                $deptRecords = $deptRecords->where('departments.name', 'like', '%' . $request->search . '%');
            }

            $deptRecords = $deptRecords->orderByDesc('departments.name')
                ->get();

            return $this->successResponse(
                ($deptRecords->count() > 0) ? new CompanyDepartmentsCollection($deptRecords) : ['data' => []],
                ($deptRecords->count() > 0) ? 'Departments list retrieved successfully.' : 'No results'
            );
        } catch (\Exception $e) {
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
            \DB::beginTransaction();

            $user                  = $this->user();
            $group                 = Group::find($request->groupId);
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

            if (!empty($group)) {
                if (!empty($model)) {
                    $group
                        ->groupMessages()
                        ->attach($user, ['model_id' => $request->modelId, 'model_name' => $request->modelType]);

                    $group
                        ->update(['updated_at' => now()->toDateTimeString()]);

                    \DB::commit();

                    $title = trans('notifications.share.title');
                    $title = str_replace(['#module_name#'], [$moduleName], $title);

                    $notificationModelData['title']         = $title;
                    $notificationModelData['name']          = $model->title;
                    $notificationModelData['deep_link_uri'] = (!empty($model->deep_link_uri)) ? $model->deep_link_uri : "";

                    // dispatch job to send shared content notification to specified group members
                    $this->dispatch(new SendContentSharePushNotification($group, $notificationModelData, $user, ['tag' => $tag]));

                    return $this->successResponse(['data' => []], "{$moduleName} shared successfully.");
                } else {
                    \DB::rollback();
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

    public function setViewCount(Request $request, $id, $modelType)
    {
        try {
            // logged-in user
            $user = $this->user();
            $tableName = "";
            if ($modelType == 'feed') {
                $modelData = Feed::find($id);
                $tableName = "feeds";
            } elseif ($modelType == 'meditation') {
                $modelData = MeditationTrack::find($id);
                $tableName = "meditation_tracks";
            } else {
                return $this->notFoundResponse("Requested data not found");
            }

            if (!empty($modelData)) {
                if ($modelType == 'feed') {
                    $pivotExsisting = $modelData->feedUserLogs()->wherePivot('user_id', $user->getKey())->wherePivot('feed_id', $modelData->getKey())->first();
                } elseif ($modelType == 'meditation') {
                    $pivotExsisting = $modelData->trackUserLogs()->wherePivot('user_id', $user->getKey())->wherePivot('meditation_track_id', $modelData->getKey())->first();
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
}
