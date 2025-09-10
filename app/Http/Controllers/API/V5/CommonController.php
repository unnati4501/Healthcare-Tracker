<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V5;

use App\Http\Collections\V1\CompanyTeamsCollection;
use App\Http\Collections\V1\HomeCourseCollection;
use App\Http\Collections\V5\CompanyUsersCollection;
use App\Http\Collections\V5\FeedCollection;
use App\Http\Controllers\API\V4\CommonController as v4CommonController;
use App\Http\Requests\Api\V5\LogTrackerRequest;
use App\Http\Resources\V1\SurveyListResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Feed;
use App\Models\HsSurvey;
use App\Models\TrackerLogs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommonController extends v4CommonController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Store log of tracker
     *
     * @param LogTrackerRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logTracker(LogTrackerRequest $request)
    {
        try {
            if (!$request->headers->has('X-User-Tracker')) {
                return $this->invalidResponse([], 'The User-Tracker header is required', 422);
            }
            $payload = $request->all();
            $user    = $this->user();

            if (!empty($payload)) {
                foreach ($payload as $key => $log) {
                    TrackerLogs::create([
                        'user_id'      => $user->getKey(),
                        'os'           => $request->headers->get('X-Device-Os'),
                        'tracker_name' => $request->headers->get('X-User-Tracker'),
                        'request_url'  => ($log['requestURL'] ?? null),
                        'request_data' => ($log['requestData'] ?? null),
                        'fetched_data' => ($log['fetchedData'] ?? null),
                    ]);
                }
            }

            return $this->successResponse([], 'Logs stored successfully.');
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
    public function getAllUsers(Request $request)
    {
        try {
            $user     = $this->user();
            $company  = $user->company()->first();
            $timezone = $user->timezone ?? config('app.timezone');

            $departmentIds = $company->departments()->where('departments.default', 0)->pluck('departments.id')->toArray();

            $modelId   = $request->modelId;
            $modelType = $request->modelType;

            if (!empty($modelType) & !empty($modelId)) {
                if ($modelType == 'group') {
                    $model = \App\Models\Group::find($modelId);
                } elseif ($modelType == 'challenge') {
                    $model = \App\Models\Challenge::find($modelId);
                }
            }

            $alredySelectedMembers = [];
            if (!empty($model)) {
                $alredySelectedMembers = $model->members()->pluck('users.id')->toArray();
            }

            array_unshift($alredySelectedMembers, $user->getKey());

            $userRecords = $company->members()->whereNotIn('users.id', $alredySelectedMembers);

            $userRestriction = 0;
            if ($modelType == 'group') {
                $teamId       = $user->company()->first()->pivot->team_id;
                $departmentId = $user->company()->first()->pivot->department_id;

                $userRestriction = $company->group_restriction;

                if ($userRestriction != 0) {
                    $userRecords = $userRecords->wherePivotIn('department_id', $departmentIds);
                }

                if ($userRestriction == 1) {
                    $userRecords = $userRecords->wherePivot('department_id', $departmentId);
                } elseif ($userRestriction == 2) {
                    $userRecords = $userRecords->wherePivot('team_id', $teamId);
                }
            }

            if (!empty($request->search)) {
                $userRecords = $userRecords->where(\DB::raw("CONCAT(first_name,' ',last_name)"), 'like', '%' . $request->search . '%');
            }

            if (!empty($request->team)) {
                $userRecords = $userRecords->wherePivotIn('team_id', $request->team);
            }

            if (!empty($request->department)) {
                $userRecords = $userRecords->wherePivotIn('department_id', $request->department);
            }

            $userRecords = $userRecords->where('can_access_app', 1)
                ->where('is_blocked', 0)
                ->orderBy(\DB::raw("CONCAT(first_name,' ',last_name)"))
                ->orderByDesc('users.id')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            return $this->successResponse(
                ($userRecords->count() > 0) ? new CompanyUsersCollection($userRecords) : ['data' => []],
                ($userRecords->count() > 0) ? 'Users list retrieved successfully.' : 'No results'
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
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            return $this->successResponse(
                ($deptRecords->count() > 0) ? new CompanyTeamsCollection($deptRecords) : ['data' => []],
                ($deptRecords->count() > 0) ? 'Teams list retrieved successfully.' : 'No results'
            );
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
